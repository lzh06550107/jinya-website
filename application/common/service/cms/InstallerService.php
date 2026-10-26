<?php

namespace app\common\service\cms;

use think\Cache;
use think\Config;
use think\Db;
use app\common\service\cms\render\CmsCacheInvalidator;

/**
 * CMS 数据库安装与完整性检查。
 *
 * 该服务同时供 FastAdmin 首次安装流程和已有项目的 cms:install 命令使用。
 */
class InstallerService
{
    const CMS_ASSET_VERSION = '1.0.2.20260922';
    const LABEL_REFERENCE_ACCENT_COLOR = '#e25042';

    protected static $installed = null;

    protected $requiredTables = [
        'cms_product_category',
        'cms_product',
        'cms_product_image',
        'cms_product_parameter',
        'cms_product_section',
        'cms_article_category',
        'cms_article',
        'cms_page',
        'cms_case',
        'cms_navigation',
        'cms_banner',
        'cms_home_section',
        'cms_inquiry',
        'cms_inquiry_followup',
        'cms_layout_component',
        'cms_page_config',
        'cms_page_block',
        'cms_page_block_reference',
        'cms_home_section_reference',
        'cms_page_content_block',
    ];

    /**
     * 判断 CMS 是否完成安装。
     *
     * @param mixed       $connection ThinkPHP 数据库连接
     * @param string|null $prefix     数据表前缀
     * @return bool
     */
    public function isInstalled($connection = null, $prefix = null)
    {
        if ($connection === null && $prefix === null && self::$installed !== null) {
            return self::$installed;
        }

        try {
            $connection = $connection ?: Db::connect();
            $prefix = $this->normalizePrefix($prefix ?: Config::get('database.prefix'));
            $installed = $this->tableExists($connection, $prefix . 'cms_navigation')
                && $this->tableExists($connection, $prefix . 'cms_home_section');
        } catch (\Exception $e) {
            $installed = false;
        }

        if ($connection !== null && $prefix !== null) {
            self::$installed = $installed;
        }
        return $installed;
    }

    /**
     * 安装 CMS 数据表、默认数据及后台菜单。
     * SQL 使用 CREATE TABLE IF NOT EXISTS 和 INSERT IGNORE，可重复执行。
     *
     * @param mixed       $connection ThinkPHP 数据库连接
     * @param string|null $prefix     数据表前缀
     * @param bool        $clearCaches 是否清理后台菜单与前台缓存
     * @return array
     */
    public function install($connection = null, $prefix = null, $clearCaches = true)
    {
        $connection = $connection ?: Db::connect();
        $prefix = $this->normalizePrefix($prefix ?: Config::get('database.prefix'));

        if (!$this->tableExists($connection, $prefix . 'auth_rule')) {
            throw new \RuntimeException('FastAdmin 基础数据表不存在，请先完成 FastAdmin 安装。');
        }

        $pdo = $this->getPdo($connection);
        $pdo->exec($this->renderSchemaSql($prefix));
        $this->ensureSchema($connection, $prefix);
        $this->ensureDynamicRenderSchema($connection, $prefix);
        $this->ensureHomeReferenceDisplayImageColumns($connection, $prefix);
        $this->retireBannerPosterColumns($connection, $prefix);
        $this->ensureBannerHighlightsColumn($connection, $prefix);
        $this->migrateLegacyHomeHeroBannerDefaults($connection, $prefix);
        $pdo->exec($this->renderCloneSql($prefix));
        $pdo->exec($this->renderJinyaProductCatalogSql($prefix));
        $this->retireRemovedCmsRuntimeArtifacts($connection, $prefix);
        $this->ensureLabelHeaderNavigation($connection, $prefix);
        $this->ensureBagsHeaderNavigation($connection, $prefix);
        $this->ensureBoxesHeaderNavigation($connection, $prefix);
        $this->ensureAboutHeaderNavigation($connection, $prefix);
        $this->ensureContactHeaderNavigation($connection, $prefix);
        $this->normalizePrimaryHeaderNavigation($connection, $prefix);
        $this->normalizeLegacyFooterNavigation($connection, $prefix);
        $this->ensureHomeSectionDefaults($connection, $prefix);
        $this->ensureLabelPageDefaults($connection, $prefix);
        $this->ensureBagsPageDefaults($connection, $prefix);
        $this->ensureBoxesPageDefaults($connection, $prefix);
        $this->ensureAboutPageDefaults($connection, $prefix);
        $this->ensureContactPageDefaults($connection, $prefix);
        $this->normalizeLegacyHomeSectionWeights($connection, $prefix);
        $this->retireCmsMobileConfig($connection, $prefix);
        $this->retireLegacySiteBannerConfig($connection, $prefix);
        $this->syncAssetVersion($connection, $prefix);
        // Normalize legacy HTML links before HTML is migrated to Markdown.
        $this->normalizeCmsUrls($connection, $prefix);
        // Historical strict clone fragments contained page layout HTML/CSS in data.
        // Retire them before structured-content migration so templates remain the
        // single owner of DOM/class/CSS layout.
        $this->retireStrictCloneBodies($connection, $prefix);
        $this->migrateMarkdownBodies($connection, $prefix);
        $this->syncPageSchema($connection, $prefix);
        // Apply the current html/ + html/mobile/ content baseline last so a fresh
        // CMS install renders the same copy and media as the approved static pages.
        $pdo->exec($this->renderHtmlBaselineSql($prefix));
        // HTML baseline marks obsolete clone-era news as hidden. Hidden is not a
        // valid article publishing state in the current CMS, so remove those rows
        // and any stale article references immediately after the baseline is applied.
        $this->deleteHiddenArticles($connection, $prefix);
        // Homepage products are referenced directly by cms_home_section_reference.
        // Retire the historical fake product category used only to satisfy category_id.
        $this->retireLegacyHomeProductCategory($connection, $prefix);
        // Keep only products that the current frontend explicitly references.
        // This removes old clone/demo products from Product Management while
        // preserving homepage/page-block products and their detail pages.
        $this->deleteUnusedProducts($connection, $prefix);
        // Retire old clone-era article categories that are not part of the current
        // Jinya news information architecture. Keep the articles themselves.
        $this->retireLegacyArticleCategories($connection, $prefix);
        // Keep the boxes page CMS section order identical to the approved frontend.
        // This also retires the historical standalone boxes_purchase block, whose
        // content now lives inside boxes_details and is not rendered as a section.
        $this->normalizeBoxesContentBlockOrder($connection, $prefix);
        // The approved promise badge icon used to live only as inline template SVG.
        // Backfill its asset path so the admin Logo field reflects what frontend shows.
        $this->ensureBoxesPromiseLogoDefault($connection, $prefix);
        // Apply the approved labels reference accent to editable rich-text copy.
        $this->applyLabelCapabilityReferenceTextColors($connection, $prefix);
        // bags_compare is a complete-image module. Install the approved uploaded artwork
        // into empty/legacy-only slots while preserving any real backend custom upload.
        $this->ensureBagsCompareFullImageDefaults($connection, $prefix);
        $this->ensureHomeAboutSocialIconDefaults($connection, $prefix);

        $missing = $this->missingTables($connection, $prefix);
        if ($missing) {
            throw new \RuntimeException('CMS 安装不完整，缺少数据表：' . implode(', ', $missing));
        }

        $this->refreshSiteConfig($connection, $prefix);
        self::$installed = true;

        if ($clearCaches) {
            $this->clearCaches();
        }

        return [
            'installed' => true,
            'table_count' => count($this->requiredTables),
            'prefix' => $prefix,
        ];
    }

    /**
     * Retire CMS runtime artifacts that belong only to removed case/media/legacy pages.
     * Active product management/list/detail metadata is intentionally preserved.
     * Case business rows remain because the homepage still reads them.
     */
    protected function retireRemovedCmsRuntimeArtifacts($connection, $prefix)
    {
        $pdo = $this->getPdo($connection);

        $authTable = $prefix . 'auth_rule';
        if ($this->tableExists($connection, $authTable)) {
            $roots = [
                'cms/cases',
                'cms/case_image',
                'cms/media_item',
                'cms/content_revision',
                'cms/url_redirect',
            ];
            $deleteRule = $pdo->prepare("DELETE FROM `{$authTable}` WHERE `name`=? OR `name` LIKE ?");
            foreach ($roots as $root) {
                $deleteRule->execute([$root, $root . '/%']);
            }
        }

        $retiredPageKeys = [
            'case.index', 'case.detail',
            'page.general', 'page.honor', 'page.patent', 'page.gallery',
            'page.video', 'page.construction',
        ];
        $pageConfigTable = $prefix . 'cms_page_config';
        $pageBlockTable = $prefix . 'cms_page_block';
        $pageBlockReferenceTable = $prefix . 'cms_page_block_reference';
        if ($this->tableExists($connection, $pageBlockTable)) {
            $placeholders = implode(',', array_fill(0, count($retiredPageKeys), '?'));
            if ($this->tableExists($connection, $pageBlockReferenceTable)) {
                $deleteReferences = $pdo->prepare(
                    "DELETE FROM `{$pageBlockReferenceTable}` WHERE `page_block_id` IN (SELECT `id` FROM `{$pageBlockTable}` WHERE `page_key` IN ({$placeholders}))"
                );
                $deleteReferences->execute($retiredPageKeys);
            }
            $deleteBlocks = $pdo->prepare("DELETE FROM `{$pageBlockTable}` WHERE `page_key` IN ({$placeholders})");
            $deleteBlocks->execute($retiredPageKeys);
        }
        if ($this->tableExists($connection, $pageConfigTable)) {
            $placeholders = implode(',', array_fill(0, count($retiredPageKeys), '?'));
            $deletePages = $pdo->prepare("DELETE FROM `{$pageConfigTable}` WHERE `page_key` IN ({$placeholders})");
            $deletePages->execute($retiredPageKeys);
        }

        $bannerTable = $prefix . 'cms_banner';
        if ($this->tableExists($connection, $bannerTable)) {
            $retiredBannerKeys = [
                    'case.index', 'case.detail',
                'page.honor', 'page.patent', 'page.gallery', 'page.video', 'page.construction',
            ];
            $placeholders = implode(',', array_fill(0, count($retiredBannerKeys), '?'));
            $retireBanners = $pdo->prepare(
                "UPDATE `{$bannerTable}` SET `status`='hidden',`deletetime`=COALESCE(`deletetime`,UNIX_TIMESTAMP()),`updatetime`=UNIX_TIMESTAMP() " .
                "WHERE (`page_key` IN ({$placeholders}) OR `page_key` LIKE 'case.detail.%') AND `deletetime` IS NULL"
            );
            $retireBanners->execute($retiredBannerKeys);
        }
    }

    /**
     * 为“不干胶/卷标”页面补齐一级 Header 导航。
     *
     * 只在历史上从未存在该 Header URL 时创建；如果管理员曾隐藏或删除
     * 同 URL 导航，不通过安装器擅自复活。新行默认插在“首页”之后，
     * 后续仍由公共布局中的现有 weigh 拖拽排序管理。
     *
     * @param mixed  $connection
     * @param string $prefix
     * @return void
     */
    protected function ensureLabelHeaderNavigation($connection, $prefix)
    {
        $table = $prefix . 'cms_navigation';
        if (!$this->tableExists($connection, $table)) {
            return;
        }

        $pdo = $this->getPdo($connection);
        $position = 'header';
        $url = '/page/label';

        $existing = $pdo->prepare("SELECT `id` FROM `{$table}` WHERE `parent_id`=0 AND `position`=? AND `url`=? LIMIT 1");
        $existing->execute([$position, $url]);
        if ($existing->fetch(\PDO::FETCH_ASSOC)) {
            return;
        }

        $statement = $pdo->prepare("SELECT `id`,`url`,`weigh` FROM `{$table}` WHERE `parent_id`=0 AND `position`=? AND `deletetime` IS NULL ORDER BY `weigh` DESC,`id` ASC");
        $statement->execute([$position]);
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $weight = 1000;
        if ($rows) {
            $weight = (int)$rows[0]['weigh'] + 10;
            foreach ($rows as $index => $row) {
                if ((string)$row['url'] !== '/') {
                    continue;
                }
                $homeWeight = (int)$row['weigh'];
                if (isset($rows[$index + 1])) {
                    $nextWeight = (int)$rows[$index + 1]['weigh'];
                    $weight = $homeWeight - $nextWeight >= 2
                        ? (int)floor(($homeWeight + $nextWeight) / 2)
                        : $homeWeight;
                } else {
                    $weight = $homeWeight - 10;
                }
                break;
            }
        }

        $now = time();
        $insert = $pdo->prepare(
            "INSERT INTO `{$table}` (`parent_id`,`title`,`slug`,`url`,`link_type`,`link_value`,`target`,`icon`,`position`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) " .
            "VALUES (0,?,?,?,?,?,'_self','',?,1,1,?,'normal',?,?)"
        );
        $insert->execute([
            '不干胶/卷标',
            'label',
            $url,
            'url',
            $url,
            $position,
            $weight,
            $now,
            $now,
        ]);
    }


    /** 为“包装袋·无版印刷”页面补齐一级 Header 导航，默认排在不干胶之后。 */
    protected function ensureBagsHeaderNavigation($connection, $prefix)
    {
        $table = $prefix . 'cms_navigation';
        if (!$this->tableExists($connection, $table)) return;
        $pdo = $this->getPdo($connection);
        $position = 'header';
        $url = '/page/bags';
        $existing = $pdo->prepare("SELECT `id` FROM `{$table}` WHERE `parent_id`=0 AND `position`=? AND `url`=? LIMIT 1");
        $existing->execute([$position, $url]);
        if ($existing->fetch(\PDO::FETCH_ASSOC)) return;
        $statement = $pdo->prepare("SELECT `id`,`url`,`weigh` FROM `{$table}` WHERE `parent_id`=0 AND `position`=? AND `deletetime` IS NULL ORDER BY `weigh` DESC,`id` ASC");
        $statement->execute([$position]);
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $weight = 990;
        if ($rows) {
            foreach ($rows as $index => $row) {
                if ((string)$row['url'] !== '/page/label') continue;
                $anchor = (int)$row['weigh'];
                if (isset($rows[$index + 1])) {
                    $next = (int)$rows[$index + 1]['weigh'];
                    $weight = $anchor - $next >= 2 ? (int)floor(($anchor + $next) / 2) : $anchor - 1;
                } else {
                    $weight = $anchor - 10;
                }
                break;
            }
        }
        $now = time();
        $insert = $pdo->prepare(
            "INSERT INTO `{$table}` (`parent_id`,`title`,`slug`,`url`,`link_type`,`link_value`,`target`,`icon`,`position`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) " .
            "VALUES (0,?,?,?,?,?,'_self','',?,1,1,?,'normal',?,?)"
        );
        $insert->execute(['包装袋·无版印刷','bags',$url,'url',$url,$position,$weight,$now,$now]);
    }

    /** 为“彩盒”页面补齐一级 Header 导航，默认排在包装袋之后。 */
    protected function ensureBoxesHeaderNavigation($connection, $prefix)
    {
        $table = $prefix . 'cms_navigation';
        if (!$this->tableExists($connection, $table)) return;
        $pdo = $this->getPdo($connection);
        $position = 'header';
        $url = '/page/boxes';
        $existing = $pdo->prepare("SELECT `id` FROM `{$table}` WHERE `parent_id`=0 AND `position`=? AND `url`=? LIMIT 1");
        $existing->execute([$position, $url]);
        if ($existing->fetch(\PDO::FETCH_ASSOC)) return;
        $statement = $pdo->prepare("SELECT `id`,`url`,`weigh` FROM `{$table}` WHERE `parent_id`=0 AND `position`=? AND `deletetime` IS NULL ORDER BY `weigh` DESC,`id` ASC");
        $statement->execute([$position]);
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $weight = 980;
        if ($rows) {
            foreach ($rows as $index => $row) {
                if ((string)$row['url'] !== '/page/bags') continue;
                $anchor = (int)$row['weigh'];
                if (isset($rows[$index + 1])) {
                    $next = (int)$rows[$index + 1]['weigh'];
                    $weight = $anchor - $next >= 2 ? (int)floor(($anchor + $next) / 2) : $anchor - 1;
                } else {
                    $weight = $anchor - 10;
                }
                break;
            }
        }
        $now = time();
        $insert = $pdo->prepare(
            "INSERT INTO `{$table}` (`parent_id`,`title`,`slug`,`url`,`link_type`,`link_value`,`target`,`icon`,`position`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) " .
            "VALUES (0,?,?,?,?,?,'_self','',?,1,1,?,'normal',?,?)"
        );
        $insert->execute(['彩盒','boxes',$url,'url',$url,$position,$weight,$now,$now]);
    }


    /** 为“走近金亚”页面补齐一级 Header 导航，默认排在彩盒之后。 */
    protected function ensureAboutHeaderNavigation($connection, $prefix)
    {
        $table = $prefix . 'cms_navigation';
        if (!$this->tableExists($connection, $table)) return;
        $pdo = $this->getPdo($connection);
        $position = 'header';
        $url = '/page/about';
        $existing = $pdo->prepare("SELECT `id`,`title`,`slug` FROM `{$table}` WHERE `parent_id`=0 AND `position`=? AND `url`=? AND `deletetime` IS NULL LIMIT 1");
        $existing->execute([$position, $url]);
        $row = $existing->fetch(\PDO::FETCH_ASSOC);
        if ($row) {
            $legacyTitles = ['关于我们','走进企业','走进科创美','走近金亚包装'];
            if (in_array((string)$row['title'], $legacyTitles, true)) {
                $update = $pdo->prepare("UPDATE `{$table}` SET `title`='走近金亚',`slug`='about',`updatetime`=? WHERE `id`=?");
                $update->execute([time(), (int)$row['id']]);
            }
            return;
        }
        $statement = $pdo->prepare("SELECT `id`,`url`,`weigh` FROM `{$table}` WHERE `parent_id`=0 AND `position`=? AND `deletetime` IS NULL ORDER BY `weigh` DESC,`id` ASC");
        $statement->execute([$position]);
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $weight = 970;
        if ($rows) {
            foreach ($rows as $index => $nav) {
                if ((string)$nav['url'] !== '/page/boxes') continue;
                $anchorWeight = (int)$nav['weigh'];
                if (isset($rows[$index + 1])) {
                    $next = (int)$rows[$index + 1]['weigh'];
                    $weight = $anchorWeight - $next >= 2 ? (int)floor(($anchorWeight + $next) / 2) : $anchorWeight - 1;
                } else {
                    $weight = $anchorWeight - 10;
                }
                break;
            }
        }
        $now = time();
        $insert = $pdo->prepare(
            "INSERT INTO `{$table}` (`parent_id`,`title`,`slug`,`url`,`link_type`,`link_value`,`target`,`icon`,`position`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) " .
            "VALUES (0,?,?,?,?,?,'_self','',?,1,1,?,'normal',?,?)"
        );
        $insert->execute(['走近金亚','about',$url,'url',$url,$position,$weight,$now,$now]);
    }

    /** 为“联系我们”页面补齐一级 Header 导航，默认排在新闻动态之后。 */
    protected function ensureContactHeaderNavigation($connection, $prefix)
    {
        $table = $prefix . 'cms_navigation';
        if (!$this->tableExists($connection, $table)) return;
        $pdo = $this->getPdo($connection);
        $position = 'header';
        $url = '/page/contact';
        $existing = $pdo->prepare(
            "SELECT `id`,`title`,`slug`,`status`,`pc_visible`,`mobile_visible`,`deletetime` FROM `{$table}` " .
            "WHERE `parent_id`=0 AND `position`=? AND (`url`=? OR `url`='/page/contact.html' OR `slug`='contact' OR `title`='联系我们') " .
            "ORDER BY (`deletetime` IS NULL) DESC,(`status`='normal') DESC,`id` ASC LIMIT 1"
        );
        $existing->execute([$position, $url]);
        $row = $existing->fetch(\PDO::FETCH_ASSOC);
        if ($row) {
            // 当前金亚站明确要求“联系我们”为一级 Header 导航。历史升级中该行可能
            // 已被旧公共布局保存逻辑隐藏或软删除；在这里恢复为当前规范记录。
            $update = $pdo->prepare(
                "UPDATE `{$table}` SET `parent_id`=0,`title`='联系我们',`slug`='contact',`url`='/page/contact'," .
                "`link_type`='url',`link_value`='/page/contact',`target`='_self',`position`='header'," .
                "`pc_visible`=1,`mobile_visible`=1,`status`='normal',`deletetime`=NULL,`updatetime`=? WHERE `id`=?"
            );
            $update->execute([time(), (int)$row['id']]);
            return;
        }
        $statement = $pdo->prepare("SELECT `id`,`url`,`weigh` FROM `{$table}` WHERE `parent_id`=0 AND `position`=? AND `deletetime` IS NULL ORDER BY `weigh` DESC,`id` ASC");
        $statement->execute([$position]);
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $weight = 60;
        if ($rows) {
            $anchorIndex = null;
            foreach ($rows as $index => $nav) {
                if (in_array((string)$nav['url'], ['/news','/article_xwdt.html'], true)) { $anchorIndex = $index; break; }
            }
            if ($anchorIndex === null) {
                foreach ($rows as $index => $nav) {
                    if ((string)$nav['url'] === '/page/about') { $anchorIndex = $index; break; }
                }
            }
            if ($anchorIndex !== null) {
                $anchorWeight = (int)$rows[$anchorIndex]['weigh'];
                if (isset($rows[$anchorIndex + 1])) {
                    $next = (int)$rows[$anchorIndex + 1]['weigh'];
                    $weight = $anchorWeight - $next >= 2 ? (int)floor(($anchorWeight + $next) / 2) : $anchorWeight - 1;
                } else {
                    $weight = $anchorWeight - 10;
                }
            }
        }
        $now = time();
        $insert = $pdo->prepare(
            "INSERT INTO `{$table}` (`parent_id`,`title`,`slug`,`url`,`link_type`,`link_value`,`target`,`icon`,`position`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) " .
            "VALUES (0,?,?,?,?,?,'_self','',?,1,1,?,'normal',?,?)"
        );
        $insert->execute(['联系我们','contact',$url,'url',$url,$position,$weight,$now,$now]);
    }

    /**
     * 规范化金亚站一级 Header 导航。
     *
     * 当前站点明确保留 7 个官方入口并固定顺序，同时退役旧涂料站入口。
     * Header 严格只保留 7 个官方一级入口。旧入口、官方重复、自定义历史入口
     * 以及所有 Header 子导航直接物理删除，不再保留 deletetime 软删除记录。
     * 软删除/隐藏过的唯一官方入口会恢复为当前规范记录。
     *
     * @param mixed  $connection
     * @param string $prefix
     * @return void
     */
    protected function normalizePrimaryHeaderNavigation($connection, $prefix)
    {
        $table = $prefix . 'cms_navigation';
        if (!$this->tableExists($connection, $table)) {
            return;
        }

        $pdo = $this->getPdo($connection);
        $select = $pdo->prepare(
            "SELECT `id`,`parent_id`,`title`,`slug`,`url`,`weigh`,`status`,`pc_visible`,`mobile_visible`,`deletetime` " .
            "FROM `{$table}` WHERE `position`=? ORDER BY `id` ASC"
        );
        $select->execute(['header']);
        $rows = $select->fetchAll(\PDO::FETCH_ASSOC);
        $rows = is_array($rows) ? $rows : [];

        $official = [
            ['title' => '首页', 'slug' => '', 'url' => '/', 'aliases' => ['首页'], 'urls' => ['/']],
            ['title' => '不干胶/卷标', 'slug' => 'label', 'url' => '/page/label', 'aliases' => ['不干胶/卷标'], 'urls' => ['/page/label']],
            ['title' => '包装袋无版印刷', 'slug' => 'bags', 'url' => '/page/bags', 'aliases' => ['包装袋无版印刷', '包装袋·无版印刷'], 'urls' => ['/page/bags']],
            ['title' => '彩盒', 'slug' => 'boxes', 'url' => '/page/boxes', 'aliases' => ['彩盒'], 'urls' => ['/page/boxes']],
            ['title' => '走进金亚', 'slug' => 'about', 'url' => '/page/about', 'aliases' => ['走进金亚', '走近金亚', '走近金亚包装', '关于我们', '走进企业', '走进科创美'], 'urls' => ['/page/about', '/page/about.html']],
            ['title' => '新闻动态', 'slug' => 'news', 'url' => '/news', 'aliases' => ['新闻动态', '新闻中心'], 'urls' => ['/news', '/news.html', '/article_xwdt.html']],
            ['title' => '联系我们', 'slug' => 'contact', 'url' => '/page/contact', 'aliases' => ['联系我们', '联系科创美'], 'urls' => ['/page/contact', '/page/contact.html']],
        ];

        $matchesOfficial = function (array $row, array $definition) {
            $url = isset($row['url']) ? trim((string)$row['url']) : '';
            $slug = isset($row['slug']) ? trim((string)$row['slug']) : '';
            $title = isset($row['title']) ? trim((string)$row['title']) : '';
            if ($url !== '' && in_array($url, $definition['urls'], true)) {
                return true;
            }
            if ($definition['slug'] !== '' && $slug === $definition['slug']) {
                return true;
            }
            return $title !== '' && in_array($title, $definition['aliases'], true);
        };

        $legacyRules = [
            ['titles' => ['仿石漆'], 'urls' => ['/product/fsq', '/products/fsq', '/products/fsq.html']],
            ['titles' => ['真石漆'], 'urls' => ['/product/zsq', '/products/zsq', '/products/zsq.html']],
            ['titles' => ['产品中心'], 'urls' => ['/products', '/products.html', '/product_index.html']],
            ['titles' => ['涂料施工'], 'urls' => ['/page/construction', '/helps/tlsg.html']],
            ['titles' => ['工程案例'], 'urls' => ['/cases', '/cases.html', '/article_gcal.html']],
            // “走进科创美”曾与 /page/about 共用 URL；官方 about keeper 会先被挑选并
            // 改名为“走进金亚”，其余同名旧入口再按标题隐藏。
            ['titles' => ['走进科创美'], 'urls' => []],
        ];
        $matchesLegacy = function (array $row) use ($legacyRules) {
            $title = isset($row['title']) ? trim((string)$row['title']) : '';
            $url = isset($row['url']) ? trim((string)$row['url']) : '';
            foreach ($legacyRules as $rule) {
                if ($title !== '' && in_array($title, $rule['titles'], true)) {
                    return true;
                }
                if ($url !== '' && in_array($url, $rule['urls'], true)) {
                    return true;
                }
            }
            return false;
        };

        // 先确定每个官方入口的 keeper，避免把唯一的旧“走进科创美 /page/about”
        // 先隐藏掉，从而丢失其现有子导航关系。
        $keepers = [];
        $usedIds = [];
        foreach ($official as $index => $definition) {
            $best = null;
            $bestScore = -1;
            foreach ($rows as $row) {
                $id = isset($row['id']) ? (int)$row['id'] : 0;
                $parentId = isset($row['parent_id']) ? (int)$row['parent_id'] : 0;
                if ($id <= 0 || $parentId !== 0 || isset($usedIds[$id]) || !$matchesOfficial($row, $definition)) {
                    continue;
                }
                $score = 0;
                if ((string)$row['url'] === $definition['url']) $score += 100;
                if ($definition['slug'] !== '' && (string)$row['slug'] === $definition['slug']) $score += 40;
                if ((string)$row['title'] === $definition['title']) $score += 20;
                if (!isset($row['deletetime']) || $row['deletetime'] === null || $row['deletetime'] === '') $score += 10;
                if (isset($row['status']) && (string)$row['status'] === 'normal') $score += 5;
                if ($best === null || $score > $bestScore || ($score === $bestScore && $id < (int)$best['id'])) {
                    $best = $row;
                    $bestScore = $score;
                }
            }
            if ($best !== null) {
                $keepers[$index] = $best;
                $usedIds[(int)$best['id']] = true;
            }
        }

        // 兼容历史权重：先参考旧一级导航最高权重生成 7 个 canonical 权重；旧行随后会物理删除。
        $maxCustomWeight = 0;
        foreach ($rows as $row) {
            $id = isset($row['id']) ? (int)$row['id'] : 0;
            $parentId = isset($row['parent_id']) ? (int)$row['parent_id'] : 0;
            if ($parentId !== 0) {
                continue;
            }
            $isOfficialCandidate = false;
            foreach ($official as $definition) {
                if ($matchesOfficial($row, $definition)) {
                    $isOfficialCandidate = true;
                    break;
                }
            }
            if ($isOfficialCandidate || $matchesLegacy($row)) {
                continue;
            }
            $maxCustomWeight = max($maxCustomWeight, isset($row['weigh']) ? (int)$row['weigh'] : 0);
        }
        $topWeight = max(1000, $maxCustomWeight + 70);
        $now = time();

        $canonicalIds = [];
        foreach ($official as $index => $definition) {
            $weight = $topWeight - ($index * 10);
            if (isset($keepers[$index])) {
                $id = (int)$keepers[$index]['id'];
                $canonicalIds[$id] = true;
                $update = $pdo->prepare(
                    "UPDATE `{$table}` SET `parent_id`=0,`title`=?,`slug`=?,`url`=?,`link_type`='url',`link_value`=?," .
                    "`target`='_self',`position`='header',`pc_visible`=1,`mobile_visible`=1,`weigh`=?,`status`='normal'," .
                    "`deletetime`=NULL,`updatetime`=? WHERE `id`=?"
                );
                $update->execute([
                    $definition['title'], $definition['slug'], $definition['url'], $definition['url'],
                    $weight, $now, $id,
                ]);
                continue;
            }

            $insert = $pdo->prepare(
                "INSERT INTO `{$table}` (`parent_id`,`title`,`slug`,`url`,`link_type`,`link_value`,`target`,`icon`,`position`," .
                "`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) " .
                "VALUES (0,?,?,?,'url',?,'_self','','header',1,1,?,'normal',?,?)"
            );
            $insert->execute([
                $definition['title'], $definition['slug'], $definition['url'], $definition['url'],
                $weight, $now, $now,
            ]);
        }

        // Header 导航严格收敛为 7 个官方一级入口。
        // 用户已明确要求不保留软删除记录：除了 canonical keeper 外，所有历史一级导航、
        // 重复导航、自定义旧入口以及任何 Header 子导航都直接从表中物理删除。
        // 只删除本轮 SELECT 已读取到的旧行，因此刚刚 INSERT 的 canonical 行不会被误删。
        $deleteIds = [];
        foreach ($rows as $row) {
            $id = isset($row['id']) ? (int)$row['id'] : 0;
            if ($id <= 0 || isset($canonicalIds[$id])) {
                continue;
            }
            $deleteIds[$id] = true;
        }

        if ($deleteIds) {
            $delete = $pdo->prepare("DELETE FROM `{$table}` WHERE `id`=?");
            foreach (array_keys($deleteIds) as $id) {
                $delete->execute([(int)$id]);
            }
        }
    }

    /**
     * 根据当前项目表前缀渲染安装 SQL。
     *
     * @param string $prefix
     * @return string
     */
    public function renderSql($prefix)
    {
        return $this->renderSchemaSql($prefix)
            . "\n\n"
            . $this->renderCloneSql($prefix)
            . "\n\n"
            . $this->renderJinyaProductCatalogSql($prefix)
            . "\n\n"
            . $this->renderHtmlBaselineSql($prefix);
    }

    /**
     * 渲染 CMS 表结构与基础权限 SQL。
     *
     * @param string $prefix
     * @return string
     */
    public function renderSchemaSql($prefix)
    {
        $prefix = $this->normalizePrefix($prefix);
        $file = ROOT_PATH . 'database' . DS . 'cms.sql';
        if (!is_file($file)) {
            throw new \RuntimeException('找不到 CMS 安装脚本：database/cms.sql');
        }
        $sql = file_get_contents($file);
        if ($sql === false || trim($sql) === '') {
            throw new \RuntimeException('CMS 安装脚本为空：database/cms.sql');
        }
        return str_replace('fa_', $prefix, $sql);
    }

    /**
     * 渲染严格克隆内容 SQL。旧表字段升级完成后才能执行。
     *
     * @param string $prefix
     * @return string
     */
    public function renderCloneSql($prefix)
    {
        $prefix = $this->normalizePrefix($prefix);
        $cloneFile = ROOT_PATH . 'database' . DS . 'cms_clone_content.sql';
        if (!is_file($cloneFile)) {
            throw new \RuntimeException('找不到严格克隆内容脚本：database/cms_clone_content.sql');
        }
        $cloneSql = file_get_contents($cloneFile);
        if ($cloneSql === false || trim($cloneSql) === '') {
            throw new \RuntimeException('严格克隆内容脚本为空：database/cms_clone_content.sql');
        }
        return str_replace('fa_', $prefix, $cloneSql);
    }

    /**
     * 渲染金亚包装产品目录默认数据。
     *
     * 该脚本只补充缺失的分类、产品、图片、参数和内容区块，
     * 不修改首页产品中心结构/样式/引用，也不会覆盖后台已有同 slug 产品。
     *
     * @param string $prefix
     * @return string
     */
    public function renderJinyaProductCatalogSql($prefix)
    {
        $prefix = $this->normalizePrefix($prefix);
        $catalogFile = ROOT_PATH . 'database' . DS . 'cms_jinya_product_catalog.sql';
        if (!is_file($catalogFile)) {
            throw new \RuntimeException('找不到金亚产品目录脚本：database/cms_jinya_product_catalog.sql');
        }
        $catalogSql = file_get_contents($catalogFile);
        if ($catalogSql === false || trim($catalogSql) === '') {
            throw new \RuntimeException('金亚产品目录脚本为空：database/cms_jinya_product_catalog.sql');
        }
        return str_replace('fa_', $prefix, $catalogSql);
    }

    /**
     * 渲染当前 html/ 静态页面对应的 CMS 内容基线。
     *
     * 必须在结构升级、克隆数据、产品目录和页面 Schema 同步之后执行，
     * 使初始化数据库最终数据以当前静态参考页面为准。
     *
     * @param string $prefix
     * @return string
     */
    public function renderHtmlBaselineSql($prefix)
    {
        $prefix = $this->normalizePrefix($prefix);
        $file = ROOT_PATH . 'database' . DS . 'cms_html_baseline.sql';
        if (!is_file($file)) {
            throw new \RuntimeException('找不到 HTML 基线 CMS 脚本：database/cms_html_baseline.sql');
        }
        $sql = file_get_contents($file);
        if ($sql === false || trim($sql) === '') {
            throw new \RuntimeException('HTML 基线 CMS 脚本为空：database/cms_html_baseline.sql');
        }
        return str_replace('fa_', $prefix, $sql);
    }

    /**
     * 退役历史 Banner 独立视频海报字段。
     * 视频封面统一复用 image/mobile_image，两个旧列存在时精确删除。
     *
     * @param mixed  $connection
     * @param string $prefix
     * @return void
     */
    protected function retireBannerPosterColumns($connection, $prefix)
    {
        $table = $prefix . 'cms_banner';
        if (!$this->tableExists($connection, $table)) {
            return;
        }
        if ($this->columnExists($connection, $table, 'poster_image')) {
            $this->getPdo($connection)->exec(
                "ALTER TABLE `{$table}` DROP COLUMN `poster_image`"
            );
        }
        if ($this->columnExists($connection, $table, 'mobile_poster_image')) {
            $this->getPdo($connection)->exec(
                "ALTER TABLE `{$table}` DROP COLUMN `mobile_poster_image`"
            );
        }
    }

    /**
     * Ensure existing Banner tables can persist structured selling-point highlights.
     * MySQL 5.7 does not require or use a TEXT default here; an absent value is read as empty.
     *
     * @param mixed  $connection
     * @param string $prefix
     * @return void
     */
    protected function ensureBannerHighlightsColumn($connection, $prefix)
    {
        $table = $prefix . 'cms_banner';
        if (!$this->tableExists($connection, $table)
            || $this->columnExists($connection, $table, 'highlights_json')) {
            return;
        }
        $this->getPdo($connection)->exec(
            "ALTER TABLE `{$table}` ADD COLUMN `highlights_json` TEXT COMMENT 'Banner 卖点标签 JSON' AFTER `button_text`"
        );
    }

    /**
     * 将未被后台编辑的历史首页首屏 Banner 默认值升级为当前视觉基线。
     *
     * 这里做持久化迁移，而不是在渲染阶段临时替换，确保后台回显、
     * API/Repository 数据和前台最终展示始终使用同一份数据库真值。
     *
     * @param mixed  $connection
     * @param string $prefix
     * @return void
     */
    protected function migrateLegacyHomeHeroBannerDefaults($connection, $prefix)
    {
        $table = $prefix . 'cms_banner';
        if (!$this->tableExists($connection, $table)
            || !$this->columnExists($connection, $table, 'highlights_json')
            || !$this->columnExists($connection, $table, 'edited_by_admin')) {
            return;
        }

        $pdo = $this->getPdo($connection);
        $select = $pdo->prepare(
            "SELECT `id`,`title`,`subtitle`,`highlights_json` FROM `{$table}` " .
            "WHERE `page_key`=? AND `position`=? AND `edited_by_admin`=0 ORDER BY `weigh` DESC,`id` ASC"
        );
        $select->execute(['home', 'hero']);
        $rows = $select->fetchAll(\PDO::FETCH_ASSOC);
        if (!$rows) {
            return;
        }

        $iconMap = [
            0 => ['fa fa-users', '/assets/jinya/img/home-highlight-team.png'],
            1 => ['fa fa-shield', '/assets/jinya/img/home-highlight-quality.png'],
            2 => ['fa fa-truck', '/assets/jinya/img/home-highlight-delivery.png'],
        ];
        $update = $pdo->prepare(
            "UPDATE `{$table}` SET `title`=?,`subtitle`=?,`highlights_json`=?,`updatetime`=UNIX_TIMESTAMP() " .
            "WHERE `id`=? AND `edited_by_admin`=0"
        );

        foreach ($rows as $row) {
            $title = isset($row['title']) ? (string)$row['title'] : '';
            $subtitle = isset($row['subtitle']) ? (string)$row['subtitle'] : '';
            $json = isset($row['highlights_json']) ? (string)$row['highlights_json'] : '';
            $changed = false;

            if (trim($title) === '高品质包装印刷 一站式按需定制') {
                $title = '高质量无版印刷';
                $changed = true;
            }
            if (trim($subtitle) === 'JINYA PACKAGE · 一站式按需定制') {
                $subtitle = '不干胶·包装袋 一站式按需定制';
                $changed = true;
            }

            $items = json_decode($json, true);
            if (is_array($items)) {
                foreach ($iconMap as $index => $mapping) {
                    if (!isset($items[$index]) || !is_array($items[$index])) {
                        continue;
                    }
                    $icon = isset($items[$index]['icon']) ? trim((string)$items[$index]['icon']) : '';
                    if ($icon === $mapping[0]) {
                        $items[$index]['icon'] = $mapping[1];
                        $changed = true;
                    }
                }
                if (isset($items[1]['text'])
                    && trim((string)$items[1]['text']) === '品质为先 省心高效') {
                    $items[1]['text'] = '品质为先 省心高效 合作共赢';
                    $changed = true;
                }
                if ($changed) {
                    $encoded = json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    if ($encoded !== false) {
                        $json = $encoded;
                    }
                }
            }

            if ($changed) {
                $update->execute([$title, $subtitle, $json, (int)$row['id']]);
            }
        }
    }

    /**
     * 为首页内容引用补齐终端专用展示图。
     * 这些字段只改变首页推荐关系的视觉素材，不覆盖产品自身封面。
     *
     * @param mixed  $connection
     * @param string $prefix
     * @return void
     */
    protected function ensureHomeReferenceDisplayImageColumns($connection, $prefix)
    {
        $table = $prefix . 'cms_home_section_reference';
        if (!$this->tableExists($connection, $table)) {
            return;
        }

        $pdo = $this->getPdo($connection);
        if (!$this->columnExists($connection, $table, 'pc_image')) {
            $pdo->exec(
                "ALTER TABLE `{$table}` ADD COLUMN `pc_image` varchar(255) NOT NULL DEFAULT '' COMMENT '首页 PC 专用展示图' AFTER `content_id`"
            );
        }
        if (!$this->columnExists($connection, $table, 'mobile_image')) {
            $pdo->exec(
                "ALTER TABLE `{$table}` ADD COLUMN `mobile_image` varchar(255) NOT NULL DEFAULT '' COMMENT '首页移动端专用展示图' AFTER `pc_image`"
            );
        }
    }

    /**
     * Persist canonical homepage defaults for existing databases without
     * overwriting administrator-provided values.
     *
     * @param mixed  $connection
     * @param string $prefix
     * @return void
     */
    /**
     * 为历史首页“关于金亚”配置补齐社交入口默认图标。
     *
     * 只在 JSON key 不存在时补值；管理员主动保存为空字符串后不会再次补回。
     */
    protected function ensureHomeAboutSocialIconDefaults($connection, $prefix)
    {
        $table = $prefix . 'cms_home_section';
        if (!$this->tableExists($connection, $table)) {
            return;
        }

        $pdo = $this->getPdo($connection);
        $select = $pdo->prepare("SELECT `id`,`config_json` FROM `{$table}` WHERE `section_key`=? LIMIT 1");
        $select->execute(['about']);
        $row = $select->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            return;
        }

        $config = json_decode(isset($row['config_json']) ? (string)$row['config_json'] : '', true);
        $config = is_array($config) ? $config : [];
        $defaults = [
            'social_icon_1' => '/assets/jinya/img/social-wechat.png',
            'social_icon_2' => '/assets/jinya/img/home-video-channels.png',
        ];
        $changed = false;
        foreach ($defaults as $key => $value) {
            if (!array_key_exists($key, $config)) {
                $config[$key] = $value;
                $changed = true;
            }
        }
        if (!$changed) {
            return;
        }

        $encoded = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($encoded === false) {
            return;
        }
        $update = $pdo->prepare("UPDATE `{$table}` SET `config_json`=?,`updatetime`=UNIX_TIMESTAMP() WHERE `id`=?");
        $update->execute([$encoded, (int)$row['id']]);
    }

    /**
     * 将历史科创美 Footer 导航一次性迁移为当前金亚页脚导航。
     *
     * 仅在以下两种情况下写数据库：
     * 1. Footer 从未创建过（新安装）；
     * 2. 检测到明确的旧科创美导航痕迹。
     *
     * 一旦已经是新结构，后续 cms:install 不再覆盖运营人员在“公共布局”中
     * 对 Footer 导航所做的自定义调整。
     * 历史 Footer 使用物理删除，避免公共布局继续读取软删除残留。
     *
     * @param mixed  $connection
     * @param string $prefix
     * @return void
     */
    protected function normalizeLegacyFooterNavigation($connection, $prefix)
    {
        $table = $prefix . 'cms_navigation';
        if (!$this->tableExists($connection, $table)) {
            return;
        }

        $pdo = $this->getPdo($connection);
        $select = $pdo->prepare(
            "SELECT `id`,`parent_id`,`title`,`slug`,`url`,`status`,`deletetime` FROM `{$table}` WHERE `position`=? ORDER BY `id` ASC"
        );
        $select->execute(['footer']);
        $rows = $select->fetchAll(\PDO::FETCH_ASSOC);
        $rows = is_array($rows) ? $rows : [];

        $legacyTitles = [
            '仿石漆', '真石漆', '涂料施工', '工程案例',
            '走进科创美', '联系科创美', '视频中心',
        ];
        $legacyUrls = [
            '/product/fsq', '/products/fsq', '/products/fsq.html',
            '/product/zsq', '/products/zsq', '/products/zsq.html',
            '/helps/tlsg.html', '/page/construction',
            '/article_gcal.html', '/cases', '/cases.html',
            '/helps/zjkcm.html', '/helps/lxkcm.html', '/sitemap.html',
        ];

        $hasLegacyFootprint = false;
        foreach ($rows as $row) {
            $title = isset($row['title']) ? trim((string)$row['title']) : '';
            $url = isset($row['url']) ? trim((string)$row['url']) : '';
            if (in_array($title, $legacyTitles, true) || in_array($url, $legacyUrls, true)) {
                $hasLegacyFootprint = true;
                break;
            }
        }

        // 已经迁移过的 Footer 完全交给公共布局维护，不在安装器中反复重置。
        if ($rows && !$hasLegacyFootprint) {
            return;
        }

        if ($rows) {
            $delete = $pdo->prepare("DELETE FROM `{$table}` WHERE `position`=?");
            $delete->execute(['footer']);
        }

        $canonical = [
            ['首页', '', '/', 1000],
            ['不干胶/卷标', 'label', '/page/label', 990],
            ['包装袋无版印刷', 'bags', '/page/bags', 980],
            ['彩盒', 'boxes', '/page/boxes', 970],
            ['走进金亚', 'about', '/page/about', 960],
            ['新闻动态', 'news', '/news', 950],
            ['联系我们', 'contact', '/page/contact', 940],
            ['网站地图', 'sitemap', '/sitemap', 930],
        ];
        $insert = $pdo->prepare(
            "INSERT INTO `{$table}` (`parent_id`,`title`,`slug`,`url`,`link_type`,`link_value`,`target`,`icon`,`position`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) " .
            "VALUES (0,?,?,?,?,?,'_self','',?,1,1,?,'normal',?,?)"
        );
        $now = time();
        foreach ($canonical as $row) {
            $insert->execute([$row[0], $row[1], $row[2], 'url', $row[2], 'footer', $row[3], $now, $now]);
        }
    }

    protected function ensureHomeSectionDefaults($connection, $prefix)
    {
        $table = $prefix . 'cms_home_section';
        if (!$this->tableExists($connection, $table)
            || !$this->columnExists($connection, $table, 'background_image')) {
            return;
        }
        if (!class_exists(__NAMESPACE__ . '\\HomeSectionDefaults', false)) {
            require_once __DIR__ . '/HomeSectionDefaults.php';
        }
        $pdo = $this->getPdo($connection);
        $this->ensureHomeSectionDefaultRow($pdo, $table, 'workshop', HomeSectionDefaults::workshop());
        $this->ensureHomeSectionDefaultRow($pdo, $table, 'culture', HomeSectionDefaults::culture());

        $statement = $pdo->prepare(
            "UPDATE `{$table}` SET `background_image`=?,`updatetime`=UNIX_TIMESTAMP() " .
            "WHERE `section_key`='service' AND (`background_image` IS NULL OR `background_image`='')"
        );
        $statement->execute([HomeSectionDefaults::SERVICE_BACKGROUND_IMAGE]);

        $statement = $pdo->prepare(
            "UPDATE `{$table}` SET `background_image`=?,`updatetime`=UNIX_TIMESTAMP() " .
            "WHERE `section_key`='company' AND (`background_image` IS NULL OR `background_image`='')"
        );
        $statement->execute([HomeSectionDefaults::COMPANY_BACKGROUND_IMAGE]);

        if ($this->columnExists($connection, $table, 'mobile_background_image')) {
            $statement = $pdo->prepare(
                "UPDATE `{$table}` SET `mobile_background_image`=?,`updatetime`=UNIX_TIMESTAMP() " .
                "WHERE `section_key`='company' AND (`mobile_background_image` IS NULL OR `mobile_background_image`='')"
            );
            $statement->execute([HomeSectionDefaults::COMPANY_MOBILE_BACKGROUND_IMAGE]);
        }
    }

    /**
     * Insert a canonical homepage section and refresh only untouched legacy rows.
     * Administrator-edited content/status must never be overwritten by install.
     *
     * @param \PDO  $pdo
     * @param string $table
     * @param string $sectionKey
     * @param array  $defaults
     * @return void
     */
    protected function ensureHomeSectionDefaultRow($pdo, $table, $sectionKey, array $defaults)
    {
        $config = isset($defaults['config']) && is_array($defaults['config']) ? $defaults['config'] : [];
        $configJson = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($configJson === false) {
            throw new \RuntimeException('首页默认配置 JSON 编码失败：' . $sectionKey);
        }

        $backgroundImage = isset($defaults['background_image']) ? (string)$defaults['background_image'] : '';
        $mobileBackgroundImage = isset($defaults['mobile_background_image']) ? (string)$defaults['mobile_background_image'] : '';

        $insert = $pdo->prepare(
            "INSERT IGNORE INTO `{$table}` (`section_key`,`section_name`,`title`,`subtitle`,`background_image`,`mobile_background_image`,`config_json`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) " .
            "VALUES (?,?,?,?,?,?,?,?,?,?,?,UNIX_TIMESTAMP(),UNIX_TIMESTAMP())"
        );
        if ($insert) {
            $insert->execute([
                $sectionKey,
                isset($defaults['section_name']) ? (string)$defaults['section_name'] : '',
                isset($defaults['title']) ? (string)$defaults['title'] : '',
                isset($defaults['subtitle']) ? (string)$defaults['subtitle'] : '',
                $backgroundImage,
                $mobileBackgroundImage,
                $configJson,
                !empty($defaults['pc_visible']) ? 1 : 0,
                !empty($defaults['mobile_visible']) ? 1 : 0,
                isset($defaults['weigh']) ? (int)$defaults['weigh'] : 0,
                isset($defaults['status']) ? (string)$defaults['status'] : 'normal',
            ]);
        }

        $update = $pdo->prepare(
            "UPDATE `{$table}` SET `section_name`=?,`title`=?,`subtitle`=?,`background_image`=?,`mobile_background_image`=?,`config_json`=?,`pc_visible`=?,`mobile_visible`=?,`status`=?,`updatetime`=UNIX_TIMESTAMP() " .
            "WHERE `section_key`=? AND `edited_by_admin`=0"
        );
        if ($update) {
            $update->execute([
                isset($defaults['section_name']) ? (string)$defaults['section_name'] : '',
                isset($defaults['title']) ? (string)$defaults['title'] : '',
                isset($defaults['subtitle']) ? (string)$defaults['subtitle'] : '',
                $backgroundImage,
                $mobileBackgroundImage,
                $configJson,
                !empty($defaults['pc_visible']) ? 1 : 0,
                !empty($defaults['mobile_visible']) ? 1 : 0,
                isset($defaults['status']) ? (string)$defaults['status'] : 'normal',
                $sectionKey,
            ]);
        }

        $this->repairEmptyHomeSectionDefaultItems($pdo, $table, $sectionKey, $defaults, $configJson);
    }

    /**
     * Repair old workshop/culture placeholder rows that were marked edited by
     * earlier admin saves before any structured items existed. A row that has
     * at least one item is treated as real administrator content and is never
     * overwritten here.
     *
     * @param \PDO   $pdo
     * @param string $table
     * @param string $sectionKey
     * @param array  $defaults
     * @param string $configJson
     * @return void
     */
    protected function repairEmptyHomeSectionDefaultItems($pdo, $table, $sectionKey, array $defaults, $configJson)
    {
        if (!in_array($sectionKey, ['workshop', 'culture'], true)) {
            return;
        }

        $select = $pdo->prepare(
            "SELECT `edited_by_admin`,`config_json` FROM `{$table}` WHERE `section_key`=? LIMIT 1"
        );
        if (!$select) {
            return;
        }
        $select->execute([$sectionKey]);
        $row = $select->fetch(\PDO::FETCH_ASSOC);
        if (!$row || empty($row['edited_by_admin']) || !$this->homeSectionConfigNeedsDefaultItems(isset($row['config_json']) ? $row['config_json'] : '')) {
            return;
        }

        $backgroundImage = isset($defaults['background_image']) ? (string)$defaults['background_image'] : '';
        $mobileBackgroundImage = isset($defaults['mobile_background_image']) ? (string)$defaults['mobile_background_image'] : '';
        $repair = $pdo->prepare(
            "UPDATE `{$table}` SET " .
            "`section_name`=IF(`section_name` IS NULL OR `section_name`='',?,`section_name`)," .
            "`title`=IF(`title` IS NULL OR `title`='',?,`title`)," .
            "`subtitle`=IF(`subtitle` IS NULL OR `subtitle`='',?,`subtitle`)," .
            "`background_image`=IF(`background_image` IS NULL OR `background_image`='',?,`background_image`)," .
            "`mobile_background_image`=IF(`mobile_background_image` IS NULL OR `mobile_background_image`='',?,`mobile_background_image`)," .
            "`config_json`=?,`pc_visible`=1,`mobile_visible`=1,`status`='normal',`updatetime`=UNIX_TIMESTAMP() " .
            "WHERE `section_key`=? AND `edited_by_admin`=1"
        );
        if (!$repair) {
            return;
        }
        $repair->execute([
            isset($defaults['section_name']) ? (string)$defaults['section_name'] : '',
            isset($defaults['title']) ? (string)$defaults['title'] : '',
            isset($defaults['subtitle']) ? (string)$defaults['subtitle'] : '',
            $backgroundImage,
            $mobileBackgroundImage,
            $configJson,
            $sectionKey,
        ]);
    }

    /**
     * Return true when a persisted home-section config has no structured item
     * rows and is therefore only a legacy placeholder, not administrator data.
     *
     * @param mixed $configJson
     * @return bool
     */
    protected function homeSectionConfigNeedsDefaultItems($configJson)
    {
        $configJson = trim((string)$configJson);
        if ($configJson === '') {
            return true;
        }
        $config = json_decode($configJson, true);
        if (!is_array($config)) {
            return true;
        }
        return !isset($config['items']) || !is_array($config['items']) || count($config['items']) === 0;
    }


    /**
     * Seed the Jinya label page and its semantic body blocks. This is separate
     * from the frozen clone content SQL because the label page comes from the
     * approved V12-R88 static reference, not from the KCM strict clone.
     */
    protected function ensureLabelPageDefaults($connection, $prefix)
    {
        $pageTable = $prefix . 'cms_page';
        $blockTable = $prefix . 'cms_page_content_block';
        if (!$this->tableExists($connection, $pageTable) || !$this->tableExists($connection, $blockTable)) {
            return;
        }
        if (!class_exists(__NAMESPACE__ . '\\LabelPageDefaults', false)) {
            require_once __DIR__ . '/LabelPageDefaults.php';
        }
        $pdo = $this->getPdo($connection);
        $page = LabelPageDefaults::page();
        $pageId = $this->ensureLabelPageRow($pdo, $pageTable, $page);
        if (!$pageId) {
            return;
        }
        foreach (LabelPageDefaults::blocks() as $block) {
            $this->ensureLabelPageBlockRow($pdo, $blockTable, $pageId, $block);
        }
        $this->purgeRetiredLabelElementItems($pdo, $blockTable, $pageId);
        $this->purgeRetiredPageContentBlocks($pdo, $blockTable, $pageId, ['body','label_glue_notes','label_faq','label_applications','label_process']);
    }

    /**
     * Remove the retired direction/core helper rows embedded inside the
     * label_elements JSON while preserving all administrator values belonging
     * to the three supported intro items.
     */
    protected function purgeRetiredLabelElementItems($pdo, $table, $pageId)
    {
        if (!class_exists(__NAMESPACE__ . '\\LabelElementsConfigSanitizer', false)) {
            require_once __DIR__ . '/LabelElementsConfigSanitizer.php';
        }
        $select = $pdo->prepare(
            "SELECT `id`,`extra_json` FROM `{$table}` WHERE `page_id`=? AND `block_key`='label_elements' AND `deletetime` IS NULL ORDER BY `id` ASC"
        );
        if (!$select) {
            return;
        }
        $select->execute([(int)$pageId]);
        $rows = $select->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $extra = json_decode(isset($row['extra_json']) ? (string)$row['extra_json'] : '', true);
            if (!is_array($extra)) {
                continue;
            }
            $clean = LabelElementsConfigSanitizer::sanitizeExtra($extra);
            if ($clean === $extra) {
                continue;
            }
            $json = json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($json === false) {
                throw new \RuntimeException('卷标三要素历史数据清理 JSON 编码失败');
            }
            $update = $pdo->prepare("UPDATE `{$table}` SET `extra_json`=?,`updatetime`=UNIX_TIMESTAMP() WHERE `id`=?");
            if ($update) {
                $update->execute([$json, (int)$row['id']]);
            }
        }
    }

    /**
     * Permanently remove structured page blocks that have been replaced by the
     * current approved page design. These rows are intentionally deleted even
     * when they were edited by an administrator: the retired block keys are no
     * longer rendered or supported by the active templates.
     */
    protected function purgeRetiredPageContentBlocks($pdo, $table, $pageId, array $keys)
    {
        if (!$keys) {
            return;
        }
        $keys = array_values(array_unique(array_filter(array_map('strval', $keys), 'strlen')));
        if (!$keys) {
            return;
        }
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $statement = $pdo->prepare(
            "DELETE FROM `{$table}` WHERE `page_id`=? AND `block_key` IN ({$placeholders})"
        );
        if ($statement) {
            $statement->execute(array_merge([(int)$pageId], $keys));
        }
    }

    protected function ensureLabelPageRow($pdo, $table, array $page)
    {
        $select = $pdo->prepare("SELECT `id`,`edited_by_admin`,`template`,`page_type` FROM `{$table}` WHERE `slug`='label' AND `deletetime` IS NULL LIMIT 1");
        $select->execute();
        $row = $select->fetch(\PDO::FETCH_ASSOC);
        $sourceKey = 'jinya:page:label';
        $content = '不干胶/卷标页面主体由结构化功能块动态渲染。';
        if (!$row) {
            $insert = $pdo->prepare(
                "INSERT INTO `{$table}` (`title`,`slug`,`template`,`page_type`,`summary`,`content`,`mobile_content`,`weigh`,`status`,`publish_time`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) " .
                "VALUES (?,?,?,?,?,?,?,?,'published',UNIX_TIMESTAMP(),?,?,?,?,?,0,UNIX_TIMESTAMP(),UNIX_TIMESTAMP())"
            );
            $insert->execute([
                (string)$page['title'], 'label', 'label', 'label', (string)$page['summary'], $content, $content,
                (int)$page['weigh'], (string)$page['seo_title'], (string)$page['seo_keywords'],
                (string)$page['seo_description'], '/page/label', $sourceKey,
            ]);
            return (int)$pdo->lastInsertId();
        }

        $pageId = (int)$row['id'];
        if (empty($row['edited_by_admin'])) {
            $update = $pdo->prepare(
                "UPDATE `{$table}` SET `title`=?,`template`='label',`page_type`='label',`summary`=?,`content`=?,`mobile_content`=?,`weigh`=?,`status`='published'," .
                "`publish_time`=COALESCE(`publish_time`,UNIX_TIMESTAMP()),`seo_title`=?,`seo_keywords`=?,`seo_description`=?,`canonical_url`='/page/label',`source_key`=?,`updatetime`=UNIX_TIMESTAMP() WHERE `id`=? AND `edited_by_admin`=0"
            );
            $update->execute([
                (string)$page['title'], (string)$page['summary'], $content, $content, (int)$page['weigh'],
                (string)$page['seo_title'], (string)$page['seo_keywords'], (string)$page['seo_description'], $sourceKey, $pageId,
            ]);
        } else {
            // Template/type are structural routing metadata for the reserved
            // label slug; preserving administrator copy/media remains the rule.
            $update = $pdo->prepare(
                "UPDATE `{$table}` SET `template`='label',`page_type`='label',`canonical_url`='/page/label',`updatetime`=UNIX_TIMESTAMP() WHERE `id`=?"
            );
            $update->execute([$pageId]);
        }
        return $pageId;
    }

    protected function ensureLabelPageBlockRow($pdo, $table, $pageId, array $block)
    {
        $sourceKey = 'jinya:label:' . preg_replace('/^label_/', '', (string)$block['block_key']);
        $extraJson = json_encode(isset($block['extra']) && is_array($block['extra']) ? $block['extra'] : [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($extraJson === false) {
            throw new \RuntimeException('不干胶页面默认配置 JSON 编码失败：' . $block['block_key']);
        }
        $select = $pdo->prepare(
            "SELECT `id`,`edited_by_admin`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`extra_json` FROM `{$table}` " .
            "WHERE `page_id`=? AND `deletetime` IS NULL AND (`source_key`=? OR `block_key`=?) ORDER BY (`source_key`=?) DESC,`id` ASC LIMIT 1"
        );
        $select->execute([(int)$pageId, $sourceKey, (string)$block['block_key'], $sourceKey]);
        $row = $select->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            $insert = $pdo->prepare(
                "INSERT INTO `{$table}` (`page_id`,`block_key`,`block_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`link_text`,`link_url`,`extra_json`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) " .
                "VALUES (?,?,?,?,?,?,?,?,?,?,?,?,0,?,?,?,?,UNIX_TIMESTAMP(),UNIX_TIMESTAMP())"
            );
            $insert->execute([
                (int)$pageId, (string)$block['block_key'], 'label_section', (string)$block['title'], (string)$block['subtitle'],
                (string)$block['content'], (string)$block['image'], (string)$block['mobile_image'], (string)$block['link_text'],
                (string)$block['link_url'], $extraJson, $sourceKey, (int)$block['pc_visible'], (int)$block['mobile_visible'],
                (int)$block['weigh'], (string)$block['status'],
            ]);
            return;
        }

        if (empty($row['edited_by_admin']) || $this->isStructurallyEmptyLabelBlock($row)) {
            $update = $pdo->prepare(
                "UPDATE `{$table}` SET `block_key`=?,`block_type`='label_section',`title`=?,`subtitle`=?,`content`=?,`image`=?,`mobile_image`=?,`link_text`=?,`link_url`=?," .
                "`extra_json`=?,`source_key`=?,`pc_visible`=?,`mobile_visible`=?,`weigh`=?,`status`=?,`updatetime`=UNIX_TIMESTAMP() WHERE `id`=?"
            );
            $update->execute([
                (string)$block['block_key'], (string)$block['title'], (string)$block['subtitle'], (string)$block['content'],
                (string)$block['image'], (string)$block['mobile_image'], (string)$block['link_text'], (string)$block['link_url'],
                $extraJson, $sourceKey, (int)$block['pc_visible'], (int)$block['mobile_visible'], (int)$block['weigh'],
                (string)$block['status'], (int)$row['id'],
            ]);
        }
    }

    protected function isStructurallyEmptyLabelBlock(array $row)
    {
        foreach (['title', 'subtitle', 'content', 'image', 'mobile_image'] as $field) {
            if (isset($row[$field]) && trim((string)$row[$field]) !== '') {
                return false;
            }
        }
        $extra = json_decode(isset($row['extra_json']) ? (string)$row['extra_json'] : '', true);
        if (!is_array($extra)) {
            return true;
        }
        return !isset($extra['items']) || !is_array($extra['items']) || count($extra['items']) === 0;
    }



    /** Seed uploaded bags.html as a structured single page. */
    protected function ensureBagsPageDefaults($connection, $prefix)
    {
        $pageTable = $prefix . 'cms_page';
        $blockTable = $prefix . 'cms_page_content_block';
        if (!$this->tableExists($connection, $pageTable) || !$this->tableExists($connection, $blockTable)) return;
        if (!class_exists(__NAMESPACE__ . '\\BagsPageDefaults', false)) require_once __DIR__ . '/BagsPageDefaults.php';
        $pdo = $this->getPdo($connection);
        $page = BagsPageDefaults::page();
        $pageId = $this->ensureBagsPageRow($pdo, $pageTable, $page);
        if (!$pageId) return;
        foreach (BagsPageDefaults::blocks() as $block) $this->ensureBagsPageBlockRow($pdo, $blockTable, $pageId, $block);
        $this->purgeRetiredPageContentBlocks($pdo, $blockTable, $pageId, ['body']);
    }

    protected function ensureBagsPageRow($pdo, $table, array $page)
    {
        $select = $pdo->prepare("SELECT `id`,`edited_by_admin`,`template`,`page_type` FROM `{$table}` WHERE `slug`='bags' AND `deletetime` IS NULL LIMIT 1");
        $select->execute();
        $row = $select->fetch(\PDO::FETCH_ASSOC);
        $sourceKey = 'jinya:page:bags';
        $content = '包装袋·无版印刷页面主体由结构化功能块动态渲染。';
        if (!$row) {
            $insert = $pdo->prepare(
                "INSERT INTO `{$table}` (`title`,`slug`,`template`,`page_type`,`summary`,`content`,`mobile_content`,`weigh`,`status`,`publish_time`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) " .
                "VALUES (?,?,?,?,?,?,?,?,'published',UNIX_TIMESTAMP(),?,?,?,?,?,0,UNIX_TIMESTAMP(),UNIX_TIMESTAMP())"
            );
            $insert->execute([(string)$page['title'],'bags','bags','bags',(string)$page['summary'],$content,$content,(int)$page['weigh'],(string)$page['seo_title'],(string)$page['seo_keywords'],(string)$page['seo_description'],'/page/bags',$sourceKey]);
            return (int)$pdo->lastInsertId();
        }
        $pageId = (int)$row['id'];
        if (empty($row['edited_by_admin'])) {
            $update = $pdo->prepare(
                "UPDATE `{$table}` SET `title`=?,`template`='bags',`page_type`='bags',`summary`=?,`content`=?,`mobile_content`=?,`weigh`=?,`status`='published'," .
                "`publish_time`=COALESCE(`publish_time`,UNIX_TIMESTAMP()),`seo_title`=?,`seo_keywords`=?,`seo_description`=?,`canonical_url`='/page/bags',`source_key`=?,`updatetime`=UNIX_TIMESTAMP() WHERE `id`=? AND `edited_by_admin`=0"
            );
            $update->execute([(string)$page['title'],(string)$page['summary'],$content,$content,(int)$page['weigh'],(string)$page['seo_title'],(string)$page['seo_keywords'],(string)$page['seo_description'],$sourceKey,$pageId]);
        } else {
            $update = $pdo->prepare("UPDATE `{$table}` SET `template`='bags',`page_type`='bags',`canonical_url`='/page/bags',`updatetime`=UNIX_TIMESTAMP() WHERE `id`=?");
            $update->execute([$pageId]);
        }
        return $pageId;
    }

    protected function ensureBagsPageBlockRow($pdo, $table, $pageId, array $block)
    {
        $sourceKey = 'jinya:bags:' . preg_replace('/^bags_/', '', (string)$block['block_key']);
        $extraJson = json_encode(isset($block['extra']) && is_array($block['extra']) ? $block['extra'] : [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($extraJson === false) throw new \RuntimeException('包装袋页面默认配置 JSON 编码失败：' . $block['block_key']);
        $select = $pdo->prepare(
            "SELECT `id`,`edited_by_admin`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`extra_json`,`deletetime` FROM `{$table}` " .
            "WHERE `page_id`=? AND (`source_key`=? OR (`deletetime` IS NULL AND `block_key`=?)) " .
            "ORDER BY (`source_key`=?) DESC,`id` ASC LIMIT 1"
        );
        $select->execute([(int)$pageId,$sourceKey,(string)$block['block_key'],$sourceKey]);
        $row = $select->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            $insert = $pdo->prepare(
                "INSERT INTO `{$table}` (`page_id`,`block_key`,`block_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`link_text`,`link_url`,`extra_json`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) " .
                "VALUES (?,?,?,?,?,?,?,?,?,?,?,?,0,?,?,?,?,UNIX_TIMESTAMP(),UNIX_TIMESTAMP())"
            );
            $insert->execute([(int)$pageId,(string)$block['block_key'],'bags_section',(string)$block['title'],(string)$block['subtitle'],(string)$block['content'],(string)$block['image'],(string)$block['mobile_image'],(string)$block['link_text'],(string)$block['link_url'],$extraJson,$sourceKey,(int)$block['pc_visible'],(int)$block['mobile_visible'],(int)$block['weigh'],(string)$block['status']]);
            return;
        }
        if (empty($row['edited_by_admin']) || $this->isStructurallyEmptyBagsBlock($row)) {
            $update = $pdo->prepare(
                "UPDATE `{$table}` SET `block_key`=?,`block_type`='bags_section',`title`=?,`subtitle`=?,`content`=?,`image`=?,`mobile_image`=?,`link_text`=?,`link_url`=?," .
                "`extra_json`=?,`source_key`=?,`pc_visible`=?,`mobile_visible`=?,`weigh`=?,`status`=?,`updatetime`=UNIX_TIMESTAMP() WHERE `id`=?"
            );
            $update->execute([(string)$block['block_key'],(string)$block['title'],(string)$block['subtitle'],(string)$block['content'],(string)$block['image'],(string)$block['mobile_image'],(string)$block['link_text'],(string)$block['link_url'],$extraJson,$sourceKey,(int)$block['pc_visible'],(int)$block['mobile_visible'],(int)$block['weigh'],(string)$block['status'],(int)$row['id']]);
        }
    }

    protected function isStructurallyEmptyBagsBlock(array $row)
    {
        foreach (['title','subtitle','content','image','mobile_image'] as $field) if (isset($row[$field]) && trim((string)$row[$field]) !== '') return false;
        $extra = json_decode(isset($row['extra_json']) ? (string)$row['extra_json'] : '', true);
        return !is_array($extra) || !isset($extra['items']) || !is_array($extra['items']) || count($extra['items']) === 0;
    }


    /** Seed uploaded boxes.html as a structured single page. */
    protected function ensureBoxesPageDefaults($connection, $prefix)
    {
        $pageTable = $prefix . 'cms_page';
        $blockTable = $prefix . 'cms_page_content_block';
        if (!$this->tableExists($connection, $pageTable) || !$this->tableExists($connection, $blockTable)) return;
        if (!class_exists(__NAMESPACE__ . '\BoxesPageDefaults', false)) require_once __DIR__ . '/BoxesPageDefaults.php';
        $pdo = $this->getPdo($connection);
        $page = BoxesPageDefaults::page();
        $pageId = $this->ensureBoxesPageRow($pdo, $pageTable, $page);
        if (!$pageId) return;
        foreach (BoxesPageDefaults::blocks() as $block) $this->ensureBoxesPageBlockRow($pdo, $blockTable, $pageId, $block);
        $this->purgeRetiredPageContentBlocks($pdo, $blockTable, $pageId, ['body','boxes_purchase']);
    }

    protected function ensureBoxesPageRow($pdo, $table, array $page)
    {
        $select = $pdo->prepare("SELECT `id`,`edited_by_admin`,`template`,`page_type` FROM `{$table}` WHERE `slug`='boxes' AND `deletetime` IS NULL LIMIT 1");
        $select->execute();
        $row = $select->fetch(\PDO::FETCH_ASSOC);
        $sourceKey = 'jinya:page:boxes';
        $content = '彩盒页面主体由结构化功能块动态渲染。';
        if (!$row) {
            $insert = $pdo->prepare(
                "INSERT INTO `{$table}` (`title`,`slug`,`template`,`page_type`,`summary`,`content`,`mobile_content`,`weigh`,`status`,`publish_time`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) " .
                "VALUES (?,?,?,?,?,?,?,?,'published',UNIX_TIMESTAMP(),?,?,?,?,?,0,UNIX_TIMESTAMP(),UNIX_TIMESTAMP())"
            );
            $insert->execute([(string)$page['title'],'boxes','boxes','boxes',(string)$page['summary'],$content,$content,(int)$page['weigh'],(string)$page['seo_title'],(string)$page['seo_keywords'],(string)$page['seo_description'],'/page/boxes',$sourceKey]);
            return (int)$pdo->lastInsertId();
        }
        $pageId = (int)$row['id'];
        if (empty($row['edited_by_admin'])) {
            $update = $pdo->prepare(
                "UPDATE `{$table}` SET `title`=?,`template`='boxes',`page_type`='boxes',`summary`=?,`content`=?,`mobile_content`=?,`weigh`=?,`status`='published'," .
                "`publish_time`=COALESCE(`publish_time`,UNIX_TIMESTAMP()),`seo_title`=?,`seo_keywords`=?,`seo_description`=?,`canonical_url`='/page/boxes',`source_key`=?,`updatetime`=UNIX_TIMESTAMP() WHERE `id`=? AND `edited_by_admin`=0"
            );
            $update->execute([(string)$page['title'],(string)$page['summary'],$content,$content,(int)$page['weigh'],(string)$page['seo_title'],(string)$page['seo_keywords'],(string)$page['seo_description'],$sourceKey,$pageId]);
        } else {
            $update = $pdo->prepare("UPDATE `{$table}` SET `template`='boxes',`page_type`='boxes',`canonical_url`='/page/boxes',`updatetime`=UNIX_TIMESTAMP() WHERE `id`=?");
            $update->execute([$pageId]);
        }
        return $pageId;
    }

    protected function ensureBoxesPageBlockRow($pdo, $table, $pageId, array $block)
    {
        $sourceKey = 'jinya:boxes:' . preg_replace('/^boxes_/', '', (string)$block['block_key']);
        $extraJson = json_encode(isset($block['extra']) && is_array($block['extra']) ? $block['extra'] : [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($extraJson === false) throw new \RuntimeException('彩盒页面默认配置 JSON 编码失败：' . $block['block_key']);
        $select = $pdo->prepare(
            "SELECT `id`,`edited_by_admin`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`extra_json` FROM `{$table}` " .
            "WHERE `page_id`=? AND `deletetime` IS NULL AND (`source_key`=? OR `block_key`=?) ORDER BY (`source_key`=?) DESC,`id` ASC LIMIT 1"
        );
        $select->execute([(int)$pageId,$sourceKey,(string)$block['block_key'],$sourceKey]);
        $row = $select->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            $insert = $pdo->prepare(
                "INSERT INTO `{$table}` (`page_id`,`block_key`,`block_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`link_text`,`link_url`,`extra_json`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) " .
                "VALUES (?,?,?,?,?,?,?,?,?,?,?,?,0,?,?,?,?,UNIX_TIMESTAMP(),UNIX_TIMESTAMP())"
            );
            $insert->execute([(int)$pageId,(string)$block['block_key'],'boxes_section',(string)$block['title'],(string)$block['subtitle'],(string)$block['content'],(string)$block['image'],(string)$block['mobile_image'],(string)$block['link_text'],(string)$block['link_url'],$extraJson,$sourceKey,(int)$block['pc_visible'],(int)$block['mobile_visible'],(int)$block['weigh'],(string)$block['status']]);
            return;
        }
        if (empty($row['edited_by_admin']) || $this->isStructurallyEmptyBoxesBlock($row)) {
            $update = $pdo->prepare(
                "UPDATE `{$table}` SET `block_key`=?,`block_type`='boxes_section',`title`=?,`subtitle`=?,`content`=?,`image`=?,`mobile_image`=?,`link_text`=?,`link_url`=?," .
                "`extra_json`=?,`source_key`=?,`pc_visible`=?,`mobile_visible`=?,`weigh`=?,`status`=?,`deletetime`=NULL,`updatetime`=UNIX_TIMESTAMP() WHERE `id`=?"
            );
            $update->execute([(string)$block['block_key'],(string)$block['title'],(string)$block['subtitle'],(string)$block['content'],(string)$block['image'],(string)$block['mobile_image'],(string)$block['link_text'],(string)$block['link_url'],$extraJson,$sourceKey,(int)$block['pc_visible'],(int)$block['mobile_visible'],(int)$block['weigh'],(string)$block['status'],(int)$row['id']]);
        }
    }

    protected function isStructurallyEmptyBoxesBlock(array $row)
    {
        foreach (['title','subtitle','content','image','mobile_image'] as $field) if (isset($row[$field]) && trim((string)$row[$field]) !== '') return false;
        $extra = json_decode(isset($row['extra_json']) ? (string)$row['extra_json'] : '', true);
        return !is_array($extra) || !isset($extra['items']) || !is_array($extra['items']) || count($extra['items']) === 0;
    }


    /** Seed uploaded about.html as a structured single page. */
    protected function ensureAboutPageDefaults($connection, $prefix)
    {
        $pageTable = $prefix . 'cms_page';
        $blockTable = $prefix . 'cms_page_content_block';
        if (!$this->tableExists($connection, $pageTable) || !$this->tableExists($connection, $blockTable)) return;
        if (!class_exists(__NAMESPACE__ . '\\AboutPageDefaults', false)) require_once __DIR__ . '/AboutPageDefaults.php';
        $pdo = $this->getPdo($connection);
        $page = AboutPageDefaults::page();
        $pageId = $this->ensureAboutPageRow($pdo, $pageTable, $page);
        if (!$pageId) return;
        foreach (AboutPageDefaults::blocks() as $block) $this->ensureAboutPageBlockRow($pdo, $blockTable, $pageId, $block);
        $this->purgeRetiredPageContentBlocks($pdo, $blockTable, $pageId, ['body','about_intro','about_production','about_scale','about_honor','about_history','about_research_intro','about_research_diverse','about_research_academia','about_team','about_culture','about_showroom']);
    }

    protected function ensureAboutPageRow($pdo, $table, array $page)
    {
        $select = $pdo->prepare("SELECT `id`,`edited_by_admin`,`template`,`page_type` FROM `{$table}` WHERE `slug`='about' AND `deletetime` IS NULL LIMIT 1");
        $select->execute();
        $row = $select->fetch(\PDO::FETCH_ASSOC);
        $sourceKey = 'jinya:page:about';
        $content = '走近金亚页面主体由结构化功能块动态渲染。';
        if (!$row) {
            $insert = $pdo->prepare(
                "INSERT INTO `{$table}` (`title`,`slug`,`template`,`page_type`,`summary`,`content`,`mobile_content`,`weigh`,`status`,`publish_time`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) " .
                "VALUES (?,?,?,?,?,?,?,?,'published',UNIX_TIMESTAMP(),?,?,?,?,?,0,UNIX_TIMESTAMP(),UNIX_TIMESTAMP())"
            );
            $insert->execute([(string)$page['title'],'about','about','about',(string)$page['summary'],$content,$content,(int)$page['weigh'],(string)$page['seo_title'],(string)$page['seo_keywords'],(string)$page['seo_description'],'/page/about',$sourceKey]);
            return (int)$pdo->lastInsertId();
        }
        $pageId = (int)$row['id'];
        if (empty($row['edited_by_admin'])) {
            $update = $pdo->prepare(
                "UPDATE `{$table}` SET `title`=?,`template`='about',`page_type`='about',`summary`=?,`content`=?,`mobile_content`=?,`weigh`=?,`status`='published'," .
                "`publish_time`=COALESCE(`publish_time`,UNIX_TIMESTAMP()),`seo_title`=?,`seo_keywords`=?,`seo_description`=?,`canonical_url`='/page/about',`source_key`=?,`updatetime`=UNIX_TIMESTAMP() WHERE `id`=? AND `edited_by_admin`=0"
            );
            $update->execute([(string)$page['title'],(string)$page['summary'],$content,$content,(int)$page['weigh'],(string)$page['seo_title'],(string)$page['seo_keywords'],(string)$page['seo_description'],$sourceKey,$pageId]);
        } else {
            $update = $pdo->prepare("UPDATE `{$table}` SET `template`='about',`page_type`='about',`canonical_url`='/page/about',`updatetime`=UNIX_TIMESTAMP() WHERE `id`=?");
            $update->execute([$pageId]);
        }
        return $pageId;
    }

    protected function ensureAboutPageBlockRow($pdo, $table, $pageId, array $block)
    {
        $sourceKey = 'jinya:about:' . preg_replace('/^about_/', '', (string)$block['block_key']);
        $extraJson = json_encode(isset($block['extra']) && is_array($block['extra']) ? $block['extra'] : [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($extraJson === false) throw new \RuntimeException('走近金亚页面默认配置 JSON 编码失败：' . $block['block_key']);
        $select = $pdo->prepare(
            "SELECT `id`,`edited_by_admin`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`extra_json` FROM `{$table}` " .
            "WHERE `page_id`=? AND `deletetime` IS NULL AND (`source_key`=? OR `block_key`=?) ORDER BY (`source_key`=?) DESC,`id` ASC LIMIT 1"
        );
        $select->execute([(int)$pageId,$sourceKey,(string)$block['block_key'],$sourceKey]);
        $row = $select->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            $insert = $pdo->prepare(
                "INSERT INTO `{$table}` (`page_id`,`block_key`,`block_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`link_text`,`link_url`,`extra_json`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) " .
                "VALUES (?,?,?,?,?,?,?,?,?,?,?,?,0,?,?,?,?,UNIX_TIMESTAMP(),UNIX_TIMESTAMP())"
            );
            $insert->execute([(int)$pageId,(string)$block['block_key'],'about_section',(string)$block['title'],(string)$block['subtitle'],(string)$block['content'],(string)$block['image'],(string)$block['mobile_image'],(string)$block['link_text'],(string)$block['link_url'],$extraJson,$sourceKey,(int)$block['pc_visible'],(int)$block['mobile_visible'],(int)$block['weigh'],(string)$block['status']]);
            return;
        }
        if (empty($row['edited_by_admin']) || $this->isStructurallyEmptyAboutBlock($row)) {
            $update = $pdo->prepare(
                "UPDATE `{$table}` SET `block_key`=?,`block_type`='about_section',`title`=?,`subtitle`=?,`content`=?,`image`=?,`mobile_image`=?,`link_text`=?,`link_url`=?," .
                "`extra_json`=?,`source_key`=?,`pc_visible`=?,`mobile_visible`=?,`weigh`=?,`status`=?,`updatetime`=UNIX_TIMESTAMP() WHERE `id`=?"
            );
            $update->execute([(string)$block['block_key'],(string)$block['title'],(string)$block['subtitle'],(string)$block['content'],(string)$block['image'],(string)$block['mobile_image'],(string)$block['link_text'],(string)$block['link_url'],$extraJson,$sourceKey,(int)$block['pc_visible'],(int)$block['mobile_visible'],(int)$block['weigh'],(string)$block['status'],(int)$row['id']]);
        }
    }

    protected function isStructurallyEmptyAboutBlock(array $row)
    {
        foreach (['title','subtitle','content','image','mobile_image'] as $field) if (isset($row[$field]) && trim((string)$row[$field]) !== '') return false;
        $extra = json_decode(isset($row['extra_json']) ? (string)$row['extra_json'] : '', true);
        return !is_array($extra) || !isset($extra['items']) || !is_array($extra['items']) || count($extra['items']) === 0;
    }



    /** Seed uploaded contact.html as a structured single page. */
    protected function ensureContactPageDefaults($connection, $prefix)
    {
        $pageTable = $prefix . 'cms_page';
        $blockTable = $prefix . 'cms_page_content_block';
        if (!$this->tableExists($connection, $pageTable) || !$this->tableExists($connection, $blockTable)) return;
        if (!class_exists(__NAMESPACE__ . '\\ContactPageDefaults', false)) require_once __DIR__ . '/ContactPageDefaults.php';
        $pdo = $this->getPdo($connection);
        $page = ContactPageDefaults::page();
        $pageId = $this->ensureContactPageRow($pdo, $pageTable, $page);
        if ($pageId <= 0) return;
        foreach (ContactPageDefaults::blocks() as $block) $this->ensureContactPageBlockRow($pdo, $blockTable, $pageId, $block);
        $this->purgeRetiredPageContentBlocks($pdo, $blockTable, $pageId, ['body','contact_intro','contact_map']);
    }

    protected function ensureContactPageRow($pdo, $table, array $page)
    {
        $select = $pdo->prepare("SELECT `id`,`edited_by_admin`,`template`,`page_type` FROM `{$table}` WHERE `slug`='contact' AND `deletetime` IS NULL LIMIT 1");
        $select->execute();
        $row = $select->fetch(\PDO::FETCH_ASSOC);
        $sourceKey = 'jinya:page:contact';
        $content = '联系我们页面主体由结构化功能块动态渲染。';
        if (!$row) {
            $insert = $pdo->prepare(
                "INSERT INTO `{$table}` (`title`,`slug`,`template`,`page_type`,`summary`,`content`,`mobile_content`,`weigh`,`status`,`publish_time`,`seo_title`,`seo_keywords`,`seo_description`,`canonical_url`,`source_key`,`edited_by_admin`,`createtime`,`updatetime`) " .
                "VALUES (?,?,?,?,?,?,?,?,'published',UNIX_TIMESTAMP(),?,?,?,?,?,0,UNIX_TIMESTAMP(),UNIX_TIMESTAMP())"
            );
            $insert->execute([(string)$page['title'],'contact','contact','contact',(string)$page['summary'],$content,$content,(int)$page['weigh'],(string)$page['seo_title'],(string)$page['seo_keywords'],(string)$page['seo_description'],'/page/contact',$sourceKey]);
            return (int)$pdo->lastInsertId();
        }
        $pageId = (int)$row['id'];
        if (empty($row['edited_by_admin'])) {
            $update = $pdo->prepare(
                "UPDATE `{$table}` SET `title`=?,`template`='contact',`page_type`='contact',`summary`=?,`content`=?,`mobile_content`=?,`weigh`=?,`status`='published'," .
                "`publish_time`=COALESCE(`publish_time`,UNIX_TIMESTAMP()),`seo_title`=?,`seo_keywords`=?,`seo_description`=?,`canonical_url`='/page/contact',`source_key`=?,`updatetime`=UNIX_TIMESTAMP() WHERE `id`=? AND `edited_by_admin`=0"
            );
            $update->execute([(string)$page['title'],(string)$page['summary'],$content,$content,(int)$page['weigh'],(string)$page['seo_title'],(string)$page['seo_keywords'],(string)$page['seo_description'],$sourceKey,$pageId]);
        } else {
            $update = $pdo->prepare("UPDATE `{$table}` SET `template`='contact',`page_type`='contact',`canonical_url`='/page/contact',`updatetime`=UNIX_TIMESTAMP() WHERE `id`=?");
            $update->execute([$pageId]);
        }
        return $pageId;
    }

    protected function ensureContactPageBlockRow($pdo, $table, $pageId, array $block)
    {
        $sourceKey = 'jinya:contact:' . preg_replace('/^contact_/', '', (string)$block['block_key']);
        $extraJson = json_encode(isset($block['extra']) && is_array($block['extra']) ? $block['extra'] : [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($extraJson === false) throw new \RuntimeException('联系我们页面默认配置 JSON 编码失败：' . $block['block_key']);
        $select = $pdo->prepare(
            "SELECT `id`,`edited_by_admin`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`extra_json` FROM `{$table}` " .
            "WHERE `page_id`=? AND `deletetime` IS NULL AND (`source_key`=? OR `block_key`=?) ORDER BY (`source_key`=?) DESC,`id` ASC LIMIT 1"
        );
        $select->execute([(int)$pageId,$sourceKey,(string)$block['block_key'],$sourceKey]);
        $row = $select->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            $insert = $pdo->prepare(
                "INSERT INTO `{$table}` (`page_id`,`block_key`,`block_type`,`title`,`subtitle`,`content`,`image`,`mobile_image`,`link_text`,`link_url`,`extra_json`,`source_key`,`edited_by_admin`,`pc_visible`,`mobile_visible`,`weigh`,`status`,`createtime`,`updatetime`) " .
                "VALUES (?,?,?,?,?,?,?,?,?,?,?,?,0,?,?,?,?,UNIX_TIMESTAMP(),UNIX_TIMESTAMP())"
            );
            $insert->execute([(int)$pageId,(string)$block['block_key'],'contact_section',(string)$block['title'],(string)$block['subtitle'],(string)$block['content'],(string)$block['image'],(string)$block['mobile_image'],(string)$block['link_text'],(string)$block['link_url'],$extraJson,$sourceKey,(int)$block['pc_visible'],(int)$block['mobile_visible'],(int)$block['weigh'],(string)$block['status']]);
            return;
        }
        if (empty($row['edited_by_admin']) || $this->isStructurallyEmptyContactBlock($row)) {
            $update = $pdo->prepare(
                "UPDATE `{$table}` SET `block_key`=?,`block_type`='contact_section',`title`=?,`subtitle`=?,`content`=?,`image`=?,`mobile_image`=?,`link_text`=?,`link_url`=?," .
                "`extra_json`=?,`source_key`=?,`pc_visible`=?,`mobile_visible`=?,`weigh`=?,`status`=?,`updatetime`=UNIX_TIMESTAMP() WHERE `id`=?"
            );
            $update->execute([(string)$block['block_key'],(string)$block['title'],(string)$block['subtitle'],(string)$block['content'],(string)$block['image'],(string)$block['mobile_image'],(string)$block['link_text'],(string)$block['link_url'],$extraJson,$sourceKey,(int)$block['pc_visible'],(int)$block['mobile_visible'],(int)$block['weigh'],(string)$block['status'],(int)$row['id']]);
        }
    }

    protected function isStructurallyEmptyContactBlock(array $row)
    {
        foreach (['title','subtitle','content','image','mobile_image'] as $field) if (isset($row[$field]) && trim((string)$row[$field]) !== '') return false;
        $extra = json_decode(isset($row['extra_json']) ? (string)$row['extra_json'] : '', true);
        return !is_array($extra) || ((!isset($extra['items']) || !is_array($extra['items']) || count($extra['items']) === 0) && empty($extra['map_image']) && empty($extra['phone']));
    }


    /**
     * Normalize only known legacy default homepage weights so enabling dynamic
     * ordering preserves the historical visual order. Administrator-customized
     * values are deliberately left untouched.
     *
     * @param mixed  $connection
     * @param string $prefix
     * @return void
     */
    protected function normalizeLegacyHomeSectionWeights($connection, $prefix)
    {
        // Once operators have drag-sorted the homepage, cms_page_block is the
        // canonical evidence that the order is customized. Historical weight
        // normalization must not rewrite HomeSection weights in that case.
        if (!$this->homePageBlockOrderIsDefault($connection, $prefix)) {
            return;
        }

        $table = $prefix . 'cms_home_section';
        if (!$this->tableExists($connection, $table) || !$this->columnExists($connection, $table, 'weigh')) {
            return;
        }

        $weights = [
            'about' => [80, 300, 800],
            'products' => [90, 700],
            'service' => [45, 600],
            'workshop' => [42, 550],
            'cases' => [70, 500],
            'advantages' => [40, 400],
            'news' => [60, 200],
            'company' => [35, 100],
        ];
        $pdo = $this->getPdo($connection);

        foreach ($weights as $sectionKey => $values) {
            $canonical = array_pop($values);
            if (!$values) {
                continue;
            }
            $placeholders = implode(',', array_fill(0, count($values), '?'));
            $statement = $pdo->prepare(
                "UPDATE `{$table}` SET `weigh`=?,`updatetime`=UNIX_TIMESTAMP() " .
                "WHERE `section_key`=? AND `edited_by_admin`=0 AND `weigh` IN ({$placeholders})"
            );
            if ($statement) {
                $statement->execute(array_merge([$canonical, $sectionKey], $values));
            }
        }
    }

    /**
     * 判断首页页面功能块当前是否仍保持默认 ID 升序。
     *
     * page_block 的默认权重可能来自不同历史版本，但只要按 weigh DESC,
     * id ASC 得到的顺序仍等于 ID ASC，就视为尚未人工拖拽。只要顺序发生
     * 变化，历史 HomeSection 权重迁移就必须停止，避免覆盖运营排序。
     *
     * @param mixed  $connection
     * @param string $prefix
     * @return bool
     */
    protected function homePageBlockOrderIsDefault($connection, $prefix)
    {
        $table = $prefix . 'cms_page_block';
        if (!$this->tableExists($connection, $table) || !$this->columnExists($connection, $table, 'weigh')) {
            return true;
        }

        $statement = $this->getPdo($connection)->query(
            "SELECT `id` FROM `{$table}` WHERE `page_key`='home' AND `deletetime` IS NULL ORDER BY `weigh` DESC,`id` ASC"
        );
        $rows = $statement ? $statement->fetchAll(\PDO::FETCH_ASSOC) : [];
        if (!$rows) {
            return true;
        }

        $current = array_map(function ($row) {
            return (int)$row['id'];
        }, $rows);
        $default = $current;
        sort($default, SORT_NUMERIC);
        return $current === $default;
    }

    /**
     * 退役历史 cms_mobile 联系电话配置。
     * 只删除精确键，禁止使用 cms_mobile% 前缀匹配，以保护移动端 Banner 配置。
     *
     * @param mixed  $connection
     * @param string $prefix
     * @return void
     */
    protected function retireCmsMobileConfig($connection, $prefix)
    {
        $table = $prefix . 'config';
        $this->getPdo($connection)->exec(
            "DELETE FROM `{$table}` WHERE `name` = 'cms_mobile'"
        );
    }

    /**
     * 退役旧版 fa_config 栏目 Banner。
     *
     * 产品/新闻频道已经统一读取 cms_banner；结构化单页统一读取 *_hero 内容块。
     * 这些历史站点级字段继续存在会形成第二数据源，因此升级时按精确键物理清理。
     *
     * @param mixed  $connection
     * @param string $prefix
     * @return void
     */
    protected function retireLegacySiteBannerConfig($connection, $prefix)
    {
        $table = $prefix . 'config';
        if (!$this->tableExists($connection, $table)) {
            return;
        }

        $keys = [
            'cms_pc_product_banner',
            'cms_pc_news_banner',
            'cms_pc_case_banner',
            'cms_pc_about_banner',
            'cms_mobile_product_banner',
            'cms_mobile_news_banner',
            'cms_mobile_case_banner',
            'cms_mobile_about_banner',
        ];
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $statement = $this->getPdo($connection)->prepare(
            "DELETE FROM `{$table}` WHERE `name` IN ({$placeholders})"
        );
        if ($statement) {
            $statement->execute($keys);
        }
    }

    /**
     * 将“专版和无版印刷怎么选”的 CSS 默认背景迁回 CMS 数据。
     * 仅补齐空的 image/mobile_image，不覆盖后台已有自定义图片。
     */
    /**
     * 退役“专版和无版印刷怎么选”曾经写入 CMS 的 CSS 背景默认值。
     *
     * 整图模式下 image/mobile_image 代表完整成品图，而不是背景层。
     * 这里只清空上一版迁移写入的精确默认路径；后台自定义上传绝不覆盖。
     */
    /**
     * 为“专版和无版印刷怎么选”整图模块补齐默认成品图。
     *
     * 空值、上一版 CSS 背景默认值或错误的 v1 整图会迁移为用户确认的完整效果图；
     * 后台已经上传的其它自定义图片保持不变。
     */
    /**
     * 统一彩盒页内容区块顺序，使后台列表与前端实际 section 顺序完全一致。
     *
     * 前端与后台列表都按 weigh DESC 排序，因此这里只规范排序字段，不覆盖
     * 管理员编辑过的标题、图片、文案等内容。历史 boxes_purchase 已不再由前端
     * 独立渲染，其采购 CTA 已合并进 boxes_details，故升级时软删除该孤立区块。
     */
    protected function normalizeBoxesContentBlockOrder($connection, $prefix)
    {
        $pageTable = $prefix . 'cms_page';
        $blockTable = $prefix . 'cms_page_content_block';
        if (!$this->tableExists($connection, $pageTable) || !$this->tableExists($connection, $blockTable)) {
            return;
        }

        $pdo = $this->getPdo($connection);
        $order = [
            'boxes_hero' => 1000,
            'boxes_products' => 900,
            'boxes_value' => 800,
            'boxes_promise' => 700,
            'boxes_details' => 600,
            'boxes_applications' => 500,
            'boxes_craft_material' => 400,
            'boxes_team' => 300,
            'boxes_types' => 200,
            'boxes_services' => 100,
        ];

        $update = $pdo->prepare(
            "UPDATE `{$blockTable}` b INNER JOIN `{$pageTable}` p ON p.`id`=b.`page_id` " .
            "SET b.`weigh`=?,b.`updatetime`=UNIX_TIMESTAMP() " .
            "WHERE p.`slug`='boxes' AND b.`block_key`=? AND b.`deletetime` IS NULL"
        );
        if ($update) {
            foreach ($order as $blockKey => $weight) {
                $update->execute([$weight, $blockKey]);
            }
        }

        $retire = $pdo->prepare(
            "UPDATE `{$blockTable}` b INNER JOIN `{$pageTable}` p ON p.`id`=b.`page_id` " .
            "SET b.`status`='hidden',b.`deletetime`=COALESCE(b.`deletetime`,UNIX_TIMESTAMP()),b.`updatetime`=UNIX_TIMESTAMP() " .
            "WHERE p.`slug`='boxes' AND b.`block_key`='boxes_purchase' AND b.`deletetime` IS NULL"
        );
        if ($retire) {
            $retire->execute();
        }
    }
    /**
     * 将彩盒“品质承诺”当前前端礼盒 Logo 显式写回 CMS extra_json。
     *
     * 只在 badge_logo 为空时补默认资源，管理员已经上传的 Logo 永不覆盖。
     * mobile_badge_logo 留空表示移动端继承 PC Logo。
     */
    protected function ensureBoxesPromiseLogoDefault($connection, $prefix)
    {
        $pageTable = $prefix . 'cms_page';
        $blockTable = $prefix . 'cms_page_content_block';
        if (!$this->tableExists($connection, $pageTable) || !$this->tableExists($connection, $blockTable)) {
            return;
        }

        $pdo = $this->getPdo($connection);
        $query = $pdo->prepare(
            "SELECT b.`id`,b.`extra_json` FROM `{$blockTable}` b " .
            "INNER JOIN `{$pageTable}` p ON p.`id`=b.`page_id` " .
            "WHERE p.`slug`='boxes' AND b.`block_key`='boxes_promise' AND b.`deletetime` IS NULL LIMIT 1"
        );
        if (!$query) {
            return;
        }
        $query->execute();
        $row = $query->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            return;
        }

        $extra = json_decode((string)$row['extra_json'], true);
        $extra = is_array($extra) ? $extra : [];
        if (isset($extra['badge_logo']) && trim((string)$extra['badge_logo']) !== '') {
            return;
        }

        $extra['badge_logo'] = '/assets/jinya/img/boxes-promise-logo.svg';
        if (!array_key_exists('mobile_badge_logo', $extra)) {
            $extra['mobile_badge_logo'] = '';
        }
        $encoded = json_encode($extra, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($encoded === false) {
            return;
        }
        $update = $pdo->prepare("UPDATE `{$blockTable}` SET `extra_json`=?,`updatetime`=UNIX_TIMESTAMP() WHERE `id`=?");
        if ($update) {
            $update->execute([$encoded, (int)$row['id']]);
        }
    }
    /**
     * 删除历史“HTML 首页展示”技术分类。
     *
     * 首页产品由 cms_home_section_reference 直接引用产品 ID，运行时并不依赖分类。
     * 因此先把仍挂在该技术分类下的产品迁移为 category_id=0，再物理删除分类，
     * 避免后台分类树出现非业务分类，同时不影响首页引用和产品记录本身。
     */
    protected function retireLegacyHomeProductCategory($connection, $prefix)
    {
        $categoryTable = $prefix . 'cms_product_category';
        $productTable = $prefix . 'cms_product';
        if (!$this->tableExists($connection, $categoryTable) || !$this->tableExists($connection, $productTable)) {
            return;
        }

        $pdo = $this->getPdo($connection);
        $select = $pdo->prepare("SELECT `id` FROM `{$categoryTable}` WHERE `slug`='html-home-display' LIMIT 1");
        if (!$select) {
            return;
        }
        $select->execute();
        $categoryId = (int)$select->fetchColumn();
        if ($categoryId <= 0) {
            return;
        }

        $pdo->beginTransaction();
        try {
            $moveProducts = $pdo->prepare("UPDATE `{$productTable}` SET `category_id`=0,`updatetime`=UNIX_TIMESTAMP() WHERE `category_id`=?");
            $moveProducts->execute([$categoryId]);

            $deleteCategory = $pdo->prepare("DELETE FROM `{$categoryTable}` WHERE `id`=? AND `slug`='html-home-display'");
            $deleteCategory->execute([$categoryId]);
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
    /**
     * 删除旧克隆站遗留的无用新闻分类，但保留新闻内容。
     *
     * “常见问答 / 科创美新闻 / 新闻动态”不属于当前金亚新闻分类体系。
     * 删除前先把所属新闻迁移为 category_id=0，并把非退役子分类提升为顶级分类，
     * 避免因为清理分类而误删文章或留下不可达的分类树。
     */
    protected function retireLegacyArticleCategories($connection, $prefix)
    {
        $categoryTable = $prefix . 'cms_article_category';
        $articleTable = $prefix . 'cms_article';
        if (!$this->tableExists($connection, $categoryTable) || !$this->tableExists($connection, $articleTable)) {
            return;
        }

        $pdo = $this->getPdo($connection);
        $names = ['常见问答', '科创美新闻', '新闻动态'];
        $placeholders = implode(',', array_fill(0, count($names), '?'));
        $select = $pdo->prepare("SELECT `id` FROM `{$categoryTable}` WHERE `name` IN ({$placeholders})");
        if (!$select) {
            return;
        }
        $select->execute($names);
        $ids = array_values(array_filter(array_map('intval', $select->fetchAll(\PDO::FETCH_COLUMN))));
        if (!$ids) {
            return;
        }

        $idPlaceholders = implode(',', array_fill(0, count($ids), '?'));
        $pdo->beginTransaction();
        try {
            $moveArticles = $pdo->prepare("UPDATE `{$articleTable}` SET `category_id`=0,`updatetime`=UNIX_TIMESTAMP() WHERE `category_id` IN ({$idPlaceholders})");
            $moveArticles->execute($ids);

            $promoteChildren = $pdo->prepare("UPDATE `{$categoryTable}` SET `parent_id`=0,`updatetime`=UNIX_TIMESTAMP() WHERE `parent_id` IN ({$idPlaceholders}) AND `id` NOT IN ({$idPlaceholders})");
            $promoteChildren->execute(array_merge($ids, $ids));

            $deleteCategories = $pdo->prepare("DELETE FROM `{$categoryTable}` WHERE `id` IN ({$idPlaceholders})");
            $deleteCategories->execute($ids);
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
    /**
     * 物理删除所有 status=hidden 的新闻及其页面引用。
     *
     * hidden 不属于当前 PublishStateMachine 的合法新闻状态，只用于历史 HTML
     * 基线淘汰旧克隆新闻。删除新闻前先清理 page_block_reference 与
     * home_section_reference，避免保留悬空引用。
     */
    protected function deleteHiddenArticles($connection, $prefix)
    {
        $articleTable = $prefix . 'cms_article';
        if (!$this->tableExists($connection, $articleTable)) {
            return;
        }

        $pdo = $this->getPdo($connection);
        $pageReferenceTable = $prefix . 'cms_page_block_reference';
        $homeReferenceTable = $prefix . 'cms_home_section_reference';

        $pdo->beginTransaction();
        try {
            if ($this->tableExists($connection, $pageReferenceTable)) {
                $pdo->exec(
                    "DELETE r FROM `{$pageReferenceTable}` r " .
                    "INNER JOIN `{$articleTable}` a ON a.`id`=r.`content_id` " .
                    "WHERE r.`content_type`='article' AND a.`status`='hidden'"
                );
            }

            if ($this->tableExists($connection, $homeReferenceTable)) {
                $pdo->exec(
                    "DELETE r FROM `{$homeReferenceTable}` r " .
                    "INNER JOIN `{$articleTable}` a ON a.`id`=r.`content_id` " .
                    "WHERE r.`content_type`='article' AND a.`status`='hidden'"
                );
            }

            $pdo->exec("DELETE FROM `{$articleTable}` WHERE `status`='hidden'");
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
    /**
     * 物理删除当前前台未使用的产品及其附属数据。
     *
     * “正在使用”以当前有效的首页引用 / 页面功能块引用为准。当前正式前台
     * 首页产品中心通过 cms_home_section_reference 引用产品；页面级手工推荐
     * 则通过 cms_page_block_reference 引用。其它旧克隆、演示或孤立产品不会
     * 再参与正式前台，安装时直接清除。
     *
     * 历史客户咨询不删除；如果它引用了被淘汰产品，只把 product_id 归零。
     */
    protected function deleteUnusedProducts($connection, $prefix)
    {
        $productTable = $prefix . 'cms_product';
        if (!$this->tableExists($connection, $productTable)) {
            return;
        }

        $pdo = $this->getPdo($connection);
        $homeReferenceTable = $prefix . 'cms_home_section_reference';
        $pageReferenceTable = $prefix . 'cms_page_block_reference';
        $keepIds = [];

        if ($this->tableExists($connection, $homeReferenceTable)) {
            $statement = $pdo->query(
                "SELECT DISTINCT `content_id` FROM `{$homeReferenceTable}` " .
                "WHERE `content_type`='product' AND `status`='normal' AND `deletetime` IS NULL AND `content_id`>0"
            );
            if ($statement) {
                $keepIds = array_merge($keepIds, $statement->fetchAll(\PDO::FETCH_COLUMN));
            }
        }

        if ($this->tableExists($connection, $pageReferenceTable)) {
            $statement = $pdo->query(
                "SELECT DISTINCT `content_id` FROM `{$pageReferenceTable}` " .
                "WHERE `content_type`='product' AND `status`='normal' AND `deletetime` IS NULL AND `content_id`>0"
            );
            if ($statement) {
                $keepIds = array_merge($keepIds, $statement->fetchAll(\PDO::FETCH_COLUMN));
            }
        }

        $keepIds = array_values(array_unique(array_filter(array_map('intval', $keepIds))));
        // Fail safe: the HTML baseline always creates homepage product references.
        // If none exist, do not risk deleting the whole product table.
        if (!$keepIds) {
            return;
        }

        $keepPlaceholders = implode(',', array_fill(0, count($keepIds), '?'));
        $select = $pdo->prepare("SELECT `id` FROM `{$productTable}` WHERE `id` NOT IN ({$keepPlaceholders})");
        $select->execute($keepIds);
        $deleteIds = array_values(array_filter(array_map('intval', $select->fetchAll(\PDO::FETCH_COLUMN))));
        if (!$deleteIds) {
            return;
        }

        $deletePlaceholders = implode(',', array_fill(0, count($deleteIds), '?'));
        $pdo->beginTransaction();
        try {
            // Preserve historical inquiries, but remove references to products being retired.
            $inquiryTable = $prefix . 'cms_inquiry';
            if ($this->tableExists($connection, $inquiryTable)) {
                $statement = $pdo->prepare("UPDATE `{$inquiryTable}` SET `product_id`=0,`updatetime`=UNIX_TIMESTAMP() WHERE `product_id` IN ({$deletePlaceholders})");
                $statement->execute($deleteIds);
            }

            foreach (['cms_product_image', 'cms_product_parameter', 'cms_product_section'] as $childName) {
                $childTable = $prefix . $childName;
                if (!$this->tableExists($connection, $childTable)) {
                    continue;
                }
                $statement = $pdo->prepare("DELETE FROM `{$childTable}` WHERE `product_id` IN ({$deletePlaceholders})");
                $statement->execute($deleteIds);
            }

            if ($this->tableExists($connection, $pageReferenceTable)) {
                $statement = $pdo->prepare("DELETE FROM `{$pageReferenceTable}` WHERE `content_type`='product' AND `content_id` IN ({$deletePlaceholders})");
                $statement->execute($deleteIds);
            }
            if ($this->tableExists($connection, $homeReferenceTable)) {
                $statement = $pdo->prepare("DELETE FROM `{$homeReferenceTable}` WHERE `content_type`='product' AND `content_id` IN ({$deletePlaceholders})");
                $statement->execute($deleteIds);
            }

            $statement = $pdo->prepare("DELETE FROM `{$productTable}` WHERE `id` IN ({$deletePlaceholders})");
            $statement->execute($deleteIds);
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
    protected function ensureBagsCompareFullImageDefaults($connection, $prefix)
    {
        $pageTable = $prefix . 'cms_page';
        $blockTable = $prefix . 'cms_page_content_block';
        if (!$this->tableExists($connection, $pageTable) || !$this->tableExists($connection, $blockTable)) {
            return;
        }

        $legacyBackground = '/assets/jinya/img/bags-tech-compare-bg-v2.jpg';
        $legacyBrokenFull = '/assets/jinya/img/bags-compare-full.webp';
        $default = '/assets/jinya/img/bags-compare-full-v2.webp';
        $sql = "UPDATE `{$blockTable}` b INNER JOIN `{$pageTable}` p ON p.`id`=b.`page_id` " .
            "SET b.`image`=CASE WHEN TRIM(COALESCE(b.`image`,''))='' OR b.`image` IN (?,?) THEN ? ELSE b.`image` END, " .
            "b.`mobile_image`=CASE WHEN TRIM(COALESCE(b.`mobile_image`,''))='' OR b.`mobile_image` IN (?,?) THEN ? ELSE b.`mobile_image` END " .
            "WHERE p.`slug`='bags' AND b.`block_key`='bags_compare'";
        $statement = $this->getPdo($connection)->prepare($sql);
        if ($statement) {
            $statement->execute([
                $legacyBackground, $legacyBrokenFull, $default,
                $legacyBackground, $legacyBrokenFull, $default,
            ]);
        }
    }

    /**
     * 按已确认的不干胶效果图，为“能力图文”富文本补齐强调色。
     *
     * 只处理命中的标题和行前缀；已有 [color=...] 的行保持原样，
     * 因此重复执行 cms:install 不会叠加标签，也不会覆盖其它后台内容。
     */
    protected function applyLabelCapabilityReferenceTextColors($connection, $prefix)
    {
        $pageTable = $prefix . 'cms_page';
        $blockTable = $prefix . 'cms_page_content_block';
        if (!$this->tableExists($connection, $pageTable) || !$this->tableExists($connection, $blockTable)) {
            return;
        }

        $pdo = $this->getPdo($connection);
        $statement = $pdo->prepare(
            "SELECT b.`id`,b.`extra_json` FROM `{$blockTable}` b " .
            "INNER JOIN `{$pageTable}` p ON p.`id`=b.`page_id` " .
            "WHERE p.`slug`='label' AND b.`block_key`='label_capability' AND b.`deletetime` IS NULL LIMIT 1"
        );
        $statement->execute();
        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            return;
        }

        $extra = json_decode((string)$row['extra_json'], true);
        if (!is_array($extra) || !isset($extra['items']) || !is_array($extra['items'])) {
            return;
        }

        $rules = [
            '铜版纸不干胶' => ['特点：', '优点：'],
            '珠光膜/PVC/合成纸不干胶' => ['特点：', '1、', '2、', '3、', '1.', '2.', '3.'],
            '亮银/哑银/合成银不干胶' => ['特点：', '优点：', '用途：'],
            '镭射不干胶' => ['特点：', '1、', '2、', '3、', '1.', '2.', '3.'],
        ];

        $changed = false;
        foreach ($extra['items'] as &$item) {
            if (!is_array($item)) {
                continue;
            }
            $title = isset($item['title']) ? trim((string)$item['title']) : '';
            if ($title === '' || !isset($rules[$title])) {
                continue;
            }
            foreach (['text', 'mobile_text'] as $field) {
                if (!isset($item[$field]) || trim((string)$item[$field]) === '') {
                    continue;
                }
                $decorated = $this->decorateReferenceTextColorLines(
                    (string)$item[$field],
                    $rules[$title],
                    self::LABEL_REFERENCE_ACCENT_COLOR
                );
                if ($decorated !== (string)$item[$field]) {
                    $item[$field] = $decorated;
                    $changed = true;
                }
            }
        }
        unset($item);

        if (!$changed) {
            return;
        }

        $encoded = json_encode($extra, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($encoded === false) {
            return;
        }
        $update = $pdo->prepare("UPDATE `{$blockTable}` SET `extra_json`=?,`updatetime`=? WHERE `id`=?");
        $update->execute([$encoded, time(), (int)$row['id']]);
    }

    protected function decorateReferenceTextColorLines($text, array $prefixes, $color)
    {
        $text = str_replace(["\r\n", "\r"], "\n", (string)$text);
        $lines = explode("\n", $text);
        foreach ($lines as &$line) {
            if (trim($line) === '' || strpos($line, '[color=') !== false) {
                continue;
            }
            $probe = trim($line);
            $probe = preg_replace('/^(?:\\*\\*|__)\\s*/u', '', $probe);
            foreach ($prefixes as $prefix) {
                if (strpos($probe, $prefix) === 0) {
                    $line = '[color=' . $color . ']' . $line . '[/color]';
                    break;
                }
            }
        }
        unset($line);
        return implode("\n", $lines);
    }
    /**
     * 更新 FastAdmin 静态资源缓存版本。
     *
     * RequireJS 会把 site.version 拼到后台 JS URL；CMS 后台脚本变更后如果
     * 版本保持不变，浏览器会继续命中旧缓存，导致新编辑器代码看似“未生效”。
     */
    protected function syncAssetVersion($connection, $prefix)
    {
        $table = $prefix . 'config';
        if (!$this->tableExists($connection, $table)) {
            return;
        }
        $statement = $this->getPdo($connection)->prepare(
            "UPDATE `{$table}` SET `value`=? WHERE `name`='version'"
        );
        if ($statement) {
            $statement->execute([self::CMS_ASSET_VERSION]);
        }
    }

    /**
     * 将 fa_config 重新生成到 application/extra/site.php。
     * 独立于后台权限，可在 CLI 安装过程中安全调用。
     *
     * @param mixed       $connection
     * @param string|null $prefix
     * @return bool
     */
    public function refreshSiteConfig($connection = null, $prefix = null)
    {
        $connection = $connection ?: Db::connect();
        $prefix = $this->normalizePrefix($prefix ?: Config::get('database.prefix'));
        $table = $prefix . 'config';

        if (!$this->tableExists($connection, $table)) {
            throw new \RuntimeException('系统配置表不存在：' . $table);
        }

        $statement = $this->getPdo($connection)->query("SELECT `name`,`type`,`value` FROM `{$table}` ORDER BY `id` ASC");
        $rows = $statement ? $statement->fetchAll(\PDO::FETCH_ASSOC) : [];
        $config = [];
        foreach ($rows as $row) {
            $value = $row['value'];
            if (in_array($row['type'], ['selects', 'checkbox', 'images', 'files'], true)) {
                $value = $value === '' ? [] : explode(',', $value);
            } elseif ($row['type'] === 'array') {
                $decoded = json_decode($value, true);
                $value = is_array($decoded) ? $decoded : [];
            }
            $config[$row['name']] = $value;
        }

        $file = CONF_PATH . 'extra' . DS . 'site.php';
        $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
        if (file_put_contents($file, $content) === false) {
            throw new \RuntimeException('无法写入站点配置文件：application/extra/site.php');
        }
        return true;
    }

    /**
     * 为已安装的旧版本补齐新增字段。字段定义为固定白名单，避免动态 SQL 注入。
     *
     * @param mixed  $connection
     * @param string $prefix
     * @return void
     */
    protected function ensureSchema($connection, $prefix)
    {
        $bannerTable = $prefix . 'cms_banner';
        if ($this->tableExists($connection, $bannerTable)) {
            $columns = [
                'mobile_title' => "varchar(150) NOT NULL DEFAULT '' AFTER `title`",
                'media_type' => "varchar(20) NOT NULL DEFAULT 'image' COMMENT 'image/video' AFTER `mobile_image`",
                'video_url' => "varchar(500) NOT NULL DEFAULT '' AFTER `media_type`",
                'overlay_image' => "varchar(255) NOT NULL DEFAULT '' AFTER `video_url`",
                'mobile_link_url' => "varchar(255) NOT NULL DEFAULT '' AFTER `link_url`",
            ];
            foreach ($columns as $column => $definition) {
                if (!$this->columnExists($connection, $bannerTable, $column)) {
                    $this->getPdo($connection)->exec("ALTER TABLE `{$bannerTable}` ADD COLUMN `{$column}` {$definition}");
                }
            }
        }

        $homeSectionTable = $prefix . 'cms_home_section';
        if ($this->tableExists($connection, $homeSectionTable)
            && !$this->columnExists($connection, $homeSectionTable, 'mobile_title')) {
            $this->getPdo($connection)->exec(
                "ALTER TABLE `{$homeSectionTable}` ADD COLUMN `mobile_title` varchar(150) NOT NULL DEFAULT '' AFTER `title`"
            );
        }

        foreach (['cms_layout_component', 'cms_page_config', 'cms_page_block'] as $tableName) {
            $table = $prefix . $tableName;
            if ($this->tableExists($connection, $table)) {
                $columns = [
                    'version' => "int(10) unsigned NOT NULL DEFAULT '1' AFTER `config_json`",
                ];
                foreach ($columns as $column => $definition) {
                    if (!$this->columnExists($connection, $table, $column)) {
                        $this->getPdo($connection)->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
                    }
                }
            }
        }
    }

    /**
     * 为旧数据库补齐动态渲染字段。所有表名和字段定义均为代码白名单。
     *
     * @param mixed  $connection
     * @param string $prefix
     * @return void
     */
    protected function ensureDynamicRenderSchema($connection, $prefix)
    {
        $definitions = [
            'cms_navigation' => [
                'slug' => "varchar(150) NOT NULL DEFAULT '' AFTER `title`",
                'link_type' => "varchar(30) NOT NULL DEFAULT 'url' AFTER `url`",
                'link_value' => "varchar(255) NOT NULL DEFAULT '' AFTER `link_type`",
                'pc_visible' => "tinyint(1) unsigned NOT NULL DEFAULT '1' AFTER `position`",
                'mobile_visible' => "tinyint(1) unsigned NOT NULL DEFAULT '1' AFTER `pc_visible`",
            ],
            'cms_banner' => [
                'page_key' => "varchar(100) NOT NULL DEFAULT 'home' AFTER `id`",
                'position' => "varchar(100) NOT NULL DEFAULT 'hero' AFTER `page_key`",
                'mobile_subtitle' => "varchar(255) NOT NULL DEFAULT '' AFTER `subtitle`",
                'description' => "varchar(1000) NOT NULL DEFAULT '' AFTER `mobile_subtitle`",
                'mobile_description' => "varchar(1000) NOT NULL DEFAULT '' AFTER `description`",
                'mobile_media_type' => "varchar(20) NOT NULL DEFAULT '' AFTER `media_type`",
                'mobile_video_url' => "varchar(500) NOT NULL DEFAULT '' AFTER `video_url`",
                'pc_visible' => "tinyint(1) unsigned NOT NULL DEFAULT '1' AFTER `end_time`",
                'mobile_visible' => "tinyint(1) unsigned NOT NULL DEFAULT '1' AFTER `pc_visible`",
                'source_key' => "varchar(190) NOT NULL DEFAULT '' AFTER `mobile_visible`",
                'edited_by_admin' => "tinyint(1) unsigned NOT NULL DEFAULT '0' AFTER `source_key`",
            ],
            'cms_home_section' => [
                'mobile_subtitle' => "varchar(255) NOT NULL DEFAULT '' AFTER `subtitle`",
                'description' => "varchar(1000) NOT NULL DEFAULT '' AFTER `mobile_subtitle`",
                'mobile_description' => "varchar(1000) NOT NULL DEFAULT '' AFTER `description`",
                'background_image' => "varchar(255) NOT NULL DEFAULT '' AFTER `image`",
                'mobile_background_image' => "varchar(255) NOT NULL DEFAULT '' AFTER `background_image`",
                'more_text' => "varchar(100) NOT NULL DEFAULT '' AFTER `mobile_background_image`",
                'more_url' => "varchar(255) NOT NULL DEFAULT '' AFTER `more_text`",
                'pc_visible' => "tinyint(1) unsigned NOT NULL DEFAULT '1' AFTER `more_url`",
                'mobile_visible' => "tinyint(1) unsigned NOT NULL DEFAULT '1' AFTER `pc_visible`",
                'pc_display_count' => "int unsigned NOT NULL DEFAULT '0' AFTER `mobile_visible`",
                'mobile_display_count' => "int unsigned NOT NULL DEFAULT '0' AFTER `pc_display_count`",
                'source_key' => "varchar(190) NOT NULL DEFAULT '' AFTER `config_json`",
                'edited_by_admin' => "tinyint(1) unsigned NOT NULL DEFAULT '0' AFTER `source_key`",
            ],
            'cms_layout_component' => [
                'title' => "varchar(150) NOT NULL DEFAULT '' AFTER `device`",
                'content' => "text AFTER `title`",
                'image' => "varchar(255) NOT NULL DEFAULT '' AFTER `content`",
                'link_text' => "varchar(100) NOT NULL DEFAULT '' AFTER `image`",
                'link_url' => "varchar(255) NOT NULL DEFAULT '' AFTER `link_text`",
                'pc_visible' => "tinyint(1) unsigned NOT NULL DEFAULT '1' AFTER `link_url`",
                'mobile_visible' => "tinyint(1) unsigned NOT NULL DEFAULT '1' AFTER `pc_visible`",
            ],
            'cms_product_image' => [
                'mobile_image' => "varchar(255) NOT NULL DEFAULT '' AFTER `image`",
            ],
            'cms_product_section' => [
                'subtitle' => "varchar(255) NOT NULL DEFAULT '' AFTER `title`",
            ],
            'cms_page' => [
                'page_type' => "varchar(30) NOT NULL DEFAULT 'general' AFTER `template`",
                'mobile_cover_image' => "varchar(255) NOT NULL DEFAULT '' AFTER `cover_image`",
                'mobile_content' => "mediumtext AFTER `content`",
                'source_key' => "varchar(190) NOT NULL DEFAULT '' AFTER `robots`",
                'edited_by_admin' => "tinyint(1) unsigned NOT NULL DEFAULT '0' AFTER `source_key`",
            ],
        ];
        foreach (['cms_product', 'cms_article', 'cms_case'] as $name) {
            $definitions[$name] = [
                'mobile_cover_image' => "varchar(255) NOT NULL DEFAULT '' AFTER `cover_image`",
                'source_key' => "varchar(190) NOT NULL DEFAULT '' AFTER `robots`",
                'edited_by_admin' => "tinyint(1) unsigned NOT NULL DEFAULT '0' AFTER `source_key`",
            ];
        }
        foreach ($definitions as $tableName => $columns) {
            $table = $prefix . $tableName;
            if (!$this->tableExists($connection, $table)) {
                continue;
            }
            foreach ($columns as $column => $definition) {
                if (!$this->columnExists($connection, $table, $column)) {
                    $this->getPdo($connection)->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
                }
            }
        }
    }

    /**
     * Retire legacy frozen-body records. These rows stored clone DOM/CSS in the
     * database and must never participate in normal runtime rendering.
     */
    protected function retireStrictCloneBodies($connection, $prefix)
    {
        $pdo = $this->getPdo($connection);
        $nowSql = 'UNIX_TIMESTAMP()';


        $productTable = $prefix . 'cms_product_section';
        if ($this->tableExists($connection, $productTable)) {
            $pdo->exec(
                "UPDATE `{$productTable}` SET `status`='hidden',`deletetime`=COALESCE(`deletetime`,{$nowSql}),`updatetime`={$nowSql} "
                . "WHERE `deletetime` IS NULL AND (`section_type`='strict_clone_body' OR `source_key` LIKE 'clone:product:%:body')"
            );
        }

        $pageTable = $prefix . 'cms_page_content_block';
        if ($this->tableExists($connection, $pageTable)) {
            $pdo->exec(
                "UPDATE `{$pageTable}` SET `status`='hidden',`deletetime`=COALESCE(`deletetime`,{$nowSql}),`updatetime`={$nowSql} "
                . "WHERE `deletetime` IS NULL AND (`block_type`='strict_clone_body' OR `block_key`='strict_clone_body' OR `source_key` LIKE 'clone:page:%:body')"
            );
        }
    }

    /**
     * Convert all operational body fields from legacy HTML to Markdown.
     * Plain-text/Markdown values are idempotent and remain unchanged.
     */
    protected function migrateMarkdownBodies($connection, $prefix)
    {
        if (!class_exists(__NAMESPACE__ . '\\HtmlToMarkdownConverter', false)) {
            require_once __DIR__ . '/HtmlToMarkdownConverter.php';
        }
        $pdo = $this->getPdo($connection);
        $definitions = [
            'cms_product' => ['features', 'applications', 'construction', 'precautions', 'content'],
            'cms_article' => ['content'],
            'cms_case' => ['content'],
            'cms_product_image' => [
                'mobile_image' => "varchar(255) NOT NULL DEFAULT '' AFTER `image`",
            ],
            'cms_product_section' => [
                'subtitle' => "varchar(255) NOT NULL DEFAULT '' AFTER `title`",
            ],
            'cms_page' => ['content', 'mobile_content'],
            'cms_home_section' => ['content', 'mobile_content'],
            'cms_page_content_block' => ['content'],
        ];
        foreach ($definitions as $tableName => $columns) {
            $table = $prefix . $tableName;
            if (!$this->tableExists($connection, $table)) {
                continue;
            }
            foreach ($columns as $column) {
                if ($this->columnExists($connection, $table, $column)) {
                    $this->migrateMarkdownColumn($connection, $pdo, $table, $column);
                }
            }
        }

        $pageBlockTable = $prefix . 'cms_page_content_block';
        if ($this->tableExists($connection, $pageBlockTable)
            && $this->columnExists($connection, $pageBlockTable, 'extra_json')) {
            $this->migratePageBlockMobileMarkdown($connection, $pdo, $pageBlockTable);
        }
    }

    private function migrateMarkdownColumn($connection, $pdo, $table, $column)
    {
        $where = "`{$column}` IS NOT NULL AND `{$column}`<>''";
        if ($this->columnExists($connection, $table, 'deletetime')) {
            $where .= ' AND `deletetime` IS NULL';
        }
        $statement = $pdo->query("SELECT `id`,`{$column}` FROM `{$table}` WHERE {$where}");
        $rows = $statement ? $statement->fetchAll(\PDO::FETCH_ASSOC) : [];
        $hasUpdatedAt = $this->columnExists($connection, $table, 'updatetime');
        $sql = "UPDATE `{$table}` SET `{$column}`=?" . ($hasUpdatedAt ? ',`updatetime`=UNIX_TIMESTAMP()' : '') . ' WHERE `id`=?';
        $update = $pdo->prepare($sql);
        foreach ($rows as $row) {
            $current = (string)$row[$column];
            $markdown = HtmlToMarkdownConverter::convert($current);
            if ($markdown !== $current) {
                $update->execute([$markdown, (int)$row['id']]);
            }
        }
    }

    private function migratePageBlockMobileMarkdown($connection, $pdo, $table)
    {
        $where = "`extra_json` IS NOT NULL AND `extra_json`<>''";
        if ($this->columnExists($connection, $table, 'deletetime')) {
            $where .= ' AND `deletetime` IS NULL';
        }
        $statement = $pdo->query("SELECT `id`,`extra_json` FROM `{$table}` WHERE {$where}");
        $rows = $statement ? $statement->fetchAll(\PDO::FETCH_ASSOC) : [];
        $hasUpdatedAt = $this->columnExists($connection, $table, 'updatetime');
        $sql = "UPDATE `{$table}` SET `extra_json`=?" . ($hasUpdatedAt ? ',`updatetime`=UNIX_TIMESTAMP()' : '') . ' WHERE `id`=?';
        $update = $pdo->prepare($sql);
        foreach ($rows as $row) {
            $extra = json_decode((string)$row['extra_json'], true);
            if (!is_array($extra) || !array_key_exists('mobile_content', $extra)) {
                continue;
            }
            $current = (string)$extra['mobile_content'];
            $markdown = HtmlToMarkdownConverter::convert($current);
            if ($markdown === $current) {
                continue;
            }
            $extra['mobile_content'] = $markdown;
            $update->execute([
                json_encode($extra, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                (int)$row['id'],
            ]);
        }
    }

    /**
     * 根据代码注册表补齐固定页面和功能块。只 INSERT IGNORE，不覆盖运营配置。
     *
     * @param mixed  $connection
     * @param string $prefix
     * @return void
     */
    protected function syncPageSchema($connection, $prefix)
    {
        $pageTable = $prefix . 'cms_page_config';
        $blockTable = $prefix . 'cms_page_block';
        if (!$this->tableExists($connection, $pageTable) || !$this->tableExists($connection, $blockTable)) {
            return;
        }

        $pdo = $this->getPdo($connection);
        $pageStatement = $pdo->prepare(
            "INSERT IGNORE INTO `{$pageTable}` (`page_key`,`page_name`,`page_type`,`route_pattern`,`pc_header_key`,`pc_footer_key`,`mobile_header_key`,`mobile_footer_key`,`config_json`,`version`,`status`,`createtime`,`updatetime`) VALUES (?,?,?,?,?,?,?,?,?,1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP())"
        );
        $syncPageStatement = $pdo->prepare(
            "UPDATE `{$pageTable}` SET `page_name`=?,`page_type`=?,`route_pattern`=?,`updatetime`=UNIX_TIMESTAMP() WHERE `page_key`=?"
        );
        $blockStatement = $pdo->prepare(
            "INSERT IGNORE INTO `{$blockTable}` (`page_key`,`block_key`,`block_name`,`block_type`,`source_type`,`config_json`,`pc_visible`,`mobile_visible`,`weigh`,`version`,`status`,`createtime`,`updatetime`) VALUES (?,?,?,?,?,'{}',?,?,?,1,'normal',UNIX_TIMESTAMP(),UNIX_TIMESTAMP())"
        );
        $syncBlockStatement = $pdo->prepare(
            "UPDATE `{$blockTable}` SET `block_name`=?,`block_type`=?,`source_type`=?,`weigh`=?,`updatetime`=UNIX_TIMESTAMP() WHERE `page_key`=? AND `block_key`=?"
        );
        $syncHomeBlockStatement = $pdo->prepare(
            "UPDATE `{$blockTable}` SET `block_name`=?,`block_type`=?,`source_type`=?,`updatetime`=UNIX_TIMESTAMP() WHERE `page_key`=? AND `block_key`=?"
        );
        $restoreCoreStatement = $pdo->prepare(
            "UPDATE `{$blockTable}` SET `pc_visible`=1,`mobile_visible`=1,`status`='normal',`updatetime`=UNIX_TIMESTAMP() WHERE `page_key`=? AND `block_key`=?"
        );
        if (!$pageStatement || !$syncPageStatement || !$blockStatement || !$syncBlockStatement || !$syncHomeBlockStatement || !$restoreCoreStatement) {
            throw new \RuntimeException('无法创建页面化 CMS 初始化语句。');
        }

        foreach (PageSchemaRegistry::pages() as $pageKey => $page) {
            $pageStatement->execute([
                $pageKey,
                $page['name'],
                $page['type'],
                $page['route'],
                'layout.header.pc',
                'layout.footer.pc',
                'layout.header.mobile',
                'layout.footer.mobile',
                '{}',
            ]);
            $syncPageStatement->execute([
                $page['name'],
                $page['type'],
                $page['route'],
                $pageKey,
            ]);
            $weigh = count($page['blocks']) * 10;
            foreach ($page['blocks'] as $blockKey => $block) {
                $pcVisible = isset($block['fields']['pc_visible']['default'])
                    ? (int)$block['fields']['pc_visible']['default']
                    : 1;
                $mobileVisible = isset($block['fields']['mobile_visible']['default'])
                    ? (int)$block['fields']['mobile_visible']['default']
                    : 1;
                $blockStatement->execute([
                    $pageKey,
                    $blockKey,
                    $block['name'],
                    $block['type'],
                    $block['source_type'],
                    $pcVisible,
                    $mobileVisible,
                    $weigh,
                ]);
                if ($pageKey === 'home') {
                    $syncHomeBlockStatement->execute([
                        $block['name'],
                        $block['type'],
                        $block['source_type'],
                        $pageKey,
                        $blockKey,
                    ]);
                } else {
                    $syncBlockStatement->execute([
                        $block['name'],
                        $block['type'],
                        $block['source_type'],
                        $weigh,
                        $pageKey,
                        $blockKey,
                    ]);
                }
                if (!empty($block['core'])) {
                    // 旧版本允许隐藏核心块。升级时恢复结构性区块，避免严格页面残缺。
                    $restoreCoreStatement->execute([$pageKey, $blockKey]);
                }
                $weigh -= 10;
            }
        }

        $this->normalizeWorkshopPageBlockIds($connection, $prefix);
        $this->normalizeCulturePageBlockId($connection, $prefix);
        $this->retireRemovedPageBlocks($connection, $prefix);
        $this->retireUnusedBannerRows($connection, $prefix);
    }

    /**
     * 物理清理已退出首页信息架构的旧 PageBlock 元数据。
     *
     * 这里只删除 cms_page_block 及其旧通用引用；cms_home_section 历史内容继续保留，
     * 避免清理后台标识时误删运营资料。
     */
    protected function retireRemovedPageBlocks($connection, $prefix)
    {
        $blockTable = $prefix . 'cms_page_block';
        if (!$this->tableExists($connection, $blockTable)) {
            return;
        }

        $pdo = $this->getPdo($connection);
        $referenceTable = $prefix . 'cms_page_block_reference';

        foreach (PageSchemaRegistry::pages() as $pageKey => $pageDefinition) {
            $keys = PageSchemaRegistry::retiredBlockKeys($pageKey);
            if (!$keys) {
                continue;
            }
            $placeholders = implode(',', array_fill(0, count($keys), '?'));
            $select = $pdo->prepare(
                "SELECT `id` FROM `{$blockTable}` WHERE `page_key`=? AND `block_key` IN ({$placeholders})"
            );
            $select->execute(array_merge([$pageKey], $keys));
            $ids = array_values(array_filter(array_map('intval', $select->fetchAll(\PDO::FETCH_COLUMN))));
            if (!$ids) {
                continue;
            }

            $idPlaceholders = implode(',', array_fill(0, count($ids), '?'));
            if ($this->tableExists($connection, $referenceTable)) {
                $deleteReferences = $pdo->prepare(
                    "DELETE FROM `{$referenceTable}` WHERE `page_block_id` IN ({$idPlaceholders})"
                );
                $deleteReferences->execute($ids);
            }

            $deleteBlocks = $pdo->prepare(
                "DELETE FROM `{$blockTable}` WHERE `id` IN ({$idPlaceholders})"
            );
            $deleteBlocks->execute($ids);
        }
    }

    /**
     * 结构化单页和新版产品详情不再读取 cms_banner。
     * 升级时仅退役已经没有前台消费者的旧 Banner 行，素材文件本身不删除。
     */
    protected function retireUnusedBannerRows($connection, $prefix)
    {
        $table = $prefix . 'cms_banner';
        if (!$this->tableExists($connection, $table)) {
            return;
        }
        $pdo = $this->getPdo($connection);
        $keys = ['page.label', 'page.bags', 'page.boxes', 'page.about', 'page.contact', 'product.detail'];
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $statement = $pdo->prepare(
            "UPDATE `{$table}` SET `status`='hidden',`deletetime`=COALESCE(`deletetime`,UNIX_TIMESTAMP()),`updatetime`=UNIX_TIMESTAMP() " .
            "WHERE (`page_key` IN ({$placeholders}) OR `page_key` LIKE 'product.detail.%') AND `deletetime` IS NULL"
        );
        $statement->execute($keys);
    }

    /**
     * Insert the workshop page-block ID immediately before cases on upgraded
     * databases. Old databases appended workshop at the end, so move it into
     * the cases slot and shift only the intervening IDs. References are moved
     * in the same transaction. Fresh/already-normalized databases are no-ops.
     */
    protected function normalizeWorkshopPageBlockIds($connection, $prefix)
    {
        $blockTable = $prefix . 'cms_page_block';
        $referenceTable = $prefix . 'cms_page_block_reference';
        if (!$this->tableExists($connection, $blockTable)) {
            return;
        }

        $pdo = $this->getPdo($connection);
        $statement = $pdo->prepare(
            "SELECT `id`,`block_key` FROM `{$blockTable}` WHERE `page_key`='home' AND `block_key` IN ('workshop','cases') AND `deletetime` IS NULL"
        );
        if (!$statement) {
            return;
        }
        $statement->execute();
        $ids = [];
        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $ids[(string)$row['block_key']] = (int)$row['id'];
        }
        if (!isset($ids['workshop'], $ids['cases'])) {
            return;
        }

        $workshopId = $ids['workshop'];
        $casesId = $ids['cases'];
        if ($workshopId === $casesId - 1 || $workshopId < $casesId) {
            return;
        }

        $maxStatement = $pdo->prepare("SELECT MAX(`id`) FROM `{$blockTable}`");
        $maxStatement->execute();
        $temporaryId = (int)$maxStatement->fetchColumn() + 1;
        $hasReferences = $this->tableExists($connection, $referenceTable);

        $pdo->beginTransaction();
        try {
            $moveWorkshop = $pdo->prepare(
                "UPDATE `{$blockTable}` SET `id`=? WHERE `id`=? AND `page_key`='home' AND `block_key`='workshop'"
            );
            $moveWorkshop->execute([$temporaryId, $workshopId]);

            if ($hasReferences) {
                $moveWorkshopReferences = $pdo->prepare(
                    "UPDATE `{$referenceTable}` SET `page_block_id`=? WHERE `page_block_id`=?"
                );
                $moveWorkshopReferences->execute([$temporaryId, $workshopId]);
            }

            $shiftBlocks = $pdo->prepare(
                "UPDATE `{$blockTable}` SET `id`=`id`+1 WHERE `id`>=? AND `id`<? ORDER BY `id` DESC"
            );
            $shiftBlocks->execute([$casesId, $workshopId]);

            if ($hasReferences) {
                $shiftReferences = $pdo->prepare(
                    "UPDATE `{$referenceTable}` SET `page_block_id`=`page_block_id`+1 WHERE `page_block_id`>=? AND `page_block_id`<? ORDER BY `page_block_id` DESC,`id` DESC"
                );
                $shiftReferences->execute([$casesId, $workshopId]);
            }

            $placeWorkshop = $pdo->prepare(
                "UPDATE `{$blockTable}` SET `id`=? WHERE `id`=? AND `page_key`='home' AND `block_key`='workshop'"
            );
            $placeWorkshop->execute([$casesId, $temporaryId]);

            if ($hasReferences) {
                $placeWorkshopReferences = $pdo->prepare(
                    "UPDATE `{$referenceTable}` SET `page_block_id`=? WHERE `page_block_id`=?"
                );
                $placeWorkshopReferences->execute([$casesId, $temporaryId]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Place the enterprise-culture home PageBlock at stable ID 10.
     *
     * Existing installations already use IDs 1..9 for the fixed homepage
     * blocks after workshop normalization. PageBlock IDs from 10 up may belong
     * to other pages, so shift the intervening primary keys and their
     * references together before moving culture into ID 10. The migration is
     * idempotent and never changes display order/weigh.
     */
    protected function normalizeCulturePageBlockId($connection, $prefix)
    {
        $blockTable = $prefix . 'cms_page_block';
        $referenceTable = $prefix . 'cms_page_block_reference';
        if (!$this->tableExists($connection, $blockTable)) {
            return;
        }

        $pdo = $this->getPdo($connection);
        $statement = $pdo->prepare(
            "SELECT `id` FROM `{$blockTable}` WHERE `page_key`='home' AND `block_key`='culture' AND `deletetime` IS NULL LIMIT 1"
        );
        if (!$statement) {
            return;
        }
        $statement->execute();
        $cultureId = (int)$statement->fetchColumn();
        $targetId = 10;
        if ($cultureId <= 0 || $cultureId === $targetId) {
            return;
        }
        // A pre-existing unexpected ID below 10 may indicate a customized or
        // damaged identity layout. Do not destructively guess how to rewrite it.
        if ($cultureId < $targetId) {
            return;
        }

        $maxStatement = $pdo->query("SELECT MAX(`id`) FROM `{$blockTable}`");
        $temporaryId = (int)($maxStatement ? $maxStatement->fetchColumn() : 0) + 1;
        $hasReferences = $this->tableExists($connection, $referenceTable);

        $pdo->beginTransaction();
        try {
            $moveCulture = $pdo->prepare(
                "UPDATE `{$blockTable}` SET `id`=? WHERE `id`=? AND `page_key`='home' AND `block_key`='culture'"
            );
            $moveCulture->execute([$temporaryId, $cultureId]);

            if ($hasReferences) {
                $moveCultureReferences = $pdo->prepare(
                    "UPDATE `{$referenceTable}` SET `page_block_id`=? WHERE `page_block_id`=?"
                );
                $moveCultureReferences->execute([$temporaryId, $cultureId]);
            }

            $shiftBlocks = $pdo->prepare(
                "UPDATE `{$blockTable}` SET `id`=`id`+1 WHERE `id`>=? AND `id`<? ORDER BY `id` DESC"
            );
            $shiftBlocks->execute([$targetId, $cultureId]);

            if ($hasReferences) {
                $shiftReferences = $pdo->prepare(
                    "UPDATE `{$referenceTable}` SET `page_block_id`=`page_block_id`+1 WHERE `page_block_id`>=? AND `page_block_id`<? ORDER BY `page_block_id` DESC,`id` DESC"
                );
                $shiftReferences->execute([$targetId, $cultureId]);
            }

            $placeCulture = $pdo->prepare(
                "UPDATE `{$blockTable}` SET `id`=? WHERE `id`=? AND `page_key`='home' AND `block_key`='culture'"
            );
            $placeCulture->execute([$targetId, $temporaryId]);

            if ($hasReferences) {
                $placeCultureReferences = $pdo->prepare(
                    "UPDATE `{$referenceTable}` SET `page_block_id`=? WHERE `page_block_id`=?"
                );
                $placeCultureReferences->execute([$targetId, $temporaryId]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * 将克隆导入及旧版本遗留的内部链接统一迁移到当前 ThinkPHP 干净路由。
     * 这里只做数据迁移，不注册任何旧 .html 运行时兼容路由。
     *
     * @param mixed  $connection
     * @param string $prefix
     * @return void
     */
    protected function normalizeCmsUrls($connection, $prefix)
    {
        $pdo = $this->getPdo($connection);
        if (!class_exists(__NAMESPACE__ . '\\CmsUrlService', false)) {
            require_once __DIR__ . '/CmsUrlService.php';
        }
        $urls = new CmsUrlService();
        $hints = $this->cmsDetailUrlHints($pdo, $prefix);

        $canonicalTables = [
            'cms_article' => 'article',
            'cms_page' => 'page',
        ];
        foreach ($canonicalTables as $tableName => $type) {
            $table = $prefix . $tableName;
            if (!$this->tableExists($connection, $table)) {
                continue;
            }
            $statement = $pdo->query("SELECT `id`,`slug` FROM `{$table}` WHERE `slug` <> ''");
            $rows = $statement ? $statement->fetchAll(\PDO::FETCH_ASSOC) : [];
            $update = $pdo->prepare("UPDATE `{$table}` SET `canonical_url`=? WHERE `id`=?");
            foreach ($rows as $row) {
                switch ($type) {
                    case 'page': $value = $urls->page($row['slug']); break;
                    case 'article':
                    default: $value = $urls->newsDetail($row['slug']); break;
                }
                $update->execute([$value, (int)$row['id']]);
            }
        }

        $pageTable = $prefix . 'cms_page_config';
        if ($this->tableExists($connection, $pageTable)) {
            $updateRoute = $pdo->prepare("UPDATE `{$pageTable}` SET `route_pattern`=?,`updatetime`=UNIX_TIMESTAMP() WHERE `page_key`=?");
            foreach (PageSchemaRegistry::pages() as $pageKey => $page) {
                $updateRoute->execute([$page['route'], $pageKey]);
            }
        }

        $urlColumns = [
            'cms_navigation' => ['url'],
            'cms_banner' => ['link_url', 'mobile_link_url'],
            'cms_home_section' => ['more_url'],
            'cms_layout_component' => ['link_url'],
            'cms_page_content_block' => ['link_url'],
        ];
        foreach ($urlColumns as $tableName => $columns) {
            $table = $prefix . $tableName;
            if (!$this->tableExists($connection, $table)) {
                continue;
            }
            foreach ($columns as $column) {
                if ($this->columnExists($connection, $table, $column)) {
                    $this->normalizeCmsUrlColumn($pdo, $table, $column, $urls, $hints);
                }
            }
        }

        $navigationTable = $prefix . 'cms_navigation';
        if ($this->tableExists($connection, $navigationTable)
            && $this->columnExists($connection, $navigationTable, 'link_type')
            && $this->columnExists($connection, $navigationTable, 'link_value')) {
            $statement = $pdo->query("SELECT `id`,`link_type`,`link_value` FROM `{$navigationTable}` WHERE `link_value` <> ''");
            $rows = $statement ? $statement->fetchAll(\PDO::FETCH_ASSOC) : [];
            $update = $pdo->prepare("UPDATE `{$navigationTable}` SET `link_value`=? WHERE `id`=?");
            foreach ($rows as $row) {
                if ((string)$row['link_type'] !== 'url') {
                    continue;
                }
                $value = $this->normalizeCmsStoredUrl($row['link_value'], $urls, $hints);
                if ($value !== (string)$row['link_value']) {
                    $update->execute([$value, (int)$row['id']]);
                }
            }
        }
        $this->deduplicateCmsNavigation($pdo, $navigationTable);

        $jsonColumns = [
            'cms_home_section' => ['config_json'],
            'cms_layout_component' => ['config_json'],
            'cms_page_config' => ['config_json'],
            'cms_page_block' => ['config_json'],
            'cms_page_content_block' => ['extra_json'],
        ];
        foreach ($jsonColumns as $tableName => $columns) {
            $table = $prefix . $tableName;
            if (!$this->tableExists($connection, $table)) {
                continue;
            }
            foreach ($columns as $column) {
                if ($this->columnExists($connection, $table, $column)) {
                    $this->normalizeCmsJsonColumn($pdo, $table, $column, $urls, $hints);
                }
            }
        }

        $htmlColumns = [
            'cms_product' => ['content', 'features', 'applications', 'construction', 'precautions'],
            'cms_article' => ['content'],
            'cms_case' => ['content'],
            'cms_product_image' => [
                'mobile_image' => "varchar(255) NOT NULL DEFAULT '' AFTER `image`",
            ],
            'cms_product_section' => [
                'subtitle' => "varchar(255) NOT NULL DEFAULT '' AFTER `title`",
            ],
            'cms_page' => ['content', 'mobile_content'],
            'cms_home_section' => ['content', 'mobile_content'],
            'cms_page_content_block' => ['content'],
        ];
        foreach ($htmlColumns as $tableName => $columns) {
            $table = $prefix . $tableName;
            if (!$this->tableExists($connection, $table)) {
                continue;
            }
            foreach ($columns as $column) {
                if ($this->columnExists($connection, $table, $column)) {
                    $this->normalizeCmsHtmlColumn($pdo, $table, $column, $urls, $hints);
                }
            }
        }
    }

    private function deduplicateCmsNavigation($pdo, $table)
    {
        if (!$table) {
            return;
        }
        if (!class_exists(__NAMESPACE__ . '\\NavigationDeduplicationService', false)) {
            require_once __DIR__ . '/NavigationDeduplicationService.php';
        }
        $statement = $pdo->query("SELECT `id`,`parent_id`,`position`,`url`,`weigh`,`status`,`deletetime` FROM `{$table}` WHERE `deletetime` IS NULL AND `status`='normal' ORDER BY `weigh` DESC,`id` ASC");
        $rows = $statement ? $statement->fetchAll(\PDO::FETCH_ASSOC) : [];
        $plan = (new NavigationDeduplicationService())->plan($rows);
        if (!$plan) {
            return;
        }
        $reparent = $pdo->prepare("UPDATE `{$table}` SET `parent_id`=?,`updatetime`=UNIX_TIMESTAMP() WHERE `parent_id`=? AND `deletetime` IS NULL");
        foreach ($plan as $duplicateId => $keeperId) {
            $reparent->execute([(int)$keeperId, (int)$duplicateId]);
        }
        $remove = $pdo->prepare("UPDATE `{$table}` SET `status`='hidden',`deletetime`=UNIX_TIMESTAMP(),`updatetime`=UNIX_TIMESTAMP() WHERE `id`=? AND `deletetime` IS NULL");
        foreach ($plan as $duplicateId => $keeperId) {
            $remove->execute([(int)$duplicateId]);
        }
    }

    private function cmsDetailUrlHints($pdo, $prefix)
    {
        $hints = [];
        foreach (['cms_article' => 'article'] as $tableName => $type) {
            $table = $prefix . $tableName;
            try {
                $statement = $pdo->query("SELECT `slug` FROM `{$table}` WHERE `slug` <> ''");
                $rows = $statement ? $statement->fetchAll(\PDO::FETCH_ASSOC) : [];
                foreach ($rows as $row) {
                    $slug = (string)$row['slug'];
                    if (!isset($hints[$slug])) {
                        $hints[$slug] = $type;
                    }
                }
            } catch (\Throwable $e) {
                // 表可能尚不存在；安装流程稍后会进行完整性检查。
            }
        }
        return $hints;
    }

    private function normalizeCmsStoredUrl($value, CmsUrlService $urls, array $hints)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return $value;
        }
        $hint = '';
        $path = parse_url($value, PHP_URL_PATH);
        if (is_string($path) && preg_match('#^/articles/([a-zA-Z0-9_-]+)\.html$#', $path, $match)) {
            $hint = isset($hints[$match[1]]) ? $hints[$match[1]] : 'article';
        }
        return $urls->normalizeInternal($value, $hint);
    }

    private function normalizeCmsUrlColumn($pdo, $table, $column, CmsUrlService $urls, array $hints)
    {
        $statement = $pdo->query("SELECT `id`,`{$column}` FROM `{$table}` WHERE `{$column}` IS NOT NULL AND `{$column}` <> ''");
        $rows = $statement ? $statement->fetchAll(\PDO::FETCH_ASSOC) : [];
        $update = $pdo->prepare("UPDATE `{$table}` SET `{$column}`=? WHERE `id`=?");
        foreach ($rows as $row) {
            $old = (string)$row[$column];
            $new = $this->normalizeCmsStoredUrl($old, $urls, $hints);
            if ($new !== $old) {
                $update->execute([$new, (int)$row['id']]);
            }
        }
    }

    private function normalizeCmsJsonColumn($pdo, $table, $column, CmsUrlService $urls, array $hints)
    {
        $statement = $pdo->query("SELECT `id`,`{$column}` FROM `{$table}` WHERE `{$column}` IS NOT NULL AND `{$column}` <> ''");
        $rows = $statement ? $statement->fetchAll(\PDO::FETCH_ASSOC) : [];
        $update = $pdo->prepare("UPDATE `{$table}` SET `{$column}`=? WHERE `id`=?");
        foreach ($rows as $row) {
            $decoded = json_decode((string)$row[$column], true);
            if (!is_array($decoded)) {
                continue;
            }
            $normalized = $this->normalizeCmsJsonValue($decoded, $urls, $hints);
            $new = json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if (is_string($new) && $new !== (string)$row[$column]) {
                $update->execute([$new, (int)$row['id']]);
            }
        }
    }

    private function normalizeCmsJsonValue($value, CmsUrlService $urls, array $hints, $key = '')
    {
        if (is_array($value)) {
            foreach ($value as $childKey => $childValue) {
                $value[$childKey] = $this->normalizeCmsJsonValue($childValue, $urls, $hints, (string)$childKey);
            }
            return $value;
        }
        if (!is_string($value)) {
            return $value;
        }
        $key = strtolower((string)$key);
        if (in_array($key, ['url', 'href', 'link', 'link_url', 'mobile_link_url', 'more_url', 'canonical_url', 'action', 'other_url', 'left_url', 'right_url'], true)) {
            return $this->normalizeCmsStoredUrl($value, $urls, $hints);
        }
        if ($key === 'mobile_content' && strpos($value, '<') !== false) {
            return $this->normalizeCmsHtmlValue($value, $urls, $hints);
        }
        return $value;
    }

    private function normalizeCmsHtmlColumn($pdo, $table, $column, CmsUrlService $urls, array $hints)
    {
        $statement = $pdo->query("SELECT `id`,`{$column}` FROM `{$table}` WHERE `{$column}` IS NOT NULL AND `{$column}` <> ''");
        $rows = $statement ? $statement->fetchAll(\PDO::FETCH_ASSOC) : [];
        $update = $pdo->prepare("UPDATE `{$table}` SET `{$column}`=? WHERE `id`=?");
        foreach ($rows as $row) {
            $old = (string)$row[$column];
            $new = $this->normalizeCmsHtmlValue($old, $urls, $hints);
            if (is_string($new) && $new !== $old) {
                $update->execute([$new, (int)$row['id']]);
            }
        }
    }

    private function normalizeCmsHtmlValue($old, CmsUrlService $urls, array $hints)
    {
        return preg_replace_callback(
            '~\b(href|action)=("|\')([^"\']+)(\2)~i',
            function ($match) use ($urls, $hints) {
                $normalized = $this->normalizeCmsStoredUrl(html_entity_decode($match[3], ENT_QUOTES, 'UTF-8'), $urls, $hints);
                return $match[1] . '=' . $match[2] . htmlspecialchars($normalized, ENT_QUOTES, 'UTF-8') . $match[2];
            },
            (string)$old
        );
    }

    /**
     * @param mixed  $connection
     * @param string $table
     * @param string $column
     * @return bool
     */
    protected function columnExists($connection, $table, $column)
    {
        $statement = $this->getPdo($connection)->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
        );
        if (!$statement) {
            throw new \RuntimeException('无法创建数据库字段检查语句。');
        }
        $statement->execute([$table, $column]);
        return (int)$statement->fetchColumn() > 0;
    }

    /**
     * @param mixed  $connection
     * @param string $prefix
     * @return array
     */
    public function missingTables($connection, $prefix)
    {
        $prefix = $this->normalizePrefix($prefix);
        $missing = [];
        foreach ($this->requiredTables as $table) {
            $fullName = $prefix . $table;
            if (!$this->tableExists($connection, $fullName)) {
                $missing[] = $fullName;
            }
        }
        return $missing;
    }

    /**
     * @param mixed  $connection
     * @param string $table
     * @return bool
     */
    protected function tableExists($connection, $table)
    {
        $statement = $this->getPdo($connection)->prepare(
            'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
        );
        if (!$statement) {
            throw new \RuntimeException('无法创建数据库查询语句，请检查数据库连接。');
        }
        $statement->execute([$table]);
        return (int)$statement->fetchColumn() > 0;
    }

    /**
     * 获取已初始化的 PDO。ThinkPHP 5 的 Db::connect() 使用懒连接，
     * 在首次 query/execute 之前 Connection::getPdo() 会返回 false。
     *
     * @param mixed $connection
     * @return \PDO
     */
    protected function getPdo($connection)
    {
        return self::initializeConnection($connection);
    }

    /**
     * Return a usable PDO for either a ThinkPHP lazy connection or an existing PDO.
     *
     * @param mixed $connection
     * @return \PDO
     */
    public static function initializeConnection($connection)
    {
        if ($connection instanceof \PDO) {
            return $connection;
        }
        if (!is_object($connection) || !method_exists($connection, 'getPdo')) {
            throw new \InvalidArgumentException('不支持的数据库连接类型。');
        }

        $pdo = $connection->getPdo();
        if (!$pdo && method_exists($connection, 'execute')) {
            $connection->execute('SELECT 1');
            $pdo = $connection->getPdo();
        }
        if (!$pdo instanceof \PDO && !is_object($pdo)) {
            throw new \RuntimeException('数据库连接初始化失败，请检查 application/database.php 或 .env 配置。');
        }
        return $pdo;
    }

    /**
     * @param string $prefix
     * @return string
     */
    protected function normalizePrefix($prefix)
    {
        $prefix = (string)$prefix;
        if ($prefix === '' || !preg_match('/^[a-zA-Z0-9_]+$/', $prefix)) {
            throw new \InvalidArgumentException('数据库表前缀不合法。');
        }
        return $prefix;
    }

    protected function clearCaches()
    {
        Cache::rm('__menu__');
        (new CmsCacheInvalidator())->invalidateLayout();
        CacheService::clearPageConfigs(array_keys(PageSchemaRegistry::pages()));
    }
}

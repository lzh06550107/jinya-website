<?php

namespace app\common\service\cms;

/**
 * 固定页面与功能块注册表。
 *
 * 页面结构由代码定义，数据库只保存运营配置，避免退化为任意页面构建器。
 */
class PageSchemaRegistry
{
    protected static $pages;

    public static function pages()
    {
        if (self::$pages === null) {
            self::$pages = self::buildPages();
        }
        return self::$pages;
    }

    public static function page($pageKey)
    {
        $pages = self::pages();
        if (!isset($pages[$pageKey])) {
            throw new \InvalidArgumentException('未注册的页面：' . $pageKey);
        }
        return $pages[$pageKey];
    }

    public static function block($pageKey, $blockKey)
    {
        $page = self::page($pageKey);
        if (!isset($page['blocks'][$blockKey])) {
            throw new \InvalidArgumentException('页面未注册功能块：' . $pageKey . '.' . $blockKey);
        }
        return $page['blocks'][$blockKey];
    }

    /**
     * 已退出当前前台信息架构的旧功能块。
     *
     * 这些键只用于兼容升级数据库和即时过滤旧记录，不能重新注册到页面 Schema。
     */
    public static function retiredBlockKeys($pageKey)
    {
        $map = [
            'home' => ['cases', 'advantages', 'news'],
            'news.index' => ['category_navigation', 'pagination'],
            'product.detail' => ['banner'],
            'page.label' => ['banner'],
            'page.bags' => ['banner'],
            'page.boxes' => ['banner'],
            'page.about' => ['banner'],
            'page.contact' => ['banner'],
        ];
        $pageKey = trim((string)$pageKey);
        return isset($map[$pageKey]) ? $map[$pageKey] : [];
    }

    public static function retiredHomeBlockKeys()
    {
        return self::retiredBlockKeys('home');
    }

    /**
     * 固定页面级字段只允许结构化 SEO 配置。
     */
    public static function sanitizePageConfig($pageKey, array $input)
    {
        $page = self::page($pageKey);
        $fields = isset($page['fields']) ? $page['fields'] : [];
        foreach ($input as $name => $value) {
            if (!isset($fields[$name])) {
                throw new \InvalidArgumentException('页面不支持字段：' . $name);
            }
        }
        $result = [];
        foreach ($fields as $name => $definition) {
            if (!array_key_exists($name, $input)) {
                if (array_key_exists('default', $definition)) {
                    $result[$name] = self::sanitizeField($name, $definition['default'], $definition);
                    continue;
                }
                if (!empty($definition['required'])) {
                    throw new \InvalidArgumentException(self::fieldTitle($definition, $name) . '不能为空');
                }
                continue;
            }
            $result[$name] = self::sanitizeField($name, $input[$name], $definition);
        }
        return $result;
    }

    /**
     * 仅保留 schema 允许的字段，并执行保存前校验。
     */
    public static function sanitizeBlockConfig($pageKey, $blockKey, array $input)
    {
        $schema = self::block($pageKey, $blockKey);
        $fields = $schema['fields'];
        $allowed = array_keys($fields);

        foreach ($fields as $name => $definition) {
            if ($definition['type'] === 'device_image') {
                $allowed[] = 'pc_' . $name;
                $allowed[] = 'mobile_' . $name;
            }
        }

        foreach ($input as $name => $value) {
            if (!in_array($name, $allowed, true)) {
                throw new \InvalidArgumentException('功能块不支持字段：' . $name);
            }
        }

        $result = [];
        foreach ($fields as $name => $definition) {
            if ($definition['type'] === 'device_image') {
                $result[$name] = self::sanitizeField($name, isset($input[$name]) ? $input[$name] : '', $definition);
                $result['pc_' . $name] = self::sanitizeField('pc_' . $name, isset($input['pc_' . $name]) ? $input['pc_' . $name] : '', ['type' => 'image', 'required' => false]);
                $result['mobile_' . $name] = self::sanitizeField('mobile_' . $name, isset($input['mobile_' . $name]) ? $input['mobile_' . $name] : '', ['type' => 'image', 'required' => false]);
                continue;
            }
            if (!array_key_exists($name, $input)) {
                if (array_key_exists('default', $definition)) {
                    $result[$name] = self::sanitizeField($name, $definition['default'], $definition);
                    continue;
                }
                if (!empty($definition['required'])) {
                    throw new \InvalidArgumentException(self::fieldTitle($definition, $name) . '不能为空');
                }
                continue;
            }
            $result[$name] = self::sanitizeField($name, $input[$name], $definition);
        }

        if (!empty($schema['core'])) {
            if (isset($result['enabled']) && !$result['enabled']) {
                throw new \InvalidArgumentException('核心功能块不能停用');
            }
            if ((isset($result['pc_visible']) && !$result['pc_visible']) || (isset($result['mobile_visible']) && !$result['mobile_visible'])) {
                throw new \InvalidArgumentException('核心功能块不能在单独终端隐藏');
            }
        }

        return $result;
    }

    /**
     * 将 device_image 字段解析为当前终端真正使用的图片。
     */
    public static function resolveDeviceImages(array $schema, array $config, $device)
    {
        if (!in_array($device, ['pc', 'mobile'], true)) {
            throw new \InvalidArgumentException('终端类型必须是 pc 或 mobile');
        }
        foreach ($schema['fields'] as $name => $definition) {
            if ($definition['type'] !== 'device_image') {
                continue;
            }
            $deviceKey = $device . '_' . $name;
            $config[$name] = !empty($config[$deviceKey]) ? $config[$deviceKey] : (isset($config[$name]) ? $config[$name] : '');
            unset($config['pc_' . $name], $config['mobile_' . $name]);
        }
        return $config;
    }

    protected static function sanitizeField($name, $value, array $definition)
    {
        $type = $definition['type'];
        if ($type === 'boolean') {
            $value = in_array($value, [1, '1', true, 'true', 'on'], true) ? 1 : 0;
        } elseif ($type === 'integer') {
            if ($value === '' && empty($definition['required'])) {
                return isset($definition['default']) ? (int)$definition['default'] : 0;
            }
            if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                throw new \InvalidArgumentException(self::fieldTitle($definition, $name) . '必须是整数');
            }
            $value = (int)$value;
            if (isset($definition['min']) && $value < $definition['min']) {
                throw new \InvalidArgumentException(self::fieldTitle($definition, $name) . '不能小于' . $definition['min']);
            }
            if (isset($definition['max']) && $value > $definition['max']) {
                throw new \InvalidArgumentException(self::fieldTitle($definition, $name) . '不能大于' . $definition['max']);
            }
        } elseif ($type === 'enum') {
            $value = trim((string)$value);
            if (!in_array($value, $definition['options'], true)) {
                throw new \InvalidArgumentException(self::fieldTitle($definition, $name) . '选项不合法');
            }
        } elseif ($type === 'link') {
            $value = trim((string)$value);
            if ($value !== '' && !self::isSafeLink($value)) {
                throw new \InvalidArgumentException(self::fieldTitle($definition, $name) . '包含不安全协议');
            }
        } elseif (in_array($type, ['text', 'textarea', 'image', 'device_image'], true)) {
            $value = trim((string)$value);
        } else {
            throw new \InvalidArgumentException('未支持的字段类型：' . $type);
        }

        if (!empty($definition['required']) && ($value === '' || $value === null)) {
            throw new \InvalidArgumentException(self::fieldTitle($definition, $name) . '不能为空');
        }
        if (isset($definition['max_length']) && is_string($value) && self::stringLength($value) > $definition['max_length']) {
            throw new \InvalidArgumentException(self::fieldTitle($definition, $name) . '长度不能超过' . $definition['max_length']);
        }
        return $value;
    }

    protected static function isSafeLink($value)
    {
        if ($value === '' || $value[0] === '/' || $value[0] === '#' || $value[0] === '?') {
            return true;
        }
        $scheme = parse_url($value, PHP_URL_SCHEME);
        return $scheme !== null && in_array(strtolower($scheme), ['http', 'https', 'tel', 'mailto'], true);
    }

    protected static function stringLength($value)
    {
        return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    }

    protected static function fieldTitle(array $definition, $fallback)
    {
        return isset($definition['title']) ? $definition['title'] : $fallback;
    }

    protected static function buildPages()
    {
        return [
            'home' => self::pageDefinition('首页', 'fixed', '/', [
                'hero' => self::blockDefinition('首页轮播', 'hero', true, self::heroFields(), 'page'),
                'about' => self::blockDefinition('顶部企业简介', 'content', true, self::contentFields(), 'page'),
                'products' => self::blockDefinition('推荐产品', 'business_list', true, self::homeListFields('product', 7, 3), 'product'),
                'service' => self::blockDefinition('一体化服务', 'items', true, self::serviceFields(), 'page'),
                'workshop' => self::blockDefinition('生产车间', 'items', true, self::workshopFields(), 'page'),
                'company' => self::blockDefinition('底部企业介绍', 'content', true, self::contentFields(), 'page'),
                'culture' => self::blockDefinition('企业文化', 'items', true, self::workshopFields(), 'page'),
            ]),
            'product.index' => self::pageDefinition('产品总列表', 'list', '/products', [
                'banner' => self::blockDefinition('产品栏目 Banner', 'banner', false, self::bannerFields(), 'page'),
                'category_navigation' => self::blockDefinition('产品分类导航', 'navigation', true, self::navigationFields(), 'product_category'),
                'list' => self::blockDefinition('产品列表', 'business_list', true, self::productPagedListFields(), 'product'),
                'pagination' => self::blockDefinition('产品分页', 'pagination', true, self::paginationFields(), 'query'),
            ]),
            'product.category' => self::pageDefinition('产品分类列表', 'dynamic_list', '/products?category={category}', [
                'banner' => self::blockDefinition('分类 Banner', 'banner', false, self::bannerFields(), 'product_category'),
                'category_navigation' => self::blockDefinition('产品分类导航', 'navigation', true, self::navigationFields(), 'product_category'),
                'list' => self::blockDefinition('分类产品列表', 'business_list', true, self::productPagedListFields(), 'product'),
                'pagination' => self::blockDefinition('产品分页', 'pagination', true, self::paginationFields(), 'query'),
            ]),
            'product.detail' => self::pageDefinition('产品详情', 'dynamic_detail', '/product/{slug}', [
                'gallery' => self::blockDefinition('产品相册', 'gallery', true, self::visibilityFields(true), 'product'),
                'summary' => self::blockDefinition('产品基础信息', 'detail', true, self::visibilityFields(true), 'product'),
                'parameters' => self::blockDefinition('技术参数', 'parameters', false, self::visibilityFields(false), 'product'),
                'content' => self::blockDefinition('产品详细介绍', 'rich_text', true, self::visibilityFields(true), 'product'),
                'related' => self::blockDefinition('相关推荐', 'business_list', false, self::listFields('product'), 'product'),
                'contact' => self::blockDefinition('在线咨询入口', 'contact', false, self::buttonFields(), 'global'),
            ]),
            'news.index' => self::pageDefinition('新闻总列表', 'list', '/news', self::newsIndexPageBlocks()),
            'news.category' => self::pageDefinition('新闻分类列表', 'dynamic_list', '/news-list/{category}', self::newsListPageBlocks()),
            'news.detail' => self::pageDefinition('新闻详情', 'dynamic_detail', '/news/{slug}', self::newsDetailPageBlocks()),
            'page.label' => self::pageDefinition('不干胶/卷标', 'fixed_page', '/page/label', []),
            'page.bags' => self::pageDefinition('包装袋无版印刷', 'fixed_page', '/page/bags', []),
            'page.boxes' => self::pageDefinition('彩盒', 'fixed_page', '/page/boxes', []),
            'page.about' => self::aboutPage(),
            'page.contact' => self::contactPage(),
            'search' => self::pageDefinition('搜索结果', 'system', '/search', [
                'banner' => self::blockDefinition('搜索页 Banner', 'banner', false, self::bannerFields(), 'page'),
                'form' => self::blockDefinition('搜索表单', 'search_form', true, self::searchFormFields(), 'query'),
                'results' => self::blockDefinition('搜索结果', 'search_results', true, self::searchResultFields(), 'query'),
                'pagination' => self::blockDefinition('搜索分页', 'pagination', false, self::paginationFields(), 'query'),
            ]),
            'sitemap' => self::pageDefinition('网站地图', 'system', '/sitemap', [
                'banner' => self::blockDefinition('网站地图 Banner', 'banner', false, self::bannerFields(), 'page'),
                'links' => self::blockDefinition('网站地图链接', 'sitemap', true, self::sitemapFields(), 'system'),
            ]),
            'error.404' => self::pageDefinition('404 页面', 'system', '404', [
                'message' => self::blockDefinition('错误提示', 'error', true, self::errorFields(), 'page'),
            ]),
        ];
    }

    protected static function pageDefinition($name, $type, $route, array $blocks)
    {
        return [
            'name' => $name,
            'type' => $type,
            'route' => $route,
            'allow_custom_blocks' => false,
            'fields' => self::pageFields(),
            'blocks' => $blocks,
        ];
    }

    protected static function blockDefinition($name, $type, $core, array $fields, $sourceType)
    {
        if (!isset($fields['pc_visible'])) {
            $fields['pc_visible'] = ['title' => 'PC 显示', 'type' => 'boolean', 'default' => 1];
        }
        if (!isset($fields['mobile_visible'])) {
            $fields['mobile_visible'] = ['title' => '移动端显示', 'type' => 'boolean', 'default' => 1];
        }
        if (!isset($fields['enabled'])) {
            $fields['enabled'] = ['title' => '启用状态', 'type' => 'boolean', 'default' => 1];
        }
        return [
            'name' => $name,
            'type' => $type,
            'core' => (bool)$core,
            'source_type' => $sourceType,
            'empty_behavior' => $core ? 'required' : 'hide_block',
            'fields' => $fields,
        ];
    }

    protected static function pageFields()
    {
        return [
            'seo_title' => ['title' => 'SEO 标题', 'type' => 'text', 'required' => false, 'max_length' => 255, 'default' => ''],
            'seo_keywords' => ['title' => 'SEO 关键词', 'type' => 'text', 'required' => false, 'max_length' => 255, 'default' => ''],
            'seo_description' => ['title' => 'SEO 描述', 'type' => 'textarea', 'required' => false, 'max_length' => 500, 'default' => ''],
            'canonical_url' => ['title' => 'Canonical 地址', 'type' => 'link', 'required' => false, 'max_length' => 500, 'default' => ''],
            'robots' => ['title' => '搜索引擎规则', 'type' => 'enum', 'options' => ['index,follow', 'noindex,follow', 'noindex,nofollow'], 'default' => 'index,follow'],
        ];
    }

    protected static function heroFields()
    {
        return [
            'title' => ['title' => '标题', 'type' => 'text', 'required' => false, 'max_length' => 150],
            'subtitle' => ['title' => '副标题', 'type' => 'textarea', 'required' => false, 'max_length' => 255],
            'image' => ['title' => '轮播图片', 'type' => 'device_image', 'required' => false],
            'button_text' => ['title' => '按钮文字', 'type' => 'text', 'required' => false, 'max_length' => 100],
            'link_url' => ['title' => '按钮链接', 'type' => 'link', 'required' => false, 'max_length' => 500],
        ];
    }

    protected static function bannerFields()
    {
        return [
            'title' => ['title' => 'Banner 标题', 'type' => 'text', 'required' => false, 'max_length' => 150],
            'subtitle' => ['title' => 'Banner 副标题', 'type' => 'text', 'required' => false, 'max_length' => 255],
            'image' => ['title' => 'Banner 图片', 'type' => 'device_image', 'required' => false],
        ];
    }

    protected static function contentFields()
    {
        return [
            'title' => ['title' => '标题', 'type' => 'text', 'required' => false, 'max_length' => 150],
            'subtitle' => ['title' => '副标题', 'type' => 'text', 'required' => false, 'max_length' => 255],
            'content' => ['title' => '内容', 'type' => 'textarea', 'required' => false, 'max_length' => 5000],
            'image' => ['title' => '图片', 'type' => 'image', 'required' => false],
        ];
    }

    protected static function serviceFields()
    {
        return [
            'title' => ['title' => '标题', 'type' => 'text', 'required' => false, 'max_length' => 150],
            'subtitle' => ['title' => '副标题', 'type' => 'text', 'required' => false, 'max_length' => 255],
            'manager_value' => ['title' => '项目经理数值', 'type' => 'text', 'required' => false, 'max_length' => 50],
            'manager_text' => ['title' => '项目经理说明', 'type' => 'text', 'required' => false, 'max_length' => 150],
            'team_value' => ['title' => '施工团队数值', 'type' => 'text', 'required' => false, 'max_length' => 50],
            'team_text' => ['title' => '施工团队说明', 'type' => 'text', 'required' => false, 'max_length' => 150],
            'warranty_value' => ['title' => '质保数值', 'type' => 'text', 'required' => false, 'max_length' => 50],
            'warranty_text' => ['title' => '质保说明', 'type' => 'text', 'required' => false, 'max_length' => 150],
            'response_value' => ['title' => '响应数值', 'type' => 'text', 'required' => false, 'max_length' => 50],
            'response_text' => ['title' => '响应说明', 'type' => 'text', 'required' => false, 'max_length' => 150],
            'button_text' => ['title' => '咨询按钮文字', 'type' => 'text', 'required' => false, 'max_length' => 100],
            'link_url' => ['title' => '咨询链接', 'type' => 'link', 'required' => false, 'max_length' => 500],
            // 保留旧版泛化字段，避免升级后已有配置因 schema 变化整体回退。
            'content' => ['title' => '补充说明', 'type' => 'textarea', 'required' => false, 'max_length' => 5000],
            'image' => ['title' => '公共图片', 'type' => 'image', 'required' => false],
        ];
    }

    protected static function workshopFields()
    {
        return [
            'title' => ['title' => '标题', 'type' => 'text', 'required' => false, 'max_length' => 150],
            'subtitle' => ['title' => '副标题', 'type' => 'text', 'required' => false, 'max_length' => 255],
            'pc_visible' => ['title' => 'PC 显示', 'type' => 'boolean', 'default' => 1],
            'mobile_visible' => ['title' => '移动端显示', 'type' => 'boolean', 'default' => 1],
        ];
    }

    protected static function advantageFields()
    {
        $fields = [
            'title' => ['title' => '标题', 'type' => 'text', 'required' => false, 'max_length' => 150],
            'subtitle' => ['title' => '副标题', 'type' => 'text', 'required' => false, 'max_length' => 255],
        ];
        for ($index = 1; $index <= 4; $index++) {
            $fields['title' . $index] = ['title' => '优势' . $index . '标题', 'type' => 'text', 'required' => false, 'max_length' => 100];
            $fields['text' . $index] = ['title' => '优势' . $index . '说明', 'type' => 'textarea', 'required' => false, 'max_length' => 1000];
        }
        // 原严格页面的优势图片和图标属于固定模板资源，不开放 PC/移动端专属上传。
        return $fields;
    }

    protected static function buttonFields()
    {
        return [
            'button_text' => ['title' => '按钮文字', 'type' => 'text', 'required' => false, 'max_length' => 100],
            'link_url' => ['title' => '按钮链接', 'type' => 'link', 'required' => false, 'max_length' => 500],
        ];
    }

    protected static function visibilityFields($core)
    {
        return [
            'title' => ['title' => '功能块标题', 'type' => 'text', 'required' => false, 'max_length' => 150],
            'pc_visible' => ['title' => 'PC 显示', 'type' => 'boolean', 'default' => 1],
            'mobile_visible' => ['title' => '移动端显示', 'type' => 'boolean', 'default' => 1],
            'enabled' => ['title' => '启用状态', 'type' => 'boolean', 'default' => $core ? 1 : 1],
        ];
    }

    protected static function listFields($contentType)
    {
        return [
            'title' => ['title' => '功能块标题', 'type' => 'text', 'required' => false, 'max_length' => 150],
            'subtitle' => ['title' => '副标题', 'type' => 'text', 'required' => false, 'max_length' => 255],
            'source_mode' => ['title' => '数据来源', 'type' => 'enum', 'options' => ['manual', 'auto'], 'default' => 'auto'],
            'content_type' => ['title' => '内容类型', 'type' => 'enum', 'options' => [$contentType], 'default' => $contentType],
            'category_id' => ['title' => '分类', 'type' => 'integer', 'required' => false, 'min' => 0, 'default' => 0],
            'display_count' => ['title' => '显示数量', 'type' => 'integer', 'required' => true, 'min' => 1, 'max' => 100, 'default' => 6],
            'sort_mode' => ['title' => '排序方式', 'type' => 'enum', 'options' => ['manual', 'weigh_desc', 'publish_time_desc'], 'default' => 'weigh_desc'],
            'button_text' => ['title' => '查看更多文字', 'type' => 'text', 'required' => false, 'max_length' => 100],
            'link_url' => ['title' => '查看更多链接', 'type' => 'link', 'required' => false, 'max_length' => 500],
            'pc_visible' => ['title' => 'PC 显示', 'type' => 'boolean', 'default' => 1],
            'mobile_visible' => ['title' => '移动端显示', 'type' => 'boolean', 'default' => 1],
        ];
    }

    protected static function homeListFields($contentType, $pcCount, $mobileCount)
    {
        $fields = self::listFields($contentType);
        $fields['display_count']['title'] = '公共显示数量';
        $fields['display_count']['default'] = max((int)$pcCount, (int)$mobileCount);
        $fields['pc_display_count'] = [
            'title' => 'PC 显示数量', 'type' => 'integer', 'required' => true,
            'min' => 1, 'max' => 100, 'default' => (int)$pcCount,
        ];
        $fields['mobile_display_count'] = [
            'title' => '移动端显示数量', 'type' => 'integer', 'required' => true,
            'min' => 1, 'max' => 100, 'default' => (int)$mobileCount,
        ];
        return $fields;
    }

    protected static function productPagedListFields()
    {
        return [
            'page_size' => ['title' => '公共每页数量', 'type' => 'integer', 'required' => true, 'min' => 1, 'max' => 100, 'default' => 12],
            'pc_page_size' => ['title' => 'PC 每页数量', 'type' => 'integer', 'required' => true, 'min' => 1, 'max' => 100, 'default' => 12],
            'mobile_page_size' => ['title' => '移动端每页数量', 'type' => 'integer', 'required' => true, 'min' => 1, 'max' => 100, 'default' => 10],
            'sort_mode' => ['title' => '排序方式', 'type' => 'enum', 'options' => ['weigh_desc', 'publish_time_desc'], 'default' => 'weigh_desc'],
            'show_summary' => ['title' => '显示摘要', 'type' => 'boolean', 'default' => 1],
            'summary_length' => ['title' => '摘要长度', 'type' => 'integer', 'min' => 20, 'max' => 1000, 'default' => 160],
            'pc_visible' => ['title' => 'PC 显示', 'type' => 'boolean', 'default' => 1],
            'mobile_visible' => ['title' => '移动端显示', 'type' => 'boolean', 'default' => 1],
        ];
    }

    protected static function newsPagedListFields()
    {
        return [
            'page_size' => ['title' => '公共每页数量', 'type' => 'integer', 'required' => true, 'min' => 1, 'max' => 100, 'default' => 10],
            'pc_page_size' => ['title' => 'PC 每页数量', 'type' => 'integer', 'required' => true, 'min' => 1, 'max' => 100, 'default' => 10],
            'mobile_page_size' => ['title' => '移动端每页数量', 'type' => 'integer', 'required' => true, 'min' => 1, 'max' => 100, 'default' => 10],
            'sort_mode' => ['title' => '排序方式', 'type' => 'enum', 'options' => ['is_top_desc', 'publish_time_desc'], 'default' => 'is_top_desc'],
            'show_cover' => ['title' => '显示封面', 'type' => 'boolean', 'default' => 1],
            'show_date' => ['title' => '显示日期', 'type' => 'boolean', 'default' => 0],
            'show_summary' => ['title' => '显示摘要', 'type' => 'boolean', 'default' => 1],
            'summary_length' => ['title' => '公共摘要长度', 'type' => 'integer', 'min' => 20, 'max' => 1000, 'default' => 75],
            'pc_summary_length' => ['title' => 'PC 摘要长度', 'type' => 'integer', 'min' => 20, 'max' => 1000, 'default' => 75],
            'mobile_summary_length' => ['title' => '移动端摘要长度', 'type' => 'integer', 'min' => 20, 'max' => 1000, 'default' => 28],
            'pc_visible' => ['title' => 'PC 显示', 'type' => 'boolean', 'default' => 1],
            'mobile_visible' => ['title' => '移动端显示', 'type' => 'boolean', 'default' => 1],
        ];
    }

    protected static function newsDetailHeaderFields()
    {
        return [
            'title' => ['title' => '功能块标题', 'type' => 'text', 'required' => false, 'max_length' => 150],
            'show_source' => ['title' => '显示来源', 'type' => 'boolean', 'default' => 1],
            'show_date' => ['title' => '显示发布日期', 'type' => 'boolean', 'default' => 1],
            'show_tags' => ['title' => '显示本文标签', 'type' => 'boolean', 'default' => 1],
            'show_editor' => ['title' => '显示责任编辑', 'type' => 'boolean', 'default' => 1],
            'pc_visible' => ['title' => 'PC 显示', 'type' => 'boolean', 'default' => 1],
            'mobile_visible' => ['title' => '移动端显示', 'type' => 'boolean', 'default' => 1],
        ];
    }

    protected static function newsRelatedFields()
    {
        return [
            'title' => ['title' => '功能块标题', 'type' => 'text', 'required' => false, 'max_length' => 150],
            'subtitle' => ['title' => '副标题', 'type' => 'text', 'required' => false, 'max_length' => 255],
            'source_mode' => ['title' => '数据来源', 'type' => 'enum', 'options' => ['manual', 'auto'], 'default' => 'auto'],
            'content_type' => ['title' => '内容类型', 'type' => 'enum', 'options' => ['article'], 'default' => 'article'],
            'category_id' => ['title' => '新闻分类', 'type' => 'integer', 'required' => false, 'min' => 0, 'default' => 0],
            'display_count' => ['title' => '公共显示数量', 'type' => 'integer', 'required' => true, 'min' => 1, 'max' => 100, 'default' => 4],
            'pc_display_count' => ['title' => 'PC 显示数量', 'type' => 'integer', 'required' => true, 'min' => 1, 'max' => 100, 'default' => 2],
            'mobile_display_count' => ['title' => '移动端显示数量', 'type' => 'integer', 'required' => true, 'min' => 1, 'max' => 100, 'default' => 4],
            'sort_mode' => ['title' => '排序方式', 'type' => 'enum', 'options' => ['manual', 'is_top_desc', 'publish_time_desc'], 'default' => 'is_top_desc'],
            'button_text' => ['title' => '查看更多文字', 'type' => 'text', 'required' => false, 'max_length' => 100],
            'link_url' => ['title' => '查看更多链接', 'type' => 'link', 'required' => false, 'max_length' => 500],
            'pc_visible' => ['title' => 'PC 显示', 'type' => 'boolean', 'default' => 1],
            'mobile_visible' => ['title' => '移动端显示', 'type' => 'boolean', 'default' => 1],
        ];
    }


    protected static function navigationFields()
    {
        return [
            'max_depth' => ['title' => '最大层级', 'type' => 'integer', 'required' => true, 'min' => 1, 'max' => 3, 'default' => 2],
            'pc_visible' => ['title' => 'PC 显示', 'type' => 'boolean', 'default' => 1],
            'mobile_visible' => ['title' => '移动端显示', 'type' => 'boolean', 'default' => 1],
        ];
    }

    protected static function paginationFields()
    {
        return [
            'preserve_query' => ['title' => '保留查询参数', 'type' => 'boolean', 'default' => 1],
            'pc_visible' => ['title' => 'PC 显示', 'type' => 'boolean', 'default' => 1],
            'mobile_visible' => ['title' => '移动端显示', 'type' => 'boolean', 'default' => 1],
        ];
    }

    /**
     * 新闻总列表后台只开放真正影响 /news 前端的配置。
     *
     * 分类导航直接来自已发布新闻分类树，分页直接由当前页、总数和终端 page size
     * 自动生成，因此不再把 category_navigation / pagination 暴露成“可配置”功能块。
     */
    protected static function newsIndexPageBlocks()
    {
        $blocks = self::newsListPageBlocks();
        unset($blocks['category_navigation'], $blocks['pagination']);

        // 这些字段保留在兼容 Schema 中用于读取历史 config_json，但当前 /news
        // 前端并不消费，后台不再展示“可填但不生效”的输入项。
        $blocks['list']['admin_hidden_fields'] = [
            'page_size',
            'show_date',
            'summary_length',
            'pc_visible',
            'mobile_visible',
            'enabled',
        ];
        return $blocks;
    }

    protected static function newsListPageBlocks()
    {
        return [
            'banner' => self::blockDefinition('新闻栏目 Banner', 'banner', false, self::bannerFields(), 'page'),
            'category_navigation' => self::blockDefinition('新闻分类导航', 'navigation', false, self::navigationFields(), 'article_category'),
            'list' => self::blockDefinition('新闻列表', 'business_list', true, self::newsPagedListFields(), 'article'),
            'pagination' => self::blockDefinition('新闻分页', 'pagination', true, self::paginationFields(), 'query'),
        ];
    }

    protected static function newsDetailPageBlocks()
    {
        return [
            'banner' => self::blockDefinition('新闻详情 Banner', 'banner', false, self::bannerFields(), 'page'),
            'header' => self::blockDefinition('新闻标题信息', 'detail_header', true, self::newsDetailHeaderFields(), 'article'),
            'content' => self::blockDefinition('新闻正文', 'rich_text', true, self::visibilityFields(true), 'article'),
            'prev_next' => self::blockDefinition('上一篇/下一篇', 'prev_next', false, self::visibilityFields(false), 'article'),
            'related' => self::blockDefinition('相关新闻', 'business_list', false, self::newsRelatedFields(), 'article'),
        ];
    }

    protected static function aboutPage()
    {
        return self::pageDefinition('走进金亚', 'fixed_page', '/page/about', [
            'content' => self::blockDefinition('关于我们正文', 'rich_text', true, self::pageContentFields(true), 'page:about'),
        ]);
    }

    protected static function contactPage()
    {
        return self::pageDefinition('联系我们', 'fixed_page', '/page/contact', [
            'content' => self::blockDefinition('联系我们正文', 'rich_text', true, self::pageContentFields(false), 'page:contact'),
            'contact_info' => self::blockDefinition('联系方式', 'contact_info', false, self::contactInfoFields(), 'global'),
            'inquiry' => self::blockDefinition('在线咨询', 'inquiry', false, self::inquiryFields(), 'global'),
        ]);
    }

    protected static function pageContentFields($includeCover)
    {
        $fields = [
            'show_title' => ['title' => '显示标题', 'type' => 'boolean', 'default' => 1],
            'show_summary' => ['title' => '显示摘要', 'type' => 'boolean', 'default' => 1],
        ];
        if ($includeCover) {
            $fields['show_cover'] = ['title' => '显示封面', 'type' => 'boolean', 'default' => 1];
        }
        return $fields;
    }

    protected static function contactInfoFields()
    {
        return [
            'title' => ['title' => '联系方式标题', 'type' => 'text', 'required' => false, 'max_length' => 150, 'default' => ''],
            'show_company' => ['title' => '显示公司名称', 'type' => 'boolean', 'default' => 1],
            'show_phone' => ['title' => '显示服务热线', 'type' => 'boolean', 'default' => 1],
            'show_mobile' => ['title' => '显示联系电话', 'type' => 'boolean', 'default' => 1],
            'show_email' => ['title' => '显示联系邮箱', 'type' => 'boolean', 'default' => 1],
            'show_address' => ['title' => '显示公司地址', 'type' => 'boolean', 'default' => 1],
        ];
    }

    protected static function inquiryFields()
    {
        return [
            'title' => ['title' => '咨询标题', 'type' => 'text', 'required' => false, 'max_length' => 150, 'default' => ''],
            'subtitle' => ['title' => '咨询说明', 'type' => 'textarea', 'required' => false, 'max_length' => 255, 'default' => ''],
            'button_text' => ['title' => '提交按钮文字', 'type' => 'text', 'required' => false, 'max_length' => 100, 'default' => ''],
        ];
    }

    protected static function searchFormFields()
    {
        return [
            'title' => ['title' => '搜索页标题', 'type' => 'text', 'required' => false, 'max_length' => 150, 'default' => '站内搜索'],
            'placeholder' => ['title' => '输入框提示', 'type' => 'text', 'required' => false, 'max_length' => 150, 'default' => '请输入您要搜索的关键词'],
            'button_text' => ['title' => '搜索按钮文字', 'type' => 'text', 'required' => false, 'max_length' => 50, 'default' => '搜索'],
        ];
    }

    protected static function searchResultFields()
    {
        return [
            'page_size' => ['title' => '每页数量', 'type' => 'integer', 'required' => true, 'min' => 1, 'max' => 50, 'default' => 10],
            'show_product' => ['title' => '显示产品结果', 'type' => 'boolean', 'default' => 1],
            'show_article' => ['title' => '显示新闻结果', 'type' => 'boolean', 'default' => 1],
            'show_page' => ['title' => '显示页面结果', 'type' => 'boolean', 'default' => 1],
            'show_summary' => ['title' => '显示摘要', 'type' => 'boolean', 'default' => 1],
            'summary_length' => ['title' => '摘要长度', 'type' => 'integer', 'required' => true, 'min' => 20, 'max' => 500, 'default' => 160],
            'empty_text' => ['title' => '无结果提示', 'type' => 'text', 'required' => false, 'max_length' => 200, 'default' => '没有找到匹配内容。'],
        ];
    }

    protected static function sitemapFields()
    {
        return [
            'show_products' => ['title' => '显示产品', 'type' => 'boolean', 'default' => 1],
            'show_articles' => ['title' => '显示新闻', 'type' => 'boolean', 'default' => 1],
            'show_pages' => ['title' => '显示企业页面', 'type' => 'boolean', 'default' => 1],
            'product_title' => ['title' => '产品区标题', 'type' => 'text', 'required' => false, 'max_length' => 100, 'default' => '产品中心'],
            'article_title' => ['title' => '新闻区标题', 'type' => 'text', 'required' => false, 'max_length' => 100, 'default' => '新闻动态'],
            'page_title' => ['title' => '企业页面标题', 'type' => 'text', 'required' => false, 'max_length' => 100, 'default' => '企业页面'],
        ];
    }

    protected static function errorFields()
    {
        return [
            'title' => ['title' => '错误标题', 'type' => 'text', 'required' => true, 'max_length' => 150, 'default' => '页面不存在'],
            'subtitle' => ['title' => '错误说明', 'type' => 'textarea', 'required' => false, 'max_length' => 500, 'default' => '您访问的页面不存在或已被移动。'],
            'button_text' => ['title' => '按钮文字', 'type' => 'text', 'required' => true, 'max_length' => 100, 'default' => '返回首页'],
            'link_url' => ['title' => '按钮链接', 'type' => 'link', 'required' => true, 'max_length' => 500, 'default' => '/'],
        ];
    }

}

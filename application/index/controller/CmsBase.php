<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\service\cms\InstallerService;
use app\common\service\cms\LayoutSchemaRegistry;
use app\common\service\cms\PageConfigService;
use app\common\service\cms\PageSeoResolver;
use app\common\service\cms\PcNavigationPathService;
use app\common\service\cms\render\RenderContext;
use app\common\service\cms\render\RenderServiceFactory;
use app\common\viewmodel\cms\PageViewModel;
use think\Cache;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\Response;

abstract class CmsBase extends Frontend
{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';
    protected $pcThemeCss = 'index';
    protected $pcSection = 'home';
    protected $pcBodyClass = '';
    protected $pcStrictBundle = '67a40dc5e4b056b7c69412ce';
    protected $pcStrictHome = true;
    protected $currentPageConfig = [];
    protected $renderServiceFactory;
    protected $renderLayout = [];

    public function _initialize()
    {
        parent::_initialize();

        if (!(new InstallerService())->isInstalled()) {
            $html = $this->view->fetch('cms/error/not_installed');
            throw new HttpResponseException(Response::create($html, 'html', 503));
        }

        $this->renderLayout = $this->renderServices()->layout()->render($this->renderContext());
        $site = isset($this->renderLayout['site']) ? $this->renderLayout['site'] : [];
        $navigationTree = isset($this->renderLayout['navigation']['header']) ? $this->renderLayout['navigation']['header'] : [];
        $headerConfig = isset($this->renderLayout['header']['config']) && is_array($this->renderLayout['header']['config'])
            ? $this->renderLayout['header']['config'] : [];
        $headerLayoutMode = isset($headerConfig['layout_mode']) ? (string)$headerConfig['layout_mode'] : 'default';
        if (!in_array($headerLayoutMode, ['default', 'balanced', 'compact'], true)) {
            $headerLayoutMode = 'default';
        }
        $navActiveBackgroundColor = $this->safeHeaderColor(
            LayoutSchemaRegistry::sanitizeField('header', 'nav_active_background_color', isset($headerConfig['nav_active_background_color']) ? $headerConfig['nav_active_background_color'] : '#f48101'),
            '#f48101'
        );
        $hotlineTopWidth = min(300, (int)LayoutSchemaRegistry::sanitizeField('header', 'hotline_top_width', isset($headerConfig['hotline_top_width']) ? $headerConfig['hotline_top_width'] : 300));
        $hotlineScrolledWidth = LayoutSchemaRegistry::sanitizeField('header', 'hotline_scrolled_width', isset($headerConfig['hotline_scrolled_width']) ? $headerConfig['hotline_scrolled_width'] : 320);
        $hotlineTopOffsetY = LayoutSchemaRegistry::sanitizeField('header', 'hotline_top_offset_y', isset($headerConfig['hotline_top_offset_y']) ? $headerConfig['hotline_top_offset_y'] : 0);
        $hotlineScrolledOffsetY = LayoutSchemaRegistry::sanitizeField('header', 'hotline_scrolled_offset_y', isset($headerConfig['hotline_scrolled_offset_y']) ? $headerConfig['hotline_scrolled_offset_y'] : 0);
        $hotlineBadgeTopFontSize = LayoutSchemaRegistry::sanitizeField('header', 'hotline_badge_top_font_size', isset($headerConfig['hotline_badge_top_font_size']) ? $headerConfig['hotline_badge_top_font_size'] : 16);
        $hotlineBadgeScrolledFontSize = LayoutSchemaRegistry::sanitizeField('header', 'hotline_badge_scrolled_font_size', isset($headerConfig['hotline_badge_scrolled_font_size']) ? $headerConfig['hotline_badge_scrolled_font_size'] : 13);
        $hotlineIconTopSize = LayoutSchemaRegistry::sanitizeField('header', 'hotline_icon_top_size', isset($headerConfig['hotline_icon_top_size']) ? $headerConfig['hotline_icon_top_size'] : 49);
        $hotlineIconScrolledSize = LayoutSchemaRegistry::sanitizeField('header', 'hotline_icon_scrolled_size', isset($headerConfig['hotline_icon_scrolled_size']) ? $headerConfig['hotline_icon_scrolled_size'] : 39);
        $hotlineNumberTopFontSize = LayoutSchemaRegistry::sanitizeField('header', 'hotline_number_top_font_size', isset($headerConfig['hotline_number_top_font_size']) ? $headerConfig['hotline_number_top_font_size'] : 34);
        $hotlineNumberScrolledFontSize = LayoutSchemaRegistry::sanitizeField('header', 'hotline_number_scrolled_font_size', isset($headerConfig['hotline_number_scrolled_font_size']) ? $headerConfig['hotline_number_scrolled_font_size'] : 26);
        $hotlineNumberFontWeight = LayoutSchemaRegistry::sanitizeField('header', 'hotline_number_font_weight', isset($headerConfig['hotline_number_font_weight']) ? $headerConfig['hotline_number_font_weight'] : '700');
        $hotlineBackgroundColor = $this->safeHeaderColor(isset($headerConfig['hotline_background_color']) ? $headerConfig['hotline_background_color'] : '#ef202d', '#ef202d');
        $hotlineNumberColor = $this->safeHeaderColor(isset($headerConfig['hotline_number_color']) ? $headerConfig['hotline_number_color'] : '#ffd65a', '#ffd65a');
        $hotlineBadgeBackgroundColor = $this->safeHeaderColor(isset($headerConfig['hotline_badge_background_color']) ? $headerConfig['hotline_badge_background_color'] : '#ffb918', '#ffb918');
        $hotlineBadgeTextColor = $this->safeHeaderColor(isset($headerConfig['hotline_badge_text_color']) ? $headerConfig['hotline_badge_text_color'] : '#333333', '#333333');
        $logoTopWidth = LayoutSchemaRegistry::sanitizeField('header', 'logo_top_width', isset($headerConfig['logo_top_width']) ? $headerConfig['logo_top_width'] : 212);
        $logoScrolledWidth = LayoutSchemaRegistry::sanitizeField('header', 'logo_scrolled_width', isset($headerConfig['logo_scrolled_width']) ? $headerConfig['logo_scrolled_width'] : 180);
        $this->view->assign('layout', $this->renderLayout);
        $this->view->assign('cmsPcHeaderLayoutMode', $headerLayoutMode);
        $this->view->assign('cmsPcNavActiveBackgroundColor', $navActiveBackgroundColor);
        // strict PC Header 以 1903px 宽、20px 根字号为设计基准；后台填写设计像素，前台换算为 rem 保持既有响应式比例。
        $this->view->assign('cmsPcHotlineTopWidthRem', $hotlineTopWidth / 20);
        $this->view->assign('cmsPcHotlineScrolledWidthRem', $hotlineScrolledWidth / 20);
        $this->view->assign('cmsPcHotlineTopOffsetYRem', $hotlineTopOffsetY / 20);
        $this->view->assign('cmsPcHotlineScrolledOffsetYRem', $hotlineScrolledOffsetY / 20);
        $this->view->assign('cmsPcHotlineBadgeTopFontSizeRem', $hotlineBadgeTopFontSize / 20);
        $this->view->assign('cmsPcHotlineBadgeScrolledFontSizeRem', $hotlineBadgeScrolledFontSize / 20);
        $this->view->assign('cmsPcHotlineIconTopSizeRem', $hotlineIconTopSize / 20);
        $this->view->assign('cmsPcHotlineIconScrolledSizeRem', $hotlineIconScrolledSize / 20);
        $this->view->assign('cmsPcHotlineNumberTopFontSizeRem', $hotlineNumberTopFontSize / 20);
        $this->view->assign('cmsPcHotlineNumberScrolledFontSizeRem', $hotlineNumberScrolledFontSize / 20);
        $this->view->assign('cmsPcHotlineNumberFontWeight', $hotlineNumberFontWeight);
        $this->view->assign('cmsPcHotlineBackgroundColor', $hotlineBackgroundColor);
        $this->view->assign('cmsPcHotlineNumberColor', $hotlineNumberColor);
        $this->view->assign('cmsPcHotlineBadgeBackgroundColor', $hotlineBadgeBackgroundColor);
        $this->view->assign('cmsPcHotlineBadgeTextColor', $hotlineBadgeTextColor);
        $this->view->assign('cmsPcLogoTopWidthRem', $logoTopWidth / 20);
        $this->view->assign('cmsPcLogoScrolledWidthRem', $logoScrolledWidth / 20);
        $this->view->assign('cmsNavigation', $navigationTree);
        $this->view->assign('cmsNavigationTree', $navigationTree);
        $this->view->assign('cmsSite', $site);
        $this->view->assign('cmsInquiryToken', $this->request->token());
        foreach (['wechat', 'douyin', 'kuaishou', 'xiaohongshu', 'video', 'bilibili'] as $qrName) {
            $key = $qrName . '_qr';
            $this->view->assign('cms' . ucfirst($qrName) . 'Qr', isset($site[$key]) ? $site[$key] : '');
        }
        $this->view->assign('cmsMobileUrl', $this->buildViewModeUrl('mobile'));
        $navigationPath = new PcNavigationPathService();
        $this->view->assign('cmsCurrentPath', $navigationPath->normalize($this->requestPath()));
        $this->view->assign('pcAssetBase', '/assets/kcm-pc-strict');
        $this->view->assign('pcThemeCss', $this->pcThemeCss);
        $this->view->assign('pcSection', $this->pcSection);
        $this->view->assign('pcBodyClass', $this->pcBodyClass);
        $this->view->assign('pcStrictBundle', $this->pcStrictBundle);
        $this->view->assign('pcStrictHome', $this->pcStrictHome);
        $this->view->assign('channelBanner', '');
        $this->view->assign('channelBannerTitle', '');
        $this->view->assign('channelBannerSubtitle', '');
        $this->view->assign('channelTitle', '');
        $this->view->assign('channelUrl', '');
        $this->view->assign('breadcrumbCurrent', '');
        $this->view->assign('breadcrumbCategory', '');
        $this->view->assign('breadcrumbCategoryUrl', '');
        $this->view->assign('strictPage', []);
        $this->view->assign('pageConfig', []);
        $this->view->assign('channelBannerVisible', true);
        $this->view->assign('productBlockVisibility', []);
    }

    protected function setPcTheme($theme, $section = '', $bodyClass = 'body-color')
    {
        $theme = preg_replace('/[^a-z0-9\-]/i', '', (string)$theme);
        $this->pcThemeCss = $theme ?: 'index';
        $this->pcSection = $section !== '' ? (string)$section : $this->pcSection;
        $this->pcBodyClass = (string)$bodyClass;

        $bundleMap = [
            'index' => '67a40dc5e4b056b7c69412ce',
            'product-list' => '67a71e82e4b0f9d0b802554b',
            'product-category' => '67a71e82e4b0f9d0b802554c',
            'product-detail' => '67a71e84e4b0f9d0b8025557',
            'article-detail' => '67a71e88e4b0f9d0b8025579',
            'about' => '67a71e89e4b0f9d0b8025587',
            'label-page' => '67a71e89e4b0f9d0b8025587',
            'bags-page' => '67a71e89e4b0f9d0b8025587',
            'boxes-page' => '67a71e89e4b0f9d0b8025587',
            'search' => '67a71e8be4b0f9d0b802559a',
            'sitemap' => '67a71e8ae4b0f9d0b8025591',
            'article-list' => '67b692bce4b0d0e791e075ec',
        ];        $this->pcStrictBundle = isset($bundleMap[$this->pcThemeCss])
            ? $bundleMap[$this->pcThemeCss]
            : $bundleMap['about'];
        $this->pcStrictHome = $this->pcThemeCss === 'index';

        $bannerKey = $this->channelBannerConfigKey();
        $channelBanner = (string)config('site.' . $bannerKey, '');
        if (!$this->channelBannerAssetExists($channelBanner)) {
            $channelBanner = '';
        }

        $this->view->assign('pcThemeCss', $this->pcThemeCss);
        $this->view->assign('pcSection', $this->pcSection);
        $this->view->assign('pcBodyClass', $this->pcBodyClass);
        $this->view->assign('pcStrictBundle', $this->pcStrictBundle);
        $this->view->assign('pcStrictHome', $this->pcStrictHome);
        $navigationPath = new PcNavigationPathService();
        $this->view->assign('cmsCurrentPath', $navigationPath->resolve($this->requestPath(), $this->pcSection));
        $this->view->assign('channelBanner', $channelBanner);
    }

    protected function assignChannel($title, $breadcrumb = '')
    {
        $this->view->assign('channelTitle', (string)$title);
        $this->view->assign('breadcrumbCurrent', $breadcrumb !== '' ? (string)$breadcrumb : (string)$title);
    }

    /**
     * 将 ThinkPHP 分页器转换为目标站原有的纯 a 标签分页结构。
     *
     * @param mixed $paginator
     * @return string
     */
    protected function assignPcPagination($paginator, $routePath = '')
    {
        $current = max(1, (int)$paginator->currentPage());
        $last = max(1, (int)$paginator->lastPage());
        $query = $this->request->get();
        unset($query['page']);
        $path = trim((string)$routePath) !== '' ? (string)$routePath : $this->requestPath();
        $url = function ($page) use ($query, $path) {
            $params = $query;
            if ((int)$page > 1) {
                $params['page'] = (int)$page;
            }
            $queryString = http_build_query($params);
            $target = $path . ($queryString !== '' ? '?' . $queryString : '');
            return htmlspecialchars($target, ENT_QUOTES, 'UTF-8');
        };

        $html = '';
        if ($current > 1) {
            $html .= '<a class="page_first" href="' . $url(1) . '">首页</a>';
            $html .= '<a class="page_pre" href="' . $url($current - 1) . '">上一页</a>';
        }
        $start = max(1, $current - 2);
        $end = min($last, $start + 4);
        $start = max(1, $end - 4);
        for ($page = $start; $page <= $end; $page++) {
            if ($page === $current) {
                $html .= '<a class="page_curr">' . $page . '</a>';
            } else {
                $html .= '<a href="' . $url($page) . '">' . $page . '</a>';
            }
        }
        if ($current < $last) {
            $html .= '<a class="page_next" href="' . $url($current + 1) . '">下一页</a>';
            $html .= '<a class="page_last" href="' . $url($last) . '">末页</a>';
        }
        $this->view->assign('pcPagination', $html);
        return $html;
    }

    /**
     * 读取固定页面配置并提供给严格克隆模板。
     * 模板是否使用某个字段由后续逐页接入决定，本方法不改变 DOM。
     */
    protected function assignPageConfig($pageKey)
    {
        $device = 'pc';
        $cacheKey = 'cms:page-config:' . $pageKey . ':' . $device;
        $pageConfig = Cache::get($cacheKey);
        if (!is_array($pageConfig)) {
            $pageConfig = (new PageConfigService())->resolvePage($pageKey, $device);
            Cache::set($cacheKey, $pageConfig, 3600);
        }
        $this->currentPageConfig = $pageConfig;
        $this->view->assign('pageConfig', $pageConfig);
        return $pageConfig;
    }

    protected function redirectOr404()
    {

        $viewModel = $this->renderServices()->error()->render(404, $this->renderContext());
        $this->setPcTheme('about', 'page');
        $this->assignPageViewModel($viewModel);
        $this->applyChannelFromViewModel($viewModel, '页面不存在', '');
        $html = $this->view->fetch('cms/error/404');
        throw new HttpResponseException(Response::create($html, 'html', 404));
    }

    protected function assignSeo($row = null, $fallbackTitle = '')
    {
        $canonicalFallback = rtrim($this->request->domain(), '/') . $this->requestPath();
        $seo = (new PageSeoResolver())->resolve($row, $this->currentPageConfig, $fallbackTitle, $canonicalFallback);
        $this->view->assign($seo);
    }

    protected function assignStrictPageSeo(array $strictPage)
    {
        if (empty($strictPage)) {
            return;
        }

        $seo = [];
        foreach (['title', 'keywords', 'description'] as $name) {
            $source = 'seo_' . $name;
            if (!empty($strictPage[$source])) {
                $seo[$name] = (string)$strictPage[$source];
            }
        }
        if ($seo) {
            $this->view->assign($seo);
        }
    }

    private function requestPath()
    {
        $path = parse_url($this->request->server('REQUEST_URI', '/'), PHP_URL_PATH);
        return $path ?: '/';
    }

    private function safeHeaderColor($value, $fallback)
    {
        $value = trim((string)$value);
        return preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? strtolower($value) : $fallback;
    }

    private function buildViewModeUrl($mode)
    {
        $path = $this->requestPath();
        $query = $this->request->get();
        unset($query['view']);
        $query['view'] = $mode;
        $queryString = http_build_query($query);
        return $path . ($queryString ? '?' . $queryString : '');
    }
    protected function renderContext($page = 1)
    {
        return RenderContext::pc($page);
    }

    protected function renderServices()
    {
        if (!$this->renderServiceFactory) {
            $this->renderServiceFactory = new RenderServiceFactory();
        }
        return $this->renderServiceFactory;
    }

    protected function assignPageViewModel(PageViewModel $viewModel)
    {
        $data = $viewModel->toArray();
        foreach ($data as $key => $value) {
            $this->view->assign($key, $value);
        }
        if (!empty($data['seo'])) {
            $this->view->assign($data['seo']);
        }
        if (!empty($data['layout'])) {
            $this->renderLayout = $data['layout'];
            $site = isset($data['layout']['site']) ? $data['layout']['site'] : [];
            $navigation = isset($data['layout']['navigation']['header']) ? $data['layout']['navigation']['header'] : [];
            $this->view->assign('cmsSite', $site);
            $this->view->assign('cmsNavigation', $navigation);
            $this->view->assign('cmsNavigationTree', $navigation);
        }
        // 兼容尚未迁移的严格模板；数据仍然来自 ViewModel。
        if (!empty($data['content']) && is_array($data['content'])) {
            foreach ($data['content'] as $key => $value) {
                $this->view->assign($key, $value);
            }
        }
        return $data;
    }

    protected function applyChannelFromViewModel(PageViewModel $viewModel, $fallbackTitle = '', $fallbackUrl = '')
    {
        $data = $viewModel->toArray();
        $banner = !empty($data['banner'][0]) ? $data['banner'][0] : [];
        $breadcrumb = !empty($data['breadcrumb']) ? $data['breadcrumb'] : [];
        $current = $breadcrumb ? end($breadcrumb) : [];
        $channelBanner = isset($banner['image']) ? (string)$banner['image'] : '';
        if ($channelBanner === '') {
            $bannerKey = $this->channelBannerConfigKey();
            $channelBanner = (string)config('site.' . $bannerKey, '');
        }
        if (!$this->channelBannerAssetExists($channelBanner)) {
            $channelBanner = '';
        }
        $this->view->assign('channelBanner', $channelBanner);
        $this->view->assign('channelBannerTitle', isset($banner['title']) ? trim((string)$banner['title']) : '');
        $this->view->assign('channelBannerSubtitle', isset($banner['subtitle']) ? trim((string)$banner['subtitle']) : '');
        $this->view->assign('channelBannerVisible', $channelBanner !== '' || $this->pcSection !== '');
        $this->view->assign('channelTitle', isset($current['title']) && $current['title'] !== '' ? $current['title'] : (string)$fallbackTitle);
        $this->view->assign('channelUrl', (string)$fallbackUrl);
        $this->view->assign('breadcrumbCurrent', isset($current['title']) ? $current['title'] : (string)$fallbackTitle);
        return $data;
    }

    protected function channelBannerConfigKey()
    {
        if ($this->pcSection === 'products') {
            return 'cms_pc_product_banner';
        }
        if ($this->pcSection === 'news') {
            return 'cms_pc_news_banner';
        }
        return 'cms_pc_about_banner';
    }

    protected function channelBannerAssetExists($url)
    {
        $url = trim((string)$url);
        if ($url === '') {
            return false;
        }
        if (preg_match('#^(?:https?:)?//#i', $url)) {
            return true;
        }
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || $path === '' || $path[0] !== '/') {
            return true;
        }
        $file = rtrim(ROOT_PATH, '/\\') . DIRECTORY_SEPARATOR . 'public'
            . str_replace('/', DIRECTORY_SEPARATOR, $path);
        return is_file($file);
    }

}

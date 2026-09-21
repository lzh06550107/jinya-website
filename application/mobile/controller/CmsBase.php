<?php

namespace app\mobile\controller;

use app\common\controller\Frontend;
use app\common\service\cms\InstallerService;
use app\common\service\cms\PageConfigService;
use app\common\service\cms\PageSeoResolver;
use think\Cache;
use think\Cookie;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\Response;
use app\common\service\cms\render\RenderContext;
use app\common\service\cms\render\RenderServiceFactory;
use app\common\viewmodel\cms\PageViewModel;

abstract class CmsBase extends Frontend
{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';
    protected $mobileBase = '';
    protected $desktopPath = '/';
    protected $mobileThemeCss = 'home';
    protected $mobileStrictAssetBase = '/assets/kcm-mobile-strict';
    protected $mobileStrictBundle = '67a40dc6e4b056b7c69413b0';
    protected $mobileStrictHome = true;
    protected $mobileStrictUsesSwiper = true;
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

        $requestPath = $this->requestPath();
        $explicitMobile = (bool)preg_match('#^/mobile(?:/|$)#i', $requestPath);
        $this->mobileBase = $explicitMobile ? '/mobile' : '';
        if ($explicitMobile) {
            Cookie::set('cms_view', 'mobile', ['expire' => 2592000, 'path' => '/', 'httponly' => true]);
        }
        $this->desktopPath = $explicitMobile ? $this->stripMobilePrefix($requestPath) : $requestPath;

        $this->renderLayout = $this->renderServices()->layout()->render($this->renderContext());
        $navigation = $this->prepareNavigation(isset($this->renderLayout['navigation']['header']) ? $this->renderLayout['navigation']['header'] : []);
        $this->view->assign('layout', $this->renderLayout);
        $this->view->assign('cmsNavigation', $navigation);
        $this->view->assign('cmsSite', isset($this->renderLayout['site']) ? $this->renderLayout['site'] : []);
        $this->view->assign('cmsInquiryToken', $this->request->token());
        $this->view->assign('mobileBase', $this->mobileBase);
        $this->view->assign('mobileHomeUrl', $this->mobileBase ? '/mobile' : '/');
        $this->view->assign('desktopUrl', $this->buildModeUrl($this->desktopPath, 'desktop'));
        $this->view->assign('cmsCurrentPath', $requestPath);
        $this->view->assign('mobileThemeCss', $this->mobileThemeCss);
        $this->view->assign('mobileAssetBase', '/assets/kcm-mobile');
        $this->view->assign('mobileStrictAssetBase', $this->mobileStrictAssetBase);
        $this->view->assign('mobileStrictBundle', $this->mobileStrictBundle);
        $this->view->assign('mobileStrictHome', $this->mobileStrictHome);
        $this->view->assign('mobileStrictUsesSwiper', $this->mobileStrictUsesSwiper);
        $this->view->assign('mobileChannelBanner', '');
        $this->view->assign('pageConfig', []);
        $this->view->assign('channelBannerVisible', true);
        $this->view->assign('productBlockVisibility', []);
    }

    /**
     * 读取固定页面配置并提供给严格克隆模板。
     * 模板是否使用某个字段由后续逐页接入决定，本方法不改变 DOM。
     */
    protected function assignPageConfig($pageKey)
    {
        $device = 'mobile';
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
        $data = $this->assignPageViewModel($viewModel);
        if ($this->mobileBase && isset($data['content']['link_url']) && $data['content']['link_url'] === '/') {
            $data['content']['link_url'] = '/mobile';
            $this->view->assign('content', $data['content']);
            foreach ($data['content'] as $key => $value) {
                $this->view->assign($key, $value);
            }
        }
        $this->setMobileTheme('about');
        $this->setMobileSection('page');
        $this->applyMobileChannelFromViewModel($viewModel, 'page');
        $html = $this->view->fetch('cms/error/404');
        throw new HttpResponseException(Response::create($html, 'html', 404));
    }

    protected function assignSeo($row = null, $fallbackTitle = '')
    {
        $canonicalFallback = rtrim($this->request->domain(), '/') . $this->desktopPath;
        $seo = (new PageSeoResolver())->resolve($row, $this->currentPageConfig, $fallbackTitle, $canonicalFallback);
        $this->view->assign($seo);
    }

    protected function mobilePath($path)
    {
        $path = '/' . ltrim((string)$path, '/');
        if (!$this->mobileBase) {
            return $path;
        }
        if ($path === '/') {
            return '/mobile';
        }
        return '/mobile' . $path;
    }


    protected function setMobileTheme($theme)
    {
        $theme = preg_replace('/[^a-z0-9\-]/i', '', (string)$theme);
        $this->mobileThemeCss = $theme ?: 'home';
        $bundles = [
            'home' => ['67a40dc6e4b056b7c69413b0', true, true],
            'product-list' => ['67b5b06ee4b0e99c463aee29', false, false],
            'product-category' => ['67b5b072e4b0e99c463aee31', false, false],
            'product-detail' => ['67b5b074e4b0e99c463aee39', false, true],
            'article-list' => ['67b5b076e4b0e99c463aee3f', false, false],
            'article-detail' => ['67b5b076e4b0e99c463aee40', false, false],
            'about' => ['67b5b07be4b0e99c463aee4e', false, false],
            'label-page' => ['67b5b07be4b0e99c463aee4e', false, false],
            'bags-page' => ['67b5b07be4b0e99c463aee4e', false, false],
            'boxes-page' => ['67b5b07be4b0e99c463aee4e', false, false],
            'search' => ['67b5b07ce4b0e99c463aee56', false, true],
        ];        $selected = isset($bundles[$this->mobileThemeCss]) ? $bundles[$this->mobileThemeCss] : $bundles['about'];
        $this->mobileStrictBundle = $selected[0];
        $this->mobileStrictHome = $selected[1];
        $this->mobileStrictUsesSwiper = $selected[2];
        $this->view->assign('mobileThemeCss', $this->mobileThemeCss);
        $this->view->assign('mobileStrictBundle', $this->mobileStrictBundle);
        $this->view->assign('mobileStrictHome', $this->mobileStrictHome);
        $this->view->assign('mobileStrictUsesSwiper', $this->mobileStrictUsesSwiper);
    }

    protected function assignChannel($type, $title = '')
    {
        $this->view->assign('channelType', (string)$type);
        $this->view->assign('channelTitle', (string)$title);
    }
    protected function setMobileSection($section)
    {
        $this->view->assign('mobileSection', (string)$section);
    }

    protected function toMobileUrl($url)
    {
        $url = trim((string)$url);
        if (!$this->mobileBase || $url === '' || preg_match('#^(?:https?:)?//#i', $url) || preg_match('#^(?:mailto:|tel:|javascript:|\#)#i', $url)) {
            return $url;
        }
        if (preg_match('#^/mobile(?:/|$)#i', $url)) {
            return $url;
        }
        if ($url === '/') {
            return '/mobile';
        }
        return '/mobile/' . ltrim($url, '/');
    }

    private function prepareNavigation($rows)
    {
        $result = [];
        foreach ($rows as $row) {
            $item = is_object($row) && method_exists($row, 'toArray') ? $row->toArray() : (array)$row;
            $item['url'] = $this->toMobileUrl(isset($item['url']) ? $item['url'] : '/');
            $result[] = $item;
        }
        return $result;
    }

    private function requestPath()
    {
        $path = parse_url($this->request->server('REQUEST_URI', '/'), PHP_URL_PATH);
        return $path ?: '/';
    }

    private function stripMobilePrefix($path)
    {
        if ($path === '/mobile' || $path === '/mobile/') {
            return '/';
        }
        $path = preg_replace('#^/mobile#i', '', $path);
        return $path ?: '/';
    }

    private function buildModeUrl($path, $mode)
    {
        $query = $this->request->get();
        unset($query['view']);
        $query['view'] = $mode;
        $queryString = http_build_query($query);
        return $path . ($queryString ? '?' . $queryString : '');
    }
    protected function renderContext($page = 1)
    {
        return RenderContext::mobile($page);
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
            $navigation = $this->prepareNavigation(isset($data['layout']['navigation']['header']) ? $data['layout']['navigation']['header'] : []);
            $this->view->assign('cmsSite', $site);
            $this->view->assign('cmsNavigation', $navigation);
        }
        if (!empty($data['content']) && is_array($data['content'])) {
            foreach ($data['content'] as $key => $value) {
                $this->view->assign($key, $value);
            }
        }
        return $data;
    }

    protected function applyMobileChannelFromViewModel(PageViewModel $viewModel, $section)
    {
        $data = $viewModel->toArray();
        $banner = !empty($data['banner'][0]) ? $data['banner'][0] : [];
        $breadcrumb = !empty($data['breadcrumb']) ? $data['breadcrumb'] : [];
        $current = $breadcrumb ? end($breadcrumb) : [];
        $mobileChannelBanner = isset($banner['image']) ? trim((string)$banner['image']) : '';
        $this->view->assign('mobileSection', (string)$section);
        $this->view->assign('mobileChannelBanner', $mobileChannelBanner);
        $this->view->assign('channelBannerVisible', $mobileChannelBanner !== '');
        $this->view->assign('channelTitle', isset($current['title']) ? $current['title'] : '');
        return $data;
    }

}

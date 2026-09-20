<?php
namespace app\common\service\cms\render;
use app\common\repository\cms\NavigationRepositoryInterface;
use app\common\repository\cms\LayoutComponentRepositoryInterface;
use app\common\service\cms\CmsIconValue;
use app\common\service\cms\LayoutSchemaRegistry;

class LayoutRenderService
{
    private $navigation;
    private $components;
    private $media;
    private $icons;

    public function __construct(NavigationRepositoryInterface $navigation, LayoutComponentRepositoryInterface $components, MediaUrlResolver $media, CmsIconValue $icons = null)
    {
        $this->navigation = $navigation;
        $this->components = $components;
        $this->media = $media;
        $this->icons = $icons ?: new CmsIconValue();
    }

    public function render(RenderContext $context)
    {
        $site = (array)config('site');
        $components = $this->components->publishedMap($context->terminal());
        $logo = $this->media->resolve(isset($site['cms_logo']) ? $site['cms_logo'] : '')['url'];
        $siteView = [
            'name' => isset($site['cms_company']) ? $site['cms_company'] : (isset($site['name']) ? $site['name'] : ''),
            'cms_company' => isset($site['cms_company']) ? $site['cms_company'] : (isset($site['name']) ? $site['name'] : ''),
            'slogan' => isset($site['cms_slogan']) ? $site['cms_slogan'] : '',
            'cms_slogan' => isset($site['cms_slogan']) ? $site['cms_slogan'] : '',
            'logo' => $logo,
            'cms_logo' => $logo,
            'hotline' => isset($site['cms_phone']) ? $site['cms_phone'] : '',
            'cms_phone' => isset($site['cms_phone']) ? $site['cms_phone'] : '',
            'email' => isset($site['cms_email']) ? $site['cms_email'] : '',
            'cms_email' => isset($site['cms_email']) ? $site['cms_email'] : '',
            'address' => isset($site['cms_address']) ? $site['cms_address'] : '',
            'cms_address' => isset($site['cms_address']) ? $site['cms_address'] : '',
            'wechat_qr' => isset($site['cms_wechat_qr']) ? $site['cms_wechat_qr'] : '',
            'cms_service_wecom_url' => $this->externalServiceUrl(isset($site['cms_service_wecom_url']) ? $site['cms_service_wecom_url'] : ''),
            'cms_service_wechat_qr' => isset($site['cms_service_wechat_qr']) ? $site['cms_service_wechat_qr'] : '',
            'cms_service_wechat_name' => isset($site['cms_service_wechat_name']) ? $site['cms_service_wechat_name'] : '',
            'cms_service_wechat_tip' => isset($site['cms_service_wechat_tip']) ? $site['cms_service_wechat_tip'] : '',
            'cms_service_hours' => isset($site['cms_service_hours']) ? $site['cms_service_hours'] : '',
            'douyin_qr' => isset($site['cms_douyin_qr']) ? $site['cms_douyin_qr'] : '',
            'kuaishou_qr' => isset($site['cms_kuaishou_qr']) ? $site['cms_kuaishou_qr'] : '',
            'xiaohongshu_qr' => isset($site['cms_xiaohongshu_qr']) ? $site['cms_xiaohongshu_qr'] : '',
            'video_qr' => isset($site['cms_video_qr']) ? $site['cms_video_qr'] : '',
            'bilibili_qr' => isset($site['cms_bilibili_qr']) ? $site['cms_bilibili_qr'] : '',
            'beian' => isset($site['beian']) ? $site['beian'] : '',
            'version' => isset($site['version']) ? $site['version'] : '1.0',
            'cms_copyright_year' => isset($site['cms_copyright_year']) ? $site['cms_copyright_year'] : date('Y'),
            'cms_tech_support' => isset($site['cms_tech_support']) ? $site['cms_tech_support'] : '',
        ];
        return [
            'site' => $siteView,
            'navigation' => [
                'header' => $this->navigation->publishedTree('header', $context->terminal()),
                'footer' => $this->navigation->publishedTree('footer', $context->terminal()),
            ],
            'components' => $components,
            'header' => $this->headerComponent($components, 'layout.header.' . $context->terminal()),
            'footer' => $this->component($components, 'layout.footer.' . $context->terminal()),
            'footer_company' => $this->component($components, 'layout.footer.company'),
            'footer_contact' => $this->component($components, 'layout.footer.contact'),
            'footer_qrcode' => $this->footerQrcodeComponent($components, $siteView),
            'friend_links' => $this->friendLinksComponent($components),
            'hot_search' => $this->hotSearchComponent($components),
            'floating_service' => $this->floatingServiceComponent($components, $context->terminal()),
            'mobile_toolbar' => $this->iconComponent($components, 'layout.mobile_toolbar'),
        ];
    }

    private function headerComponent(array $components, $key)
    {
        $component = $this->component($components, $key);
        $config = isset($component['config']) && is_array($component['config']) ? $component['config'] : [];
        $iconView = $this->icons->view(isset($config['hotline_icon']) ? $config['hotline_icon'] : '');
        if ($iconView['type'] === 'image' && $iconView['url'] !== '') {
            $resolved = $this->media->resolve($iconView['url']);
            $iconView['url'] = isset($resolved['url']) ? (string)$resolved['url'] : '';
        }
        $config['hotline_icon_view'] = $iconView;
        $component['config'] = $config;
        return $component;
    }

    private function externalServiceUrl($url)
    {
        $url = trim((string)$url);
        if ($url === '') {
            return '';
        }
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!$scheme || !in_array(strtolower($scheme), ['http', 'https'], true)) {
            return '';
        }
        return $url;
    }

    private function floatingServiceComponent(array $components, $terminal)
    {
        $component = $this->component($components, 'layout.floating_service.' . $terminal);
        $config = $component['config'];
        foreach (LayoutSchemaRegistry::fields('floating_service') as $name => $definition) {
            if (!array_key_exists($name, $config)) {
                $config[$name] = isset($definition['default']) ? (int)!empty($definition['default']) : 0;
            }
        }
        $component['config'] = $config;
        return $component;
    }

    private function component(array $components, $key, array $arrayConfigKeys = [])
    {
        $component = isset($components[$key]) && is_array($components[$key]) ? $components[$key] : [];
        $config = isset($component['config']) && is_array($component['config']) ? $component['config'] : [];
        foreach ($arrayConfigKeys as $configKey) {
            if (!isset($config[$configKey]) || !is_array($config[$configKey])) {
                $config[$configKey] = [];
            }
        }
        $component['config'] = $config;
        return $component;
    }
    private function footerQrcodeComponent(array $components, array $siteView)
    {
        $component = $this->component($components, 'layout.footer.qrcode', ['items']);
        $items = isset($component['config']['items']) && is_array($component['config']['items'])
            ? $component['config']['items'] : [];
        $visibleItems = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            if (isset($item['status']) && $item['status'] === 'hidden') {
                continue;
            }
            $qrKey = isset($item['qr_key']) ? trim((string)$item['qr_key']) : '';
            $item['qr_image'] = $qrKey !== '' && isset($siteView[$qrKey]) ? (string)$siteView[$qrKey] : '';
            $visibleItems[] = $item;
        }
        $component['config']['items'] = $this->decorateIconItems($visibleItems);
        return $component;
    }

    private function iconComponent(array $components, $key)
    {
        $component = $this->component($components, $key, ['items']);
        $component['config']['items'] = $this->decorateIconItems($component['config']['items']);
        return $component;
    }

    private function decorateIconItems(array $items)
    {
        return $this->icons->decorateRows($items);
    }

    private function friendLinksComponent(array $components)
    {
        $component = $this->component($components, 'layout.friend_links', ['items']);
        $items = [];
        foreach ($component['config']['items'] as $item) {
            if (!is_array($item)) {
                continue;
            }
            if (isset($item['status']) && $item['status'] === 'hidden') {
                continue;
            }
            $title = isset($item['title']) ? trim((string)$item['title']) : '';
            if ($title === '') {
                continue;
            }
            $item['title'] = $title;
            $item['url'] = isset($item['url']) ? trim((string)$item['url']) : '';
            $item['target'] = isset($item['target']) && $item['target'] === '_self' ? '_self' : '_blank';
            $items[] = $item;
        }
        $component['config']['items'] = $items;
        return $component;
    }

    private function hotSearchComponent(array $components)
    {
        $component = $this->component($components, 'layout.hot_search', ['items']);
        $config = $component['config'];
        $items = [];
        foreach ($config['items'] as $item) {
            if (!is_array($item)) {
                continue;
            }
            if (isset($item['status']) && $item['status'] === 'hidden') {
                continue;
            }
            $items[] = $item;
        }
        $maxItems = isset($config['max_items']) ? (int)$config['max_items'] : 0;
        if ($maxItems > 0) {
            $items = array_slice($items, 0, $maxItems);
        }
        $component['config']['items'] = $items;
        return $component;
    }

}

<?php

namespace app\common\service\cms\layout_editor;

/**
 * 固定公共布局组件的聚合编辑定义。
 *
 * Registry 只描述“编辑入口可以操作哪些现有数据”，不成为新的数据源。
 */
class LayoutEditorRegistry
{
    public static function keys()
    {
        return [
            'layout.header.pc',
            'layout.header.mobile',
            'layout.footer.pc',
            'layout.footer.mobile',
            'layout.hot_search',
            'layout.friend_links',
            'layout.footer.company',
            'layout.footer.contact',
            'layout.footer.qrcode',
            'layout.floating_service.pc',
        ];
    }

    public static function definition($componentKey)
    {
        $definitions = self::definitions();
        if (!isset($definitions[$componentKey])) {
            throw new \InvalidArgumentException('未注册的公共布局编辑器：' . $componentKey);
        }
        return $definitions[$componentKey];
    }

    protected static function definitions()
    {
        $footerSiteFields = [
            'cms_logo', 'cms_company', 'cms_phone', 'cms_email', 'cms_address', 'cms_factory_address', 'beian',
            'cms_copyright_year', 'cms_tech_support', 'cms_service_wechat_qr',
            'cms_wechat_qr', 'cms_douyin_qr', 'cms_kuaishou_qr', 'cms_xiaohongshu_qr', 'cms_video_qr', 'cms_bilibili_qr',
        ];
        $footerRelated = ['layout.footer.company', 'layout.footer.contact', 'layout.footer.qrcode', 'layout.friend_links'];
        $floatingServiceSiteFields = [
            'cms_service_wecom_url', 'cms_service_wechat_qr',
        ];
        $floatingServiceLayoutFields = [
            'show_online_consult', 'show_online_message', 'show_wechat_consult', 'show_back_top',
        ];

        return [
            'layout.header.pc' => [
                'key' => 'layout.header.pc',
                'tabs' => ['content', 'navigation', 'display', 'impact'],
                'site_fields' => ['cms_logo', 'cms_company', 'cms_phone'],
                'component_fields' => [],
                'layout_fields' => ['show_logo', 'show_navigation', 'show_phone', 'nav_active_background_color', 'hotline_badge_text', 'hotline_badge_top_font_size', 'hotline_badge_scrolled_font_size', 'hotline_icon', 'hotline_icon_top_size', 'hotline_icon_scrolled_size', 'hotline_number_top_font_size', 'hotline_number_scrolled_font_size', 'hotline_number_font_weight', 'hotline_background_color', 'hotline_number_color', 'hotline_badge_background_color', 'hotline_badge_text_color', 'hotline_top_width', 'hotline_scrolled_width', 'hotline_top_offset_y', 'hotline_scrolled_offset_y', 'logo_top_width', 'logo_scrolled_width', 'layout_mode'],
                'content_layout_fields' => ['nav_active_background_color', 'hotline_badge_text', 'hotline_badge_top_font_size', 'hotline_badge_scrolled_font_size', 'hotline_icon', 'hotline_icon_top_size', 'hotline_icon_scrolled_size', 'hotline_number_top_font_size', 'hotline_number_scrolled_font_size', 'hotline_number_font_weight', 'hotline_background_color', 'hotline_number_color', 'hotline_badge_background_color', 'hotline_badge_text_color'],
                'display_layout_fields' => ['show_logo', 'show_navigation', 'show_phone', 'hotline_top_width', 'hotline_scrolled_width', 'hotline_top_offset_y', 'hotline_scrolled_offset_y', 'logo_top_width', 'logo_scrolled_width', 'layout_mode'],
                'navigation_position' => 'header',
                'navigation_terminal' => 'pc',
                'list_type' => null,
                'related_components' => [],
            ],
            'layout.header.mobile' => [
                'key' => 'layout.header.mobile',
                'tabs' => ['content', 'navigation', 'display', 'impact'],
                'site_fields' => ['cms_logo', 'cms_company', 'cms_phone'],
                'component_fields' => [],
                'layout_fields' => ['show_logo', 'show_navigation', 'show_phone', 'nav_active_background_color', 'hotline_badge_text', 'hotline_icon'],
                'content_layout_fields' => ['nav_active_background_color', 'hotline_badge_text', 'hotline_icon'],
                'display_layout_fields' => ['show_logo', 'show_navigation', 'show_phone'],
                'navigation_position' => 'header',
                'navigation_terminal' => 'mobile',
                'list_type' => null,
                'related_components' => [],
            ],
            'layout.footer.pc' => [
                'key' => 'layout.footer.pc',
                'tabs' => ['content', 'navigation', 'display', 'impact'],
                'site_fields' => $footerSiteFields,
                'component_fields' => [],
                'layout_fields' => ['show_company', 'show_contact', 'show_navigation', 'show_qrcode', 'show_beian'],
                'navigation_position' => 'footer',
                'navigation_terminal' => 'pc',
                'list_type' => null,
                'related_components' => $footerRelated,
            ],
            'layout.footer.mobile' => [
                'key' => 'layout.footer.mobile',
                'tabs' => ['content', 'navigation', 'display', 'impact'],
                'site_fields' => $footerSiteFields,
                'component_fields' => [],
                'layout_fields' => ['show_company', 'show_contact', 'show_navigation', 'show_qrcode', 'show_beian'],
                'navigation_position' => 'footer',
                'navigation_terminal' => 'mobile',
                'list_type' => null,
                'related_components' => $footerRelated,
            ],
            'layout.hot_search' => [
                'key' => 'layout.hot_search',
                'tabs' => ['content', 'list', 'display', 'impact'],
                'site_fields' => [],
                'component_fields' => ['title'],
                'layout_fields' => ['enabled', 'max_items'],
                'navigation_position' => null,
                'navigation_terminal' => 'all',
                'list_type' => 'hot_search',
                'related_components' => [],
            ],
            'layout.friend_links' => [
                'key' => 'layout.friend_links',
                'tabs' => ['content', 'list', 'display', 'impact'],
                'site_fields' => [],
                'component_fields' => ['title'],
                'layout_fields' => ['enabled'],
                'navigation_position' => null,
                'navigation_terminal' => 'all',
                'list_type' => 'friend_links',
                'related_components' => [],
            ],
            'layout.footer.company' => [
                'key' => 'layout.footer.company',
                'tabs' => ['content', 'display', 'impact'],
                'site_fields' => ['cms_company', 'cms_copyright_year', 'beian', 'cms_tech_support'],
                'component_fields' => ['title', 'content'],
                'layout_fields' => ['enabled'],
                'navigation_position' => null,
                'navigation_terminal' => 'all',
                'list_type' => null,
                'related_components' => [],
            ],
            'layout.footer.contact' => [
                'key' => 'layout.footer.contact',
                'tabs' => ['content', 'display', 'impact'],
                'site_fields' => ['cms_phone', 'cms_email', 'cms_address', 'cms_factory_address'],
                'component_fields' => ['title'],
                'layout_fields' => ['enabled'],
                'navigation_position' => null,
                'navigation_terminal' => 'all',
                'list_type' => null,
                'related_components' => [],
            ],
            'layout.footer.qrcode' => [
                'key' => 'layout.footer.qrcode',
                'tabs' => ['content', 'list', 'display', 'impact'],
                'site_fields' => ['cms_service_wechat_qr', 'cms_wechat_qr', 'cms_douyin_qr', 'cms_kuaishou_qr', 'cms_xiaohongshu_qr', 'cms_video_qr', 'cms_bilibili_qr'],
                'component_fields' => ['title'],
                'layout_fields' => ['enabled'],
                'navigation_position' => null,
                'navigation_terminal' => 'all',
                'list_type' => 'social',
                'related_components' => [],
            ],
            'layout.floating_service.pc' => [
                'key' => 'layout.floating_service.pc',
                'tabs' => ['content', 'display', 'impact'],
                'site_fields' => $floatingServiceSiteFields,
                'component_fields' => [],
                'layout_fields' => $floatingServiceLayoutFields,
                'navigation_position' => null,
                'navigation_terminal' => 'all',
                'list_type' => null,
                'related_components' => [],
            ],
        ];
    }
}

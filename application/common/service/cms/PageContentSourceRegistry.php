<?php

namespace app\common\service\cms;

/**
 * 页面管理与当前保留单页真实内容源的映射。
 */
class PageContentSourceRegistry
{
    protected static $singleSlugs = [
        'page.label' => 'label',
        'page.bags' => 'bags',
        'page.boxes' => 'boxes',
        'page.about' => 'about',
        'page.contact' => 'contact',
    ];

    protected static $pageContentBlockPages = [
        'page.label' => true,
        'page.bags' => true,
        'page.boxes' => true,
        'page.about' => true,
        'page.contact' => true,
    ];

    public static function resolve($pageKey)
    {
        $pageKey = trim((string)$pageKey);
        if (isset(self::$singleSlugs[$pageKey])) {
            return [
                'mode' => 'single',
                'slug' => self::$singleSlugs[$pageKey],
                'title' => '页面正文内容',
                'block_mode' => 'page_content',
            ];
        }
        return [
            'mode' => 'none',
            'slug' => '',
            'title' => '',
            'block_mode' => 'page_schema',
        ];
    }

    public static function fixedSlugs()
    {
        return self::$singleSlugs;
    }
}

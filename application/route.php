<?php

return [
    '__alias__' => [],
    '__pattern__' => [
        'slug' => '[a-zA-Z0-9\-_]+',
        'category' => '[a-zA-Z0-9\-_]+',
        'token' => '[a-f0-9]{64}',
    ],

    // 保留 html/ 前端基线使用的静态风格 URL，对外仍由动态 Controller + View 渲染。
    'index.html$' => 'index/index/index',
    'labels.html$' => 'index/page/labels',
    'bags.html$' => 'index/page/bags',
    'boxes.html$' => 'index/page/boxes',
    'about.html$' => 'index/page/about',
    'contact.html$' => 'index/page/contact',
    'news.html$' => 'index/news/index',

    'mobile/index.html$' => 'mobile/index/index',
    'mobile/labels.html$' => 'mobile/page/labels',
    'mobile/bags.html$' => 'mobile/page/bags',
    'mobile/boxes.html$' => 'mobile/page/boxes',
    'mobile/about.html$' => 'mobile/page/about',
    'mobile/contact.html$' => 'mobile/page/contact',
    'mobile/news.html$' => 'mobile/news/index',

    'mobile$' => 'mobile/index/index',
    'mobile/products$' => 'mobile/product/index',
    'mobile/product/:slug' => 'mobile/product/detail',
    'mobile/news$' => 'mobile/news/index',
    'mobile/news-list/:category' => 'mobile/news/index',
    'mobile/news/:slug' => 'mobile/news/detail',
    'mobile/search$' => 'mobile/search/index',
    'mobile/sitemap$' => 'mobile/search/sitemap',
    'mobile/page/:slug' => 'mobile/page/detail',
    'mobile/inquiry/submit' => 'mobile/inquiry/submit',
    'mobile/cms-preview/:token' => 'mobile/preview/show',

    'products$' => 'index/product/index',
    'product/:slug' => 'index/product/detail',
    'news$' => 'index/news/index',
    'news-list/:category' => 'index/news/index',
    'news/:slug' => 'index/news/detail',
    'search$' => 'index/search/index',
    'sitemap$' => 'index/search/sitemap',
    'page/:slug' => 'index/page/detail',
    'inquiry/submit' => 'index/inquiry/submit',
    'cms-preview/:token' => 'index/preview/show',
];

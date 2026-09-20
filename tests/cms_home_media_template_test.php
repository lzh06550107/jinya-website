<?php

define('APP_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR);
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('RUNTIME_PATH', ROOT_PATH . 'runtime' . DIRECTORY_SEPARATOR);
define('DS', DIRECTORY_SEPARATOR);

function __($text)
{
    return $text;
}

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_NOTICE & ~E_WARNING);
require ROOT_PATH . 'thinkphp/base.php';
set_error_handler(static function () {
    return true;
}, E_DEPRECATED | E_USER_DEPRECATED | E_NOTICE | E_WARNING);
\think\Loader::addNamespace('app', APP_PATH);

$resolver = new \app\common\service\cms\render\HomeMediaResolver();
$aboutItems = [
    $resolver->resolve(['title' => 'About video', 'image' => '/poster-about-should-not-render.jpg', 'video_url' => '/media/about.mp4', 'url' => '#']),
    $resolver->resolve(['title' => 'About image', 'image' => '/media/about.webp', 'video_url' => '', 'url' => '#']),
];
$companyItems = [
    $resolver->resolve(['title' => 'Company video', 'image' => '/poster-company-should-not-render.jpg', 'video_url' => '/media/company.webm', 'url' => '#']),
    $resolver->resolve(['title' => 'Company embed', 'image' => '/poster-embed-should-not-render.jpg', 'video_url' => 'https://player.example.test/embed/123', 'url' => '#']),
    $resolver->resolve(['title' => 'Company image', 'image' => '/media/company.jpg', 'video_url' => '', 'url' => '#']),
];

$emptySection = [];
$vars = [
    'title' => 'Media template test',
    'keywords' => '',
    'description' => '',
    'robots' => 'noindex',
    'canonical' => '',
    'cmsMobileUrl' => '/',
    'cmsSite' => ['name' => 'Test', 'version' => 'test', 'logo' => '', 'slogan' => '', 'beian' => ''],
    'pcStrictHome' => true,
    'pcAssetBase' => '/assets/kcm-pc-strict',
    'pcStrictBundle' => '67a40dc5e4b056b7c69412ce',
    'pcThemeCss' => 'home',
    'pcBodyClass' => '',
    'isPreview' => false,
    'cmsPcHeaderLayoutMode' => 'default',
    'headerNav' => [],
    'footerNav' => [],
    'layout' => [
        'footer_company' => ['title' => '', 'content' => '', 'config' => []],
        'footer_contact' => ['config' => []],
        'friend_links' => ['title' => '', 'config' => ['items' => []]],
        'mobile_toolbar' => ['config' => ['items' => []]],
    ],
    'mobileStrictHome' => true,
    'mobileStrictUsesSwiper' => true,
    'mobileStrictAssetBase' => '/assets/kcm-mobile-strict',
    'mobileStrictBundle' => '753c9da66cb7ed51e382681e',
    'mobileThemeCss' => 'home',
    'mobileHomeUrl' => '/',
    'cmsNavigation' => [],
    'hero' => [],
    'section_order' => ['about', 'company'],
    'about' => [
        'title' => 'About',
        'content_html' => '<p>About</p>',
        'more_url' => '#',
        'config' => [
            'media_items' => $aboutItems,
            'social_icon_1_view' => ['type' => 'none', 'class' => '', 'url' => ''],
            'social_icon_2_view' => ['type' => 'none', 'class' => '', 'url' => ''],
            'social_text_1' => '',
            'social_text_2' => '',
        ],
    ],
    'company' => [
        'title' => 'Company',
        'content_text' => 'Company',
        'background' => '',
        'more_url' => '#',
        'more_text' => '',
        'config' => ['media_items' => $companyItems, 'metrics' => []],
    ],
    'products' => $emptySection,
    'service' => $emptySection,
    'workshop' => $emptySection,
    'cases' => $emptySection,
    'advantages' => $emptySection,
    'news' => $emptySection,
    'culture' => $emptySection,
];

$cachePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cms-home-media-template-' . bin2hex(random_bytes(6)) . DIRECTORY_SEPARATOR;
mkdir($cachePath, 0755, true);

try {
    $template = new \think\Template([
        'view_path' => APP_PATH . 'index' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR,
        'cache_path' => $cachePath,
        'tpl_replace_string' => ['__CDN__' => ''],
    ]);
    ob_start();
    $template->fetch(APP_PATH . 'index/view/cms/index/index.html', $vars);
    $html = ob_get_clean();

    foreach (['/media/about.mp4', '/media/about.webp', '/media/company.webm', 'https://player.example.test/embed/123', '/media/company.jpg'] as $expected) {
        if (strpos($html, $expected) === false) {
            fwrite(STDERR, "Rendered homepage is missing expected media: {$expected}\n");
            exit(1);
        }
    }
    foreach (['/poster-about-should-not-render.jpg', '/poster-company-should-not-render.jpg', '/poster-embed-should-not-render.jpg'] as $unexpected) {
        if (strpos($html, $unexpected) !== false) {
            fwrite(STDERR, "A lower-priority image was rendered instead of its video: {$unexpected}\n");
            exit(1);
        }
    }
    if (substr_count($html, '<video class="cms-home-media-player"') !== 2) {
        fwrite(STDERR, "Expected exactly two native video players in the rendered homepage.\n");
        exit(1);
    }
    if (strpos($html, '<iframe class="cms-home-media-player"') === false) {
        fwrite(STDERR, "Expected an iframe for the external player-page URL.\n");
        exit(1);
    }
    if (strpos($html, 'cms-home-media.js?v=test&layout=video-first-carousel-r2') === false) {
        fwrite(STDERR, "The homepage must request the updated carousel fallback asset URL.\n");
        exit(1);
    }
    if (strpos($html, '67a40dc5e4b056b7c69412ce.js?v=test&fix=banner-video-null-r1') === false) {
        fwrite(STDERR, "The homepage must request the null-safe strict bundle asset URL.\n");
        exit(1);
    }

    $mobileTemplate = new \think\Template([
        'view_path' => APP_PATH . 'mobile' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR,
        'cache_path' => $cachePath,
        'tpl_replace_string' => ['__CDN__' => ''],
    ]);
    ob_start();
    $mobileTemplate->fetch(APP_PATH . 'mobile/view/cms/index/index.html', $vars);
    $mobileHtml = ob_get_clean();
    if (strpos($mobileHtml, '<section class="g-bd">') === false) {
        fwrite(STDERR, "The mobile homepage template did not compile to the expected page structure.\n");
        exit(1);
    }
} finally {
    foreach (glob($cachePath . '*') ?: [] as $cacheFile) {
        if (is_file($cacheFile)) {
            unlink($cacheFile);
        }
    }
    rmdir($cachePath);
}

fwrite(STDOUT, "cms_home_media_template_test: PASS\n");

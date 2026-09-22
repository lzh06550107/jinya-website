<?php

define('APP_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR);
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('RUNTIME_PATH', sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'jinya-template-runtime' . DIRECTORY_SEPARATOR);
define('DS', DIRECTORY_SEPARATOR);

function __($text) { return $text; }
function build_radios($name, $list = [], $selected = null) { return ''; }

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_NOTICE & ~E_WARNING);
require ROOT_PATH . 'thinkphp/base.php';
set_error_handler(static function () { return true; }, E_DEPRECATED | E_USER_DEPRECATED | E_NOTICE | E_WARNING);
\think\Loader::addNamespace('app', APP_PATH);

try {
    $coloredText = \app\common\service\cms\MarkdownRenderer::renderInline(
        '普通文字 [color=#E25042]重点文字[/color] 其它文字'
    );
    if (strpos($coloredText, 'style="color:#e25042"') === false) {
        throw new \RuntimeException('partial text color style was not rendered');
    }
    if (strpos($coloredText, 'data-cms-text-color') !== false) {
        throw new \RuntimeException('temporary color marker leaked into frontend HTML');
    }
    $invalidColor = \app\common\service\cms\MarkdownRenderer::renderInline(
        '[color=javascript:alert(1)]危险文字[/color]'
    );
    if (strpos($invalidColor, 'style=') !== false) {
        throw new \RuntimeException('invalid partial text color produced an inline style');
    }
    $richBreaks = \app\common\service\cms\MarkdownRenderer::renderInlinePreserveLineBreaks(
        "特点：第一行\n优点：第二行\n用途：第三行"
    );
    if (substr_count($richBreaks, '<br>') !== 2) {
        throw new \RuntimeException('editor line breaks were not preserved as <br>');
    }
    $normalInline = \app\common\service\cms\MarkdownRenderer::renderInline("第一行\n第二行");
    if (strpos($normalInline, '<br>') !== false) {
        throw new \RuntimeException('normal Markdown inline rendering unexpectedly changed soft line breaks');
    }
    echo "MARKDOWN COLOR/BREAKS OK [label capability rich text]\n";
} catch (\Throwable $e) {
    fwrite(STDERR, "MARKDOWN COLOR FAIL: " . $e->getMessage() . "\n");
    exit(1);
}

$cacheRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'jinya-html-baseline-template-' . bin2hex(random_bytes(6));
mkdir($cacheRoot, 0755, true);

$baseLayout = [
    'header' => ['config' => ['hotline_badge_text' => '急单专线']],
    'navigation' => ['header' => [], 'footer' => []],
    'footer_company' => ['title' => '', 'content' => '', 'config' => []],
    'footer_contact' => ['title' => '', 'config' => []],
    'footer_qrcode' => ['title' => '', 'config' => ['items' => []]],
    'friend_links' => ['title' => '', 'config' => ['items' => []]],
    'floating_service' => ['config' => []],
];

$common = [
    'title' => '模板编译测试',
    'keywords' => '',
    'description' => '',
    'robots' => 'noindex,nofollow',
    'canonical' => '',
    'cmsMobileUrl' => '',
    'cmsSite' => [
        'name' => '金亚包装',
        'version' => 'test',
        'logo' => '',
        'hotline' => '18903716652',
        'slogan' => '',
        'beian' => '',
        'email' => '',
        'address' => '',
        'cms_copyright_year' => '2026',
        'cms_tech_support' => '',
        'cms_service_wechat_qr' => '',
    ],
    'layout' => $baseLayout,
    'cmsCurrentPath' => '/',
    'cmsNavigationTree' => [],
    'cmsNavigation' => [],
    'pcBodyClass' => 'pc-test',
    'pcStrictHome' => false,
    'mobileBodyClass' => 'mobile-test',
    'mobileThemeCss' => 'about',
    'mobileStrictHome' => false,
    'mobileHomeUrl' => '/mobile',
    'mobileBase' => '/mobile',
    'isPreview' => false,
    'content' => [
        'page' => ['title' => 'Test'],
        'blocks' => [],
        'categories' => [],
        'currentCategory' => [],
        'articles' => ['items' => [], 'empty_message' => ''],
        'article' => ['title' => '', 'summary' => '', 'cover' => '', 'content_html' => '', 'tags' => []],
        'metadata' => ['source' => '', 'publish_date' => '', 'author' => ''],
        'previous' => [],
        'next' => [],
    ],
    'banner' => [],
    'hero' => [],
    'about' => [],
    'products' => [],
    'service' => [],
    'workshop' => [],
    'company' => [],
    'culture' => [],
    'pcPagination' => '',
];

$targets = [
    'index' => [
        'cms/index/index.html',
        'cms/page/label.html',
        'cms/page/bags.html',
        'cms/page/boxes.html',
        'cms/page/about.html',
        'cms/page/contact.html',
        'cms/news/index.html',
        'cms/news/detail.html',
    ],
    'mobile' => [
        'cms/index/index.html',
        'cms/page/label.html',
        'cms/page/bags.html',
        'cms/page/boxes.html',
        'cms/page/about.html',
        'cms/page/contact.html',
        'cms/news/index.html',
        'cms/news/detail.html',
    ],
];

foreach ($targets as $module => $templates) {
    $template = new \think\Template([
        'view_path' => APP_PATH . $module . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR,
        'cache_path' => $cacheRoot . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR,
        'tpl_replace_string' => ['__CDN__' => ''],
    ]);
    foreach ($templates as $relative) {
        try {
            ob_start();
            $template->fetch(APP_PATH . $module . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $relative, $common);
            ob_end_clean();
            echo "TEMPLATE OK [$module] $relative\n";
        } catch (\Throwable $e) {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            fwrite(STDERR, "TEMPLATE FAIL [$module] $relative: " . $e->getMessage() . "\n");
            exit(1);
        }
    }
}

echo "HTML-baseline ThinkPHP template compile: OK\n";

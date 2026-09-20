<?php

define('APP_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR);
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('RUNTIME_PATH', ROOT_PATH . 'runtime' . DIRECTORY_SEPARATOR);
define('DS', DIRECTORY_SEPARATOR);

function __($text)
{
    return $text;
}

function build_radios($name, $list = [], $selected = null)
{
    return '';
}

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_NOTICE & ~E_WARNING);
require ROOT_PATH . 'thinkphp/base.php';
set_error_handler(static function () {
    return true;
}, E_DEPRECATED | E_USER_DEPRECATED | E_NOTICE | E_WARNING);
\think\Loader::addNamespace('app', APP_PATH);

class CmsBaseBannerHarness extends \app\index\controller\CmsBase
{
    public function _initialize()
    {
    }

    public function resolveChannelBanner(\app\common\viewmodel\cms\PageViewModel $viewModel)
    {
        $this->pcSection = 'news';
        $this->applyChannelFromViewModel($viewModel, '新闻动态', '/news');
        return [
            'title' => isset($this->view->channelBannerTitle) ? $this->view->channelBannerTitle : null,
            'subtitle' => isset($this->view->channelBannerSubtitle) ? $this->view->channelBannerSubtitle : null,
        ];
    }
}

$cachePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cms-pc-inner-banner-' . bin2hex(random_bytes(6)) . DIRECTORY_SEPARATOR;
mkdir($cachePath, 0755, true);

$template = new \think\Template([
    'view_path' => APP_PATH . 'index' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR,
    'cache_path' => $cachePath,
    'tpl_replace_string' => ['__CDN__' => ''],
]);

$render = static function ($relativePath, array $vars) use ($template) {
    ob_start();
    $template->fetch(APP_PATH . 'index/view/cms/' . $relativePath, $vars);
    return ob_get_clean();
};

$assertContains = static function ($needle, $html, $message) {
    if (strpos($html, $needle) === false) {
        fwrite(STDERR, $message . "\nMissing: " . $needle . "\n");
        exit(1);
    }
};

$assertNotContains = static function ($needle, $html, $message) {
    if (strpos($html, $needle) !== false) {
        fwrite(STDERR, $message . "\nUnexpected: " . $needle . "\n");
        exit(1);
    }
};

$heroTemplates = [
    'page/label/hero.html',
    'page/bags/hero.html',
    'page/boxes/hero.html',
    'page/about/hero.html',
    'page/contact/hero.html',
];

try {
    foreach ($heroTemplates as $heroTemplate) {
        $imageOnly = $render($heroTemplate, [
            'block' => [
                'title' => '',
                'content_inline_html' => '',
                'image' => '/uploads/banner-only.jpg',
                'extra' => ['items' => []],
            ],
        ]);
        $assertContains('cms-pc-inner-banner', $imageOnly, "{$heroTemplate} must opt into the full-width PC background Banner layout.");
        $assertContains("background-image:url('/uploads/banner-only.jpg')", $imageOnly, "{$heroTemplate} must render the uploaded image as the Banner background.");
        $assertNotContains('class="hero-text"', $imageOnly, "{$heroTemplate} must not render an empty text layer for an image-only Banner.");

        $withCopy = $render($heroTemplate, [
            'block' => [
                'title' => '可选标题',
                'content_inline_html' => '<em>可选说明</em>',
                'image' => '/uploads/banner-with-copy.jpg',
                'extra' => ['items' => []],
            ],
        ]);
        $assertContains('cms-pc-inner-banner has-copy', $withCopy, "{$heroTemplate} must mark a configured text overlay.");
        $assertContains('<h1>可选标题</h1>', $withCopy, "{$heroTemplate} must render a configured title.");
        $assertContains('<p class="lede"><em>可选说明</em></p>', $withCopy, "{$heroTemplate} must render configured explanatory text.");
    }

    $layout = [
        'header' => ['config' => []],
        'hot_search' => ['config' => ['items' => [], 'placeholder' => '', 'button_text' => '']],
        'footer_company' => ['title' => '', 'content' => '', 'config' => []],
        'footer_contact' => ['config' => []],
        'friend_links' => ['title' => '', 'config' => ['items' => []]],
        'mobile_toolbar' => ['config' => ['items' => []]],
    ];
    $pageVars = [
        'title' => 'PC inner Banner test',
        'cmsSite' => ['name' => 'Test', 'version' => 'test', 'logo' => '', 'slogan' => '', 'beian' => ''],
        'cmsMobileUrl' => '/',
        'pcStrictHome' => false,
        'pcAssetBase' => '/assets/kcm-pc-strict',
        'pcStrictBundle' => '67a71e89e4b0f9d0b8025587',
        'pcThemeCss' => 'about',
        'pcBodyClass' => '',
        'isPreview' => false,
        'cmsNavigation' => [],
        'layout' => $layout,
        'content' => ['page' => ['title' => 'Test'], 'blocks' => []],
    ];
    $pageTemplates = [
        'page/label.html' => 'label_hero',
        'page/bags.html' => 'bags_hero',
        'page/boxes.html' => 'boxes_hero',
        'page/about.html' => 'about_hero',
        'page/contact.html' => 'contact_hero',
    ];
    foreach ($pageTemplates as $pageTemplate => $blockKey) {
        $vars = $pageVars;
        $vars['content']['blocks'] = [[
            'key' => $blockKey,
            'title' => '',
            'content_inline_html' => '',
            'image' => '/uploads/full-page.jpg',
            'extra' => ['items' => []],
        ]];
        $fullPage = $render($pageTemplate, $vars);
        $assertContains(
            'cms-pc-inner-banner.css?v=test&layout=background-copy-optional-r1',
            $fullPage,
            "{$pageTemplate} must load the shared PC Banner layout stylesheet."
        );
    }

    $channelImageOnly = $render('common/strict_channel_header.html', [
        'channelBannerVisible' => true,
        'channelBanner' => '/uploads/news-only.jpg',
        'channelBannerTitle' => '',
        'channelBannerSubtitle' => '',
        'channelTitle' => '新闻动态',
        'pcSection' => 'news',
        'pcThemeCss' => 'article-list',
        'pcAssetBase' => '/assets/kcm-pc-strict',
        'hideChannelBreadcrumb' => true,
        'hideChannelSearch' => true,
    ]);
    $assertContains('cms-pc-inner-banner', $channelImageOnly, 'News Banner must use the shared full-width background layout.');
    $assertContains("background-image:url('/uploads/news-only.jpg')", $channelImageOnly, 'News Banner must render its image as a CSS background.');
    $assertNotContains('<img', $channelImageOnly, 'News Banner must not render its visual as an inline image.');
    $assertNotContains('cms-pc-inner-banner-copy', $channelImageOnly, 'News image-only Banner must not render an empty text layer.');

    $channelWithCopy = $render('common/strict_channel_header.html', [
        'channelBannerVisible' => true,
        'channelBanner' => '/uploads/news-with-copy.jpg',
        'channelBannerTitle' => '新闻标题',
        'channelBannerSubtitle' => '新闻说明',
        'channelTitle' => '新闻动态',
        'pcSection' => 'news',
        'pcThemeCss' => 'article-list',
        'pcAssetBase' => '/assets/kcm-pc-strict',
        'hideChannelBreadcrumb' => true,
        'hideChannelSearch' => true,
    ]);
    $assertContains('cms-pc-inner-banner has-copy', $channelWithCopy, 'News Banner must mark a configured text overlay.');
    $assertContains('<h1>新闻标题</h1>', $channelWithCopy, 'News Banner must render a configured title.');
    $assertContains('<p>新闻说明</p>', $channelWithCopy, 'News Banner must render a configured subtitle.');

    $productChannel = $render('common/strict_channel_header.html', [
        'channelBannerVisible' => true,
        'channelBanner' => '/uploads/product-channel.jpg',
        'channelTitle' => '产品中心',
        'pcSection' => 'products',
        'pcThemeCss' => 'product-list',
        'pcAssetBase' => '/assets/kcm-pc-strict',
        'hideChannelBreadcrumb' => true,
        'hideChannelSearch' => true,
    ]);
    $assertContains('<img', $productChannel, 'Pages outside this request must preserve their strict inline-image channel Banner.');
    $assertNotContains('cms-pc-inner-banner', $productChannel, 'The new background Banner must not leak into product pages.');

    $channelViewModel = new \app\common\viewmodel\cms\PageViewModel(
        [],
        [],
        [[
            'image' => 'https://example.test/news.jpg',
            'title' => '  新闻标题  ',
            'subtitle' => '  新闻说明  ',
        ]],
        [['title' => '新闻动态', 'url' => '/news']]
    );
    $resolvedChannelCopy = (new CmsBaseBannerHarness())->resolveChannelBanner($channelViewModel);
    if ($resolvedChannelCopy !== ['title' => '新闻标题', 'subtitle' => '新闻说明']) {
        fwrite(STDERR, "PC channel Banner must receive its optional title and subtitle from the rendered Banner record.\n");
        exit(1);
    }

    $newsVars = $pageVars;
    $newsVars['pcThemeCss'] = 'article-list';
    $newsVars['pcSection'] = 'news';
    $newsVars['pcStrictBundle'] = '67b692bce4b0d0e791e075ec';
    $newsVars['channelBannerVisible'] = true;
    $newsVars['channelBanner'] = '/uploads/news-only.jpg';
    $newsVars['channelBannerTitle'] = '';
    $newsVars['channelBannerSubtitle'] = '';
    $newsVars['channelTitle'] = '新闻动态';
    $newsVars['hideChannelBreadcrumb'] = true;
    $newsVars['content'] = [
        'currentCategory' => [],
        'categories' => [],
        'articles' => ['items' => []],
        'visibility' => ['date' => false],
    ];
    $newsPage = $render('news/index.html', $newsVars);
    $assertContains(
        'cms-pc-inner-banner.css?v=test&layout=background-copy-optional-r1',
        $newsPage,
        'News page must load the shared PC Banner layout stylesheet.'
    );

    $adminTemplate = new \think\Template([
        'view_path' => APP_PATH . 'admin' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR,
        'cache_path' => $cachePath,
    ]);
    ob_start();
    $adminTemplate->fetch(APP_PATH . 'admin/view/cms/banner/_form.html', [
        'row' => [],
        'statusList' => ['normal' => '正常', 'hidden' => '隐藏'],
    ]);
    $adminHtml = ob_get_clean();
    $adminDocument = new DOMDocument();
    $adminDocument->loadHTML('<?xml encoding="utf-8" ?>' . $adminHtml);
    $titleInput = $adminDocument->getElementById('c-title');
    if (!$titleInput) {
        fwrite(STDERR, "Banner admin form must render the PC title input.\n");
        exit(1);
    }
    if ($titleInput->hasAttribute('required') || strpos((string)$titleInput->getAttribute('data-rule'), 'required') !== false) {
        fwrite(STDERR, "Banner PC title must be optional so an image-only Banner can be saved.\n");
        exit(1);
    }

    $bannerValidator = new \app\common\validate\cms\Banner();
    if (!$bannerValidator->check(['title' => '', 'image' => '/uploads/image-only.jpg'])) {
        fwrite(STDERR, "Banner validation must accept an uploaded image without title text.\n");
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

fwrite(STDOUT, "cms_pc_inner_banner_template_test: PASS\n");

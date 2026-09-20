<?php

define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('APP_PATH', ROOT_PATH . 'application' . DIRECTORY_SEPARATOR);
define('RUNTIME_PATH', ROOT_PATH . 'runtime' . DIRECTORY_SEPARATOR);

function __($text)
{
    return $text;
}

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
require ROOT_PATH . 'thinkphp/base.php';
set_error_handler(static function () {
    return true;
}, E_DEPRECATED | E_USER_DEPRECATED);
\think\Loader::addNamespace('app', APP_PATH);

$resolverFile = ROOT_PATH . 'application/common/service/cms/render/HomeMediaResolver.php';
if (!is_file($resolverFile)) {
    fwrite(STDERR, "Home media resolver is missing.\n");
    exit(1);
}

require_once $resolverFile;

function assertSameValue($expected, $actual, $message)
{
    if ($expected !== $actual) {
        fwrite(STDERR, $message . " Expected " . var_export($expected, true) . ", got " . var_export($actual, true) . ".\n");
        exit(1);
    }
}

$resolver = new \app\common\service\cms\render\HomeMediaResolver();

$videoFirst = $resolver->resolve([
    'title' => '视频优先',
    'image' => '/uploads/poster.jpg',
    'video_url' => '/uploads/demo.mp4?version=2',
]);
assertSameValue('video', $videoFirst['display_type'], 'A direct video must take priority over the image.');
assertSameValue('/uploads/demo.mp4?version=2', $videoFirst['display_url'], 'The selected direct-video URL is incorrect.');
assertSameValue('video/mp4', $videoFirst['video_mime'], 'The MP4 MIME type is incorrect.');

$embedFirst = $resolver->resolve([
    'image' => '/uploads/poster.jpg',
    'video_url' => 'https://player.example.test/embed/123',
]);
assertSameValue('video_embed', $embedFirst['display_type'], 'A player-page URL must take priority and use embedded-video rendering.');

$imageFallback = $resolver->resolve([
    'image' => '/uploads/fallback.webp',
    'video_url' => '   ',
]);
assertSameValue('image', $imageFallback['display_type'], 'An image must be used when no video is configured.');
assertSameValue('/uploads/fallback.webp', $imageFallback['display_url'], 'The fallback image URL is incorrect.');

$empty = $resolver->resolve([]);
assertSameValue('empty', $empty['display_type'], 'An empty media item must remain empty.');

$serviceReflection = new ReflectionClass(\app\common\service\cms\render\HomeRenderService::class);
$service = $serviceReflection->newInstanceWithoutConstructor();
$iconsProperty = new ReflectionProperty(\app\common\service\cms\render\AbstractRenderService::class, 'icons');
$iconsProperty->setAccessible(true);
$iconsProperty->setValue($service, new \app\common\service\cms\CmsIconValue());
$sectionMethod = $serviceReflection->getMethod('section');
$sectionMethod->setAccessible(true);
$companySection = $sectionMethod->invoke($service, [
    'section_key' => 'company',
    'config' => ['media_items' => [[
        'title' => 'Service integration',
        'image' => '/uploads/service-image.jpg',
        'video_url' => '/uploads/service-video.ogg',
        'url' => '#',
    ]]],
]);
assertSameValue(
    'video',
    $companySection['config']['media_items'][0]['display_type'],
    'HomeRenderService must apply the resolver to company media items.'
);

$editor = file_get_contents(ROOT_PATH . 'application/admin/view/cms/common/_media_items_editor.html');
if (strpos($editor, '海报图片:') !== false || strpos($editor, '>图片:</label>') === false) {
    fwrite(STDERR, "The media editor must label the field as 图片 instead of 海报图片.\n");
    exit(1);
}

fwrite(STDOUT, "cms_home_media_priority_test: PASS\n");

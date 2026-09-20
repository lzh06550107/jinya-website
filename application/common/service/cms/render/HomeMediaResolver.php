<?php
namespace app\common\service\cms\render;

/**
 * Resolve the single media value rendered by an About/Company media item.
 *
 * Original image and video_url values stay intact for edit compatibility.
 * The display_* fields make the video-first rule explicit for templates.
 */
class HomeMediaResolver
{
    private $videoMimeTypes = [
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'ogg' => 'video/ogg',
    ];

    public function resolve(array $item)
    {
        $image = isset($item['image']) ? trim((string)$item['image']) : '';
        $videoUrl = isset($item['video_url']) ? trim((string)$item['video_url']) : '';

        $item['display_type'] = 'empty';
        $item['display_url'] = '';
        $item['video_mime'] = '';

        if ($videoUrl !== '') {
            $extension = $this->extension($videoUrl);
            $item['display_type'] = isset($this->videoMimeTypes[$extension]) ? 'video' : 'video_embed';
            $item['display_url'] = $videoUrl;
            $item['video_mime'] = isset($this->videoMimeTypes[$extension]) ? $this->videoMimeTypes[$extension] : '';
            return $item;
        }

        if ($image !== '') {
            $item['display_type'] = 'image';
            $item['display_url'] = $image;
        }

        return $item;
    }

    private function extension($url)
    {
        $path = parse_url((string)$url, PHP_URL_PATH);
        if ($path === false || $path === null) {
            $path = (string)$url;
        }
        return strtolower((string)pathinfo($path, PATHINFO_EXTENSION));
    }
}

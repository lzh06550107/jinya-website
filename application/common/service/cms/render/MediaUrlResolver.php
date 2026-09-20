<?php
namespace app\common\service\cms\render;
class MediaUrlResolver
{
    public function resolve($primary, $fallback = '', $alt = '')
    {
        $url = trim((string)$primary) !== '' ? trim((string)$primary) : trim((string)$fallback);
        return ['url' => $url, 'exists' => $url !== '', 'alt' => (string)$alt];
    }
}

<?php

namespace app\common\service\cms;

/**
 * Resolve the PC navigation item that should receive the active class.
 */
class PcNavigationPathService
{
    public function normalize($path)
    {
        $path = parse_url((string)$path, PHP_URL_PATH);
        $path = $path !== null && $path !== false && $path !== '' ? $path : '/';
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }
        return (new CmsUrlService())->normalizeInternal($path);
    }

    public function resolve($path, $section = '')
    {
        $path = $this->normalize($path);
        $section = strtolower(trim((string)$section));

        if ($section === 'news') {
            return '/news';
        }
        if ($section === 'home') {
            return '/';
        }
        return $path;
    }
}

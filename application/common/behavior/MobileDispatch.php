<?php

namespace app\common\behavior;

use app\common\service\cms\MobileRequestResolver;
use think\Cookie;

class MobileDispatch
{
    const COOKIE_NAME = 'cms_view';
    const COOKIE_EXPIRE = 2592000;

    public function appBegin(&$dispatch)
    {
        if (PHP_SAPI === 'cli' || !isset($dispatch['type']) || $dispatch['type'] !== 'module') {
            return;
        }

        $module = isset($dispatch['module'][0]) ? strtolower((string)$dispatch['module'][0]) : '';
        $controller = isset($dispatch['module'][1]) ? strtolower((string)$dispatch['module'][1]) : '';
        $resolver = new MobileRequestResolver();
        if (!$resolver->canDispatchModule($module, $controller)) {
            return;
        }

        $request = request();
        $forcedPreference = $resolver->normalizePreference($request->get('view', ''));
        if ($forcedPreference !== '') {
            Cookie::set(self::COOKIE_NAME, $forcedPreference, [
                'expire' => self::COOKIE_EXPIRE,
                'path' => '/',
                'httponly' => true,
            ]);
        }

        $viewMode = $resolver->resolve(
            $forcedPreference,
            (string)Cookie::get(self::COOKIE_NAME),
            $request->isMobile()
        );

        if ($viewMode === MobileRequestResolver::MOBILE) {
            $dispatch['module'][0] = 'mobile';
        } elseif ($module === '') {
            $dispatch['module'][0] = 'index';
        }
    }
}

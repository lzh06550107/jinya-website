<?php

namespace app\common\service\cms;

class MobileRequestResolver
{
    const MOBILE = 'mobile';
    const DESKTOP = 'desktop';

    public function normalizePreference($value)
    {
        $value = strtolower(trim((string)$value));
        return in_array($value, [self::MOBILE, self::DESKTOP], true) ? $value : '';
    }

    public function canDispatchModule($module, $controller)
    {
        $module = strtolower(trim((string)$module));
        $controller = strtolower(trim((string)$controller));
        if ($module !== '' && $module !== 'index') {
            return false;
        }

        return in_array($controller, ['', 'index', 'product', 'news', 'cases', 'page', 'search', 'inquiry', 'preview', 'legacy'], true);
    }

    public function resolve($forcedPreference, $cookiePreference, $isMobileUserAgent)
    {
        $forcedPreference = $this->normalizePreference($forcedPreference);
        if ($forcedPreference !== '') {
            return $forcedPreference;
        }

        $cookiePreference = $this->normalizePreference($cookiePreference);
        if ($cookiePreference !== '') {
            return $cookiePreference;
        }

        return $isMobileUserAgent ? self::MOBILE : self::DESKTOP;
    }
}

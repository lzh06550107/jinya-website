<?php

namespace app\common\service\cms;

use think\Cache;

class CacheService
{
    public static function clearByType($type, $id = null, $slug = null)
    {
        Cache::rm('cms:home');
        Cache::rm('cms:navigation');
        if ($type) {
            Cache::rm('cms:' . $type . ':list');
        }
        if ($id) {
            Cache::rm('cms:' . $type . ':id:' . $id);
        }
        if ($slug) {
            Cache::rm('cms:' . $type . ':slug:' . $slug);
        }
    }

    public static function pageConfigKeys($pageKey)
    {
        return [
            'cms:page-config:' . $pageKey . ':pc',
            'cms:page-config:' . $pageKey . ':mobile',
        ];
    }

    public static function clearPageConfig($pageKey)
    {
        self::clearKeys(self::pageConfigKeys($pageKey));
    }

    public static function clearPageConfigs(array $pageKeys)
    {
        $keys = [];
        foreach (array_values(array_unique(array_filter(array_map('strval', $pageKeys)))) as $pageKey) {
            $keys = array_merge($keys, self::pageConfigKeys($pageKey));
        }
        self::clearKeys($keys);
    }

    public static function clearKeys(array $keys)
    {
        foreach (array_values(array_unique(array_filter(array_map('strval', $keys)))) as $key) {
            Cache::rm($key);
        }
    }
}

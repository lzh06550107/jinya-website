<?php

namespace app\common\service\cms;

/**
 * Keeps label_elements aligned with the current three-item diagram model.
 * Legacy direction/core helper items are retired and must not survive saves
 * or installer upgrades.
 */
class LabelElementsConfigSanitizer
{
    private static $supportedGroups = [
        'intro-direction',
        'intro-core',
        'intro-diameter',
    ];

    public static function sanitizeItems(array $items)
    {
        $result = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $group = isset($item['group']) && !is_array($item['group']) && !is_object($item['group'])
                ? trim((string)$item['group'])
                : '';
            if (!in_array($group, self::$supportedGroups, true)) {
                continue;
            }
            $result[] = $item;
        }
        return $result;
    }

    public static function sanitizeExtra(array $extra)
    {
        $items = isset($extra['items']) && is_array($extra['items']) ? $extra['items'] : [];
        $extra['items'] = self::sanitizeItems($items);
        return $extra;
    }

    public static function supportedGroups()
    {
        return self::$supportedGroups;
    }
}

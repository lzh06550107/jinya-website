<?php

namespace app\common\service\cms;

/**
 * 发布前最小字段校验。
 */
class ContentGuard
{
    protected static $required = [
        'product' => ['title', 'category_id', 'cover_image', 'summary'],
        'article' => ['title', 'category_id', 'content'],
        'page' => ['title', 'slug', 'content'],
        'case' => ['title', 'cover_image', 'content'],
        'banner' => ['title', 'image'],
        'home_section' => ['section_key', 'title'],
    ];

    public static function missingForPublish($type, array $data)
    {
        $required = isset(self::$required[$type]) ? self::$required[$type] : ['title'];
        $missing = [];
        foreach ($required as $field) {
            if (!array_key_exists($field, $data) || self::isEmpty($data[$field])) {
                $missing[] = $field;
            }
        }
        return $missing;
    }

    public static function assertPublishable($type, array $data)
    {
        $missing = self::missingForPublish($type, $data);
        if ($missing) {
            throw new \InvalidArgumentException('发布前请完善字段：' . implode(', ', $missing));
        }
    }

    protected static function isEmpty($value)
    {
        if (is_array($value)) {
            return count($value) === 0;
        }
        return $value === null || $value === '' || $value === 0 || $value === '0';
    }
}

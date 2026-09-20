<?php

namespace app\common\service\cms;

/**
 * 公共布局组件固定字段注册表。
 */
class LayoutSchemaRegistry
{
    public static function fields($type)
    {
        $schemas = self::schemas();
        if (!isset($schemas[$type])) {
            throw new \InvalidArgumentException('未注册的公共布局类型：' . $type);
        }
        return $schemas[$type];
    }

    public static function sanitize($type, array $input)
    {
        $fields = self::fields($type);
        foreach ($input as $name => $value) {
            if (!isset($fields[$name])) {
                throw new \InvalidArgumentException('公共布局不支持字段：' . $name);
            }
        }
        $result = [];
        foreach ($fields as $name => $definition) {
            $value = array_key_exists($name, $input)
                ? $input[$name]
                : (isset($definition['default']) ? $definition['default'] : null);
            $result[$name] = self::sanitizeValue($definition, $value);
        }
        return $result;
    }

    public static function sanitizeField($type, $name, $value)
    {
        $fields = self::fields($type);
        if (!isset($fields[$name])) {
            throw new \InvalidArgumentException('公共布局不支持字段：' . $name);
        }
        return self::sanitizeValue($fields[$name], $value);
    }

    protected static function sanitizeValue(array $definition, $value)
    {
        if ($definition['type'] === 'boolean') {
            return in_array($value, [1, '1', true, 'true', 'on'], true) ? 1 : 0;
        }
        if ($definition['type'] === 'number') {
            $number = (int)$value;
            if (isset($definition['min'])) { $number = max((int)$definition['min'], $number); }
            if (isset($definition['max'])) { $number = min((int)$definition['max'], $number); }
            return $number;
        }
        if ($definition['type'] === 'select') {
            $value = (string)$value;
            $options = isset($definition['options']) && is_array($definition['options']) ? $definition['options'] : [];
            if (!array_key_exists($value, $options)) {
                throw new \InvalidArgumentException('公共布局选项值不合法：' . $value);
            }
            return $value;
        }
        if ($definition['type'] === 'text') {
            $value = trim(strip_tags((string)$value));
            $maxLength = isset($definition['max_length']) ? max(1, (int)$definition['max_length']) : 0;
            if ($maxLength > 0) {
                if (function_exists('mb_substr')) {
                    $value = mb_substr($value, 0, $maxLength, 'UTF-8');
                } else {
                    $characters = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY);
                    if (is_array($characters)) {
                        $value = implode('', array_slice($characters, 0, $maxLength));
                    } else {
                        $value = substr($value, 0, $maxLength);
                    }
                }
            }
            return $value;
        }
        throw new \InvalidArgumentException('未支持的公共布局字段类型：' . $definition['type']);
    }

    protected static function schemas()
    {
        return [
            'header' => [
                'show_logo' => ['title' => '显示 Logo', 'type' => 'boolean', 'default' => 1],
                'show_navigation' => ['title' => '显示导航', 'type' => 'boolean', 'default' => 1],
                'show_phone' => ['title' => '显示服务热线', 'type' => 'boolean', 'default' => 1],
                'show_search' => ['title' => '显示搜索入口', 'type' => 'boolean', 'default' => 1],
                'show_online_service' => ['title' => '显示在线咨询', 'type' => 'boolean', 'default' => 1],
                'nav_active_background_color' => ['title' => '导航 Hover / 选中高亮颜色', 'type' => 'text', 'default' => '#f48101', 'max_length' => 20],
                'hotline_badge_text' => ['title' => '急单专线文字', 'type' => 'text', 'default' => '急单专线', 'max_length' => 8],
                'hotline_badge_top_font_size' => ['title' => '急单专线顶部状态字号', 'type' => 'number', 'default' => 16, 'min' => 10, 'max' => 28],
                'hotline_badge_scrolled_font_size' => ['title' => '急单专线滚动状态字号', 'type' => 'number', 'default' => 13, 'min' => 9, 'max' => 24],
                'hotline_icon' => ['title' => '图标', 'type' => 'text', 'default' => '', 'max_length' => 255],
                'hotline_icon_top_size' => ['title' => '图标顶部状态大小', 'type' => 'number', 'default' => 49, 'min' => 24, 'max' => 80],
                'hotline_icon_scrolled_size' => ['title' => '图标滚动状态大小', 'type' => 'number', 'default' => 39, 'min' => 20, 'max' => 64],
                'hotline_number_top_font_size' => ['title' => '电话号码顶部状态字号', 'type' => 'number', 'default' => 34, 'min' => 20, 'max' => 56],
                'hotline_number_scrolled_font_size' => ['title' => '电话号码滚动状态字号', 'type' => 'number', 'default' => 26, 'min' => 16, 'max' => 44],
                'hotline_number_font_weight' => [
                    'title' => '电话号码字重',
                    'type' => 'select',
                    'default' => '700',
                    'options' => ['400' => '常规 400', '500' => '中等 500', '600' => '半粗 600', '700' => '加粗 700', '800' => '特粗 800'],
                ],
                'hotline_background_color' => ['title' => '电话区背景色', 'type' => 'text', 'default' => '#ef202d', 'max_length' => 20],
                'hotline_number_color' => ['title' => '电话号码颜色', 'type' => 'text', 'default' => '#ffd65a', 'max_length' => 20],
                'hotline_badge_background_color' => ['title' => '急单专线背景色', 'type' => 'text', 'default' => '#ffb918', 'max_length' => 20],
                'hotline_badge_text_color' => ['title' => '急单专线文字颜色', 'type' => 'text', 'default' => '#333333', 'max_length' => 20],
                'hotline_top_width' => ['title' => '电话区顶部状态宽度', 'type' => 'number', 'default' => 400, 'min' => 240, 'max' => 520],
                'hotline_scrolled_width' => ['title' => '电话区滚动状态宽度', 'type' => 'number', 'default' => 320, 'min' => 220, 'max' => 460],
                'hotline_top_offset_y' => ['title' => '电话区顶部状态整体上下偏移', 'type' => 'number', 'default' => 0, 'min' => -40, 'max' => 80],
                'hotline_scrolled_offset_y' => ['title' => '电话区滚动状态整体上下偏移', 'type' => 'number', 'default' => 0, 'min' => -40, 'max' => 80],
                'logo_top_width' => ['title' => 'Logo 顶部状态宽度', 'type' => 'number', 'default' => 212, 'min' => 80, 'max' => 320],
                'logo_top_height' => ['title' => 'Logo 顶部状态高度', 'type' => 'number', 'default' => 45, 'min' => 24, 'max' => 80],
                'logo_scrolled_width' => ['title' => 'Logo 滚动状态宽度', 'type' => 'number', 'default' => 180, 'min' => 80, 'max' => 320],
                'logo_scrolled_height' => ['title' => 'Logo 滚动状态高度', 'type' => 'number', 'default' => 38, 'min' => 24, 'max' => 80],
                'logo_keep_ratio' => ['title' => '保持 Logo 原始比例', 'type' => 'boolean', 'default' => 1],
                'layout_mode' => [
                    'title' => '页头排版模式',
                    'type' => 'select',
                    'default' => 'default',
                    'options' => [
                        'default' => '默认（当前排版）',
                        'balanced' => '适中（左右适度收缩）',
                        'compact' => '紧凑（左右明显收缩）',
                    ],
                ],
            ],
            'footer' => [
                'show_company' => ['title' => '显示企业信息', 'type' => 'boolean', 'default' => 1],
                'show_contact' => ['title' => '显示联系方式', 'type' => 'boolean', 'default' => 1],
                'show_navigation' => ['title' => '显示快捷导航', 'type' => 'boolean', 'default' => 1],
                'show_qrcode' => ['title' => '显示二维码', 'type' => 'boolean', 'default' => 1],
                'show_beian' => ['title' => '显示备案信息', 'type' => 'boolean', 'default' => 1],
            ],
            'search' => [
                'enabled' => ['title' => '显示热搜', 'type' => 'boolean', 'default' => 1],
                'max_items' => ['title' => '最大显示数量', 'type' => 'number', 'default' => 8, 'min' => 1, 'max' => 50],
            ],
            'floating_service' => [
                'show_online_consult' => ['title' => '显示在线咨询', 'type' => 'boolean', 'default' => 1],
                'show_online_message' => ['title' => '显示在线留言', 'type' => 'boolean', 'default' => 1],
                'show_wechat_consult' => ['title' => '显示微信咨询', 'type' => 'boolean', 'default' => 1],
                'show_back_top' => ['title' => '显示返回顶部', 'type' => 'boolean', 'default' => 1],
            ],
            'mobile_toolbar' => [
                'show_home' => ['title' => '显示首页入口', 'type' => 'boolean', 'default' => 1],
                'show_product' => ['title' => '显示产品入口', 'type' => 'boolean', 'default' => 1],
                'show_phone' => ['title' => '显示电话入口', 'type' => 'boolean', 'default' => 1],
                'show_inquiry' => ['title' => '显示咨询入口', 'type' => 'boolean', 'default' => 1],
            ],
        ];
    }
}

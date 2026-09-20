<?php

namespace app\common\service\cms;

/**
 * 后台首页模块编辑字段 Schema Registry。
 *
 * Schema 是后台编辑权限边界：未声明字段即使被人工构造 POST，也不会进入保存 patch。
 * hero 不在此处管理，继续由独立首页 Banner 编辑器维护。
 */
class HomeSectionEditorSchema
{
    protected $schemas = [
        'about' => [
            'fields' => [
                'title', 'mobile_title', 'subtitle', 'mobile_subtitle', 'content', 'mobile_background_image', 'more_url',
                'config_social_icon_1', 'config_social_text_1', 'config_social_url_1',
                'config_social_icon_2', 'config_social_text_2', 'config_social_url_2',
                'config_media_items',
                'pc_visible', 'mobile_visible', 'weigh', 'status',
            ],
            'config_fields' => [
                'config_social_icon_1', 'config_social_text_1', 'config_social_url_1',
                'config_social_icon_2', 'config_social_text_2', 'config_social_url_2',
                'config_media_items',
            ],
            'reference_type' => '',
        ],
        'products' => [
            'fields' => [
                'title', 'mobile_title', 'subtitle', 'mobile_subtitle', 'more_text', 'more_url',
                'config_other_title', 'config_other_summary', 'config_other_url',
                'pc_display_count', 'mobile_display_count',
                'pc_visible', 'mobile_visible', 'weigh', 'status',
            ],
            'config_fields' => ['config_other_title', 'config_other_summary', 'config_other_url'],
            'reference_type' => 'product',
        ],
        'service' => [
            'fields' => [
                'title', 'mobile_title', 'subtitle', 'mobile_subtitle', 'background_image',
                'more_text', 'more_url', 'config_metrics',
                'pc_visible', 'mobile_visible', 'weigh', 'status',
            ],
            'config_fields' => ['config_metrics'],
            'reference_type' => '',
        ],
        'workshop' => [
            'fields' => [
                'title', 'mobile_title', 'subtitle', 'mobile_subtitle', 'config_items',
                'pc_visible', 'mobile_visible', 'weigh', 'status',
            ],
            'config_fields' => ['config_items'],
            'reference_type' => '',
        ],
        'cases' => [
            'fields' => [
                'title', 'mobile_title', 'subtitle', 'mobile_subtitle', 'more_text', 'more_url',
                'pc_display_count', 'mobile_display_count',
                'pc_visible', 'mobile_visible', 'weigh', 'status',
            ],
            'config_fields' => [],
            'reference_type' => 'case',
        ],
        'advantages' => [
            'fields' => [
                'title', 'mobile_title', 'subtitle', 'mobile_subtitle', 'config_items',
                'pc_visible', 'mobile_visible', 'weigh', 'status',
            ],
            'config_fields' => ['config_items'],
            'reference_type' => '',
        ],
        'news' => [
            'fields' => [
                'title', 'mobile_title', 'subtitle', 'mobile_subtitle', 'more_text', 'more_url',
                'config_left_title', 'config_left_url', 'config_right_title', 'config_right_url',
                'pc_display_count', 'mobile_display_count',
                'pc_visible', 'mobile_visible', 'weigh', 'status',
            ],
            'config_fields' => ['config_left_title', 'config_left_url', 'config_right_title', 'config_right_url'],
            'reference_type' => 'article',
        ],
        'company' => [
            'fields' => [
                'title', 'mobile_title', 'subtitle', 'mobile_subtitle', 'content', 'background_image', 'mobile_background_image', 'more_text', 'more_url',
                'config_media_items', 'config_metrics', 'pc_visible', 'mobile_visible', 'weigh', 'status',
            ],
            'config_fields' => ['config_media_items', 'config_metrics'],
            'reference_type' => '',
        ],
        'culture' => [
            'fields' => [
                'title', 'mobile_title', 'subtitle', 'mobile_subtitle',
                'background_image', 'mobile_background_image', 'config_items',
                'pc_visible', 'mobile_visible', 'weigh', 'status',
            ],
            'config_fields' => ['config_items'],
            'reference_type' => '',
        ],
    ];

    public function has($sectionKey)
    {
        return isset($this->schemas[$this->key($sectionKey)]);
    }

    /**
     * 兼容上一版 about-only 命名；当前含义已变为“是否使用 Schema 编辑器”。
     */
    public function isCompact($sectionKey)
    {
        return $this->has($sectionKey);
    }

    public function fields($sectionKey)
    {
        $schema = $this->schema($sectionKey);
        return isset($schema['fields']) && is_array($schema['fields']) ? $schema['fields'] : [];
    }

    public function configFields($sectionKey)
    {
        $schema = $this->schema($sectionKey);
        return isset($schema['config_fields']) && is_array($schema['config_fields']) ? $schema['config_fields'] : [];
    }

    public function referenceType($sectionKey)
    {
        $schema = $this->schema($sectionKey);
        return isset($schema['reference_type']) ? trim((string)$schema['reference_type']) : '';
    }

    public function hasReferences($sectionKey)
    {
        return $this->referenceType($sectionKey) !== '';
    }

    /**
     * 只保留请求中实际存在且当前 Schema 允许的字段。
     * 不补默认值，避免隐藏字段在 patch 保存时被清空。
     */
    public function filter($sectionKey, array $params)
    {
        if (!$this->has($sectionKey)) {
            return $params;
        }
        $allowed = array_flip($this->fields($sectionKey));
        $filtered = [];
        foreach ($params as $key => $value) {
            if (isset($allowed[$key])) {
                $filtered[$key] = $value;
            }
        }
        return $filtered;
    }

    protected function schema($sectionKey)
    {
        $key = $this->key($sectionKey);
        return isset($this->schemas[$key]) ? $this->schemas[$key] : [];
    }

    protected function key($sectionKey)
    {
        return trim((string)$sectionKey);
    }
}

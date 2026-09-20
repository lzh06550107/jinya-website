<?php

namespace app\common\service\cms;

if (!class_exists(__NAMESPACE__ . '\\HomeSectionDefaults', false)) {
    require_once __DIR__ . '/HomeSectionDefaults.php';
}

/**
 * 首页模块后台统一保存契约。
 *
 * 只处理：Schema 过滤、提交值规范化、结构化配置 patch 提取、历史 config_json 合并。
 * 不查询数据库、不渲染模板、不处理克隆资源。
 */
class HomeSectionConfigService
{
    private $schema;
    private $codec;

    public function __construct(HomeSectionEditorSchema $schema = null, StructuredConfigCodec $codec = null)
    {
        $this->schema = $schema ?: new HomeSectionEditorSchema();
        $this->codec = $codec ?: new StructuredConfigCodec();
    }

    public function preparePatch($sectionKey, array $submitted, $existingConfigJson = '')
    {
        $sectionKey = trim((string)$sectionKey);
        if (!$this->schema->has($sectionKey)) {
            throw new \InvalidArgumentException('Unsupported home section editor: ' . $sectionKey);
        }

        $filtered = $this->schema->filter($sectionKey, $submitted);
        $filtered = HomeSectionDefaults::applySubmitted($sectionKey, $filtered);
        $fields = $this->normalizeSubmitted($filtered);
        $fields = $this->codec->extractHomePatch($fields);

        $existing = $this->codec->decode($existingConfigJson);
        $patch = $this->codec->decode(isset($fields['config_json']) ? $fields['config_json'] : '');
        $fields['config_json'] = json_encode(
            array_merge($existing, $patch),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        return $fields;
    }

    private function normalizeSubmitted(array $submitted)
    {
        $fields = [];
        $stringFields = [
            'title', 'mobile_title', 'subtitle', 'mobile_subtitle',
            'description', 'mobile_description', 'content', 'image',
            'background_image', 'mobile_background_image', 'more_text', 'more_url',
            'config_social_icon_1', 'config_social_text_1',
            'config_social_icon_2', 'config_social_text_2',
            'config_left_title', 'config_left_url', 'config_right_title', 'config_right_url',
            'config_other_title', 'config_other_summary', 'config_other_url',
            'config_video_url', 'config_video_poster',
            'config_metrics_text', 'config_items_text', 'config_social_links_text',
        ];
        foreach ($stringFields as $field) {
            if (!array_key_exists($field, $submitted)) {
                continue;
            }
            $fields[$field] = is_array($submitted[$field]) || is_object($submitted[$field])
                ? ''
                : trim((string)$submitted[$field]);
        }

        if (array_key_exists('content', $fields)) {
            $fields['content'] = HtmlSanitizer::clean($fields['content']);
        }

        if (array_key_exists('config_metrics', $submitted)) {
            $fields['config_metrics'] = is_array($submitted['config_metrics'])
                ? $submitted['config_metrics']
                : [];
        }

        if (array_key_exists('config_items', $submitted)) {
            $fields['config_items'] = is_array($submitted['config_items'])
                ? $submitted['config_items']
                : [];
        }

        if (array_key_exists('config_media_items', $submitted)) {
            $fields['config_media_items'] = is_array($submitted['config_media_items'])
                ? $submitted['config_media_items']
                : [];
        }

        foreach (['pc_visible', 'mobile_visible'] as $field) {
            if (array_key_exists($field, $submitted)) {
                $fields[$field] = !empty($submitted[$field]) ? 1 : 0;
            }
        }

        foreach (['pc_display_count', 'mobile_display_count', 'weigh'] as $field) {
            if (array_key_exists($field, $submitted)) {
                $fields[$field] = (int)$submitted[$field];
            }
        }

        if (array_key_exists('status', $submitted)) {
            $fields['status'] = $submitted['status'] === 'hidden' ? 'hidden' : 'normal';
        }

        return $fields;
    }
}

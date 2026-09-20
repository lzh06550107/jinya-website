<?php

namespace app\common\service\cms;

/**
 * Dedicated whitelist codec for the Jinya label-page structured blocks.
 * Layout HTML/CSS/JS remains template-owned; only operational content is stored.
 */
class LabelPageBlockConfigCodec
{
    private $scalarMap = [
        'label_highlight' => 'highlight',
        'label_lead_after' => 'lead_after',
        'label_note' => 'note',
        'label_consult_text' => 'consult_text',
        'label_consult_url' => 'consult_url',
        'label_phone' => 'phone',
        'label_video_url' => 'video_url',
        'label_tabs' => 'tabs',
        'label_tagline' => 'tagline',
        'label_button_text' => 'button_text',
        'label_button_url' => 'button_url',
        'label_secondary_title' => 'secondary_title',
    ];

    private $itemFields = [
        'title', 'text', 'image', 'image_top', 'image_bottom', 'url', 'value',
        'subtitle', 'badge', 'group',
        'mobile_title', 'mobile_text', 'mobile_image', 'mobile_image_top',
        'mobile_image_bottom', 'mobile_subtitle', 'mobile_badge', 'mobile_url',
        'pc_visible', 'mobile_visible',
    ];

    public function extract(array $params)
    {
        $extra = [];
        foreach ($this->scalarMap as $input => $key) {
            if (!array_key_exists($input, $params)) {
                continue;
            }
            $extra[$key] = $this->scalar($params[$input]);
        }

        $rows = isset($params['label_items']) && is_array($params['label_items']) ? $params['label_items'] : [];
        if (isset($params['block_key']) && (string)$params['block_key'] === 'label_elements') {
            if (!class_exists(__NAMESPACE__ . '\\LabelElementsConfigSanitizer', false)) {
                require_once __DIR__ . '/LabelElementsConfigSanitizer.php';
            }
            $rows = LabelElementsConfigSanitizer::sanitizeItems($rows);
        }
        $extra['items'] = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $item = [];
            $hasValue = false;
            foreach ($this->itemFields as $field) {
                if ($field === 'pc_visible' || $field === 'mobile_visible') {
                    $item[$field] = isset($row[$field]) && (string)$row[$field] === '0' ? 0 : 1;
                    continue;
                }
                $value = array_key_exists($field, $row) ? $this->scalar($row[$field]) : '';
                $item[$field] = $value;
                if ($value !== '') {
                    $hasValue = true;
                }
            }
            if ($hasValue) {
                $extra['items'][] = $item;
            }
        }

        foreach (array_keys($params) as $key) {
            if (strpos($key, 'label_') === 0 || strpos($key, 'extra_') === 0) {
                unset($params[$key]);
            }
        }
        $params['extra_json'] = json_encode($extra, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $params;
    }

    public function editorData($json)
    {
        $decoded = json_decode((string)$json, true);
        $decoded = is_array($decoded) ? $decoded : [];
        $result = ['items' => isset($decoded['items']) && is_array($decoded['items']) ? $decoded['items'] : []];
        foreach ($this->scalarMap as $input => $key) {
            $result[$key] = isset($decoded[$key]) && !is_array($decoded[$key]) ? (string)$decoded[$key] : '';
        }
        return $result;
    }

    public function scalarInputs()
    {
        return $this->scalarMap;
    }

    public function itemFields()
    {
        return $this->itemFields;
    }

    private function scalar($value)
    {
        if (is_array($value) || is_object($value)) {
            return '';
        }
        return trim((string)$value);
    }
}

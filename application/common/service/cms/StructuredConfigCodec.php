<?php

namespace app\common\service\cms;

/**
 * 将后台受控字段转换为前台使用的结构化 JSON。
 *
 * 该类只接受固定白名单字段，不接收原始 JSON、HTML 页面、CSS 或 JavaScript。
 */
class StructuredConfigCodec
{
    protected $homeScalarMap = [
        'config_social_icon_1' => 'social_icon_1',
        'config_social_text_1' => 'social_text_1',
        'config_social_url_1' => 'social_url_1',
        'config_social_icon_2' => 'social_icon_2',
        'config_social_text_2' => 'social_text_2',
        'config_social_url_2' => 'social_url_2',
        'config_left_title' => 'left_title',
        'config_right_title' => 'right_title',
        'config_left_url' => 'left_url',
        'config_right_url' => 'right_url',
        'config_other_title' => 'other_title',
        'config_other_summary' => 'other_summary',
        'config_other_url' => 'other_url',
        'config_video_url' => 'video_url',
        'config_video_poster' => 'video_poster',
    ];

    protected $blockScalarMap = [
        'extra_mobile_content' => 'mobile_content',
        'extra_map_address' => 'address',
        'extra_latitude' => 'latitude',
        'extra_longitude' => 'longitude',
        'extra_media_url' => 'media_url',
        'extra_mobile_media_url' => 'mobile_media_url',
    ];

    public function extractHome(array $params)
    {
        $config = [];
        foreach ($this->homeScalarMap as $input => $key) {
            // 社交图标是 home.about 新增的可选配置；旧 full editor 未暴露该字段时不合成空键。
            if (($input === 'config_social_icon_1' || $input === 'config_social_icon_2') && !array_key_exists($input, $params)) {
                continue;
            }
            $config[$key] = isset($params[$input]) ? trim((string)$params[$input]) : '';
        }
        $config['metrics'] = array_key_exists('config_metrics', $params)
            ? $this->normalizeRows($params['config_metrics'], ['title', 'value', 'unit', 'text', 'icon', 'prefix'])
            : $this->parseRows(isset($params['config_metrics_text']) ? $params['config_metrics_text'] : '', ['title', 'value', 'unit', 'text', 'icon', 'prefix']);
        $config['items'] = array_key_exists('config_items', $params)
            ? $this->normalizeRows($params['config_items'], ['title', 'text', 'image', 'icon', 'url', 'subtitle', 'mobile_image', 'pc_visible', 'mobile_visible'])
            : $this->parseRows(isset($params['config_items_text']) ? $params['config_items_text'] : '', ['title', 'text', 'image', 'icon', 'url', 'subtitle', 'mobile_image', 'pc_visible', 'mobile_visible']);
        $config['media_items'] = array_key_exists('config_media_items', $params)
            ? $this->normalizeRows($params['config_media_items'], ['title', 'image', 'video_url', 'url'])
            : $this->parseRows(isset($params['config_media_items_text']) ? $params['config_media_items_text'] : '', ['title', 'image', 'video_url', 'url']);
        $config['social_links'] = $this->parseRows(isset($params['config_social_links_text']) ? $params['config_social_links_text'] : '', ['title', 'url', 'image']);

        foreach (array_keys($params) as $key) {
            if (strpos($key, 'config_') === 0) {
                unset($params[$key]);
            }
        }
        $params['config_json'] = $this->encode($config);
        return $params;
    }

    /**
     * 仅提取请求中实际提交的首页结构化配置。
     *
     * 与 extractHome() 的全量语义不同：缺失的 config_* 字段不会被补为空值，
     * 供 home.about 等精简编辑器以 patch/merge 方式保存历史 config_json。
     */
    public function extractHomePatch(array $params)
    {
        $config = [];
        $consumed = [];

        foreach ($this->homeScalarMap as $input => $key) {
            if (!array_key_exists($input, $params)) {
                continue;
            }
            $config[$key] = is_array($params[$input]) || is_object($params[$input])
                ? ''
                : trim((string)$params[$input]);
            $consumed[] = $input;
        }

        if (array_key_exists('config_metrics', $params)) {
            $config['metrics'] = $this->normalizeRows($params['config_metrics'], ['title', 'value', 'unit', 'text', 'icon', 'prefix']);
            $consumed[] = 'config_metrics';
            if (array_key_exists('config_metrics_text', $params)) {
                $consumed[] = 'config_metrics_text';
            }
        }

        if (array_key_exists('config_items', $params)) {
            $config['items'] = $this->normalizeRows($params['config_items'], ['title', 'text', 'image', 'icon', 'url', 'subtitle', 'mobile_image', 'pc_visible', 'mobile_visible']);
            $consumed[] = 'config_items';
            if (array_key_exists('config_items_text', $params)) {
                $consumed[] = 'config_items_text';
            }
        }

        $rowFields = [
            'config_social_links_text' => ['social_links', ['title', 'url', 'image']],
        ];
        if (!array_key_exists('config_metrics', $params)) {
            $rowFields = ['config_metrics_text' => ['metrics', ['title', 'value', 'unit', 'text', 'icon', 'prefix']]] + $rowFields;
        }
        if (!array_key_exists('config_items', $params)) {
            $rowFields = ['config_items_text' => ['items', ['title', 'text', 'image', 'icon', 'url', 'subtitle', 'mobile_image', 'pc_visible', 'mobile_visible']]] + $rowFields;
        }
        foreach ($rowFields as $input => $definition) {
            if (!array_key_exists($input, $params)) {
                continue;
            }
            $config[$definition[0]] = $this->parseRows($params[$input], $definition[1]);
            $consumed[] = $input;
        }

        if (array_key_exists('config_media_items', $params)) {
            $config['media_items'] = $this->normalizeRows($params['config_media_items'], ['title', 'image', 'video_url', 'url']);
            $consumed[] = 'config_media_items';
            if (array_key_exists('config_media_items_text', $params)) {
                $consumed[] = 'config_media_items_text';
            }
        } elseif (array_key_exists('config_media_items_text', $params)) {
            $config['media_items'] = $this->parseRows($params['config_media_items_text'], ['title', 'image', 'video_url', 'url']);
            $consumed[] = 'config_media_items_text';
        }

        foreach (array_unique($consumed) as $key) {
            unset($params[$key]);
        }
        $params['config_json'] = $this->encode($config);
        return $params;
    }

    public function extractPageBlock(array $params)
    {
        $extra = [];
        foreach ($this->blockScalarMap as $input => $key) {
            $extra[$key] = isset($params[$input]) ? trim((string)$params[$input]) : '';
        }
        $rows = $this->parseRows(
            isset($params['extra_items_text']) ? $params['extra_items_text'] : '',
            ['label', 'value', 'image', 'url', 'year', 'media_url', 'mobile_image', 'mobile_label', 'mobile_value', 'mobile_url', 'mobile_media_url']
        );
        $extra['items'] = [];
        foreach ($rows as $row) {
            $extra['items'][] = [
                'label' => $row['label'],
                'title' => $row['label'],
                'value' => $row['value'],
                'text' => $row['value'],
                'image' => $row['image'],
                'url' => $row['url'],
                'year' => $row['year'],
                'media_url' => $row['media_url'],
                'mobile_image' => isset($row['mobile_image']) ? $row['mobile_image'] : '',
                'mobile_label' => isset($row['mobile_label']) ? $row['mobile_label'] : '',
                'mobile_value' => isset($row['mobile_value']) ? $row['mobile_value'] : '',
                'mobile_url' => isset($row['mobile_url']) ? $row['mobile_url'] : '',
                'mobile_media_url' => isset($row['mobile_media_url']) ? $row['mobile_media_url'] : '',
            ];
        }
        foreach (array_keys($params) as $key) {
            if (strpos($key, 'extra_') === 0) {
                unset($params[$key]);
            }
        }
        $params['extra_json'] = $this->encode($extra);
        return $params;
    }

    public function decode($json)
    {
        $decoded = json_decode((string)$json, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function normalizeHome(array $config)
    {
        if (!array_key_exists('metrics', $config) && isset($config['manager_value'])) {
            $config['metrics'] = [
                ['value' => $this->value($config, 'manager_value'), 'unit' => $this->value($config, 'manager_unit'), 'text' => $this->value($config, 'manager_text'), 'icon' => $this->value($config, 'manager_icon'), 'prefix' => $this->value($config, 'manager_prefix')],
                ['value' => $this->value($config, 'team_value'), 'unit' => $this->value($config, 'team_unit'), 'text' => $this->value($config, 'team_text'), 'icon' => $this->value($config, 'team_icon'), 'prefix' => $this->value($config, 'team_prefix')],
                ['value' => $this->value($config, 'warranty_value'), 'unit' => $this->value($config, 'warranty_unit'), 'text' => $this->value($config, 'warranty_text'), 'icon' => $this->value($config, 'warranty_icon'), 'prefix' => $this->value($config, 'warranty_prefix')],
                ['value' => $this->value($config, 'response_value'), 'unit' => $this->value($config, 'response_unit'), 'text' => $this->value($config, 'response_text'), 'icon' => $this->value($config, 'response_icon'), 'prefix' => $this->value($config, 'response_prefix')],
            ];
            $config['metrics'] = array_values(array_filter($config['metrics'], function ($row) {
                return implode('', $row) !== '';
            }));
        }
        if (!array_key_exists('items', $config) && isset($config['title1'])) {
            $config['items'] = [];
            for ($index = 1; $index <= 12; $index++) {
                $title = $this->value($config, 'title' . $index);
                if ($title === '') {
                    continue;
                }
                $config['items'][] = [
                    'title' => $title,
                    'text' => $this->value($config, 'text' . $index),
                    'image' => $this->value($config, 'image' . $index),
                    'icon' => $this->value($config, 'icon' . $index),
                    'url' => $this->value($config, 'url' . $index),
                    'subtitle' => $this->value($config, 'subtitle' . $index),
                ];
            }
        }
        if (!array_key_exists('media_items', $config) && (!empty($config['video_url']) || !empty($config['video_poster']))) {
            $config['media_items'] = [[
                'title' => $this->value($config, 'video_title'),
                'image' => $this->value($config, 'video_poster'),
                'video_url' => $this->value($config, 'video_url'),
                'url' => $this->value($config, 'video_link_url'),
            ]];
        }
        foreach (['metrics', 'items', 'media_items', 'social_links'] as $key) {
            if (!isset($config[$key]) || !is_array($config[$key])) {
                $config[$key] = [];
            }
        }
        return $config;
    }

    public function formatHomeRows($json, $key, array $columns)
    {
        $config = $this->normalizeHome($this->decode($json));
        return $this->formatRows(isset($config[$key]) && is_array($config[$key]) ? $config[$key] : [], $columns);
    }

    public function formatBlockRows($json)
    {
        $extra = $this->decode($json);
        $items = isset($extra['items']) && is_array($extra['items']) ? $extra['items'] : [];
        return $this->formatRows($items, ['label', 'value', 'image', 'url', 'year', 'media_url', 'mobile_image', 'mobile_label', 'mobile_value', 'mobile_url', 'mobile_media_url']);
    }

    public function scalar($json, $key, $home = false)
    {
        $data = $this->decode($json);
        if ($home) {
            $data = $this->normalizeHome($data);
        }
        return isset($data[$key]) && !is_array($data[$key]) ? (string)$data[$key] : '';
    }

    protected function normalizeRows($rows, array $columns)
    {
        if (!is_array($rows)) {
            return [];
        }
        $normalized = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $item = [];
            $hasValue = false;
            foreach ($columns as $column) {
                $value = isset($row[$column]) && !is_array($row[$column]) && !is_object($row[$column])
                    ? trim((string)$row[$column])
                    : '';
                $item[$column] = $value;
                $hasValue = $hasValue || $value !== '';
            }
            if ($hasValue) {
                $normalized[] = $item;
            }
        }
        return $normalized;
    }

    protected function parseRows($text, array $columns)
    {
        $rows = [];
        $lines = preg_split('/\r\n|\r|\n/', (string)$text);
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $values = str_getcsv($line, '|');
            $row = [];
            $hasValue = false;
            foreach ($columns as $index => $column) {
                $value = isset($values[$index]) ? trim((string)$values[$index]) : '';
                $row[$column] = $value;
                $hasValue = $hasValue || $value !== '';
            }
            if ($hasValue) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    protected function formatRows(array $rows, array $columns)
    {
        $lines = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $values = [];
            $hasValue = false;
            foreach ($columns as $column) {
                $value = isset($row[$column]) && !is_array($row[$column]) ? trim((string)$row[$column]) : '';
                $values[] = str_replace(["\r", "\n", '|'], [' ', ' ', '／'], $value);
                $hasValue = $hasValue || $value !== '';
            }
            if ($hasValue) {
                $lines[] = implode('|', $values);
            }
        }
        return implode("\n", $lines);
    }

    protected function encode(array $value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected function value(array $data, $key)
    {
        return isset($data[$key]) && !is_array($data[$key]) ? trim((string)$data[$key]) : '';
    }
}

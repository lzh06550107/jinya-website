<?php

namespace app\common\service\cms;

class BannerHighlightCodec
{
    public function decode($json)
    {
        if (is_array($json)) {
            return $this->normalize($json);
        }
        $json = trim((string)$json);
        if ($json === '') {
            return [];
        }
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $this->normalize($decoded) : [];
    }

    public function normalize(array $items)
    {
        $out = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $text = trim(isset($item['text']) && !is_array($item['text']) ? (string)$item['text'] : '');
            if ($text === '') {
                continue;
            }
            $out[] = [
                'icon' => trim(isset($item['icon']) && !is_array($item['icon']) ? (string)$item['icon'] : ''),
                'text' => $text,
                'pc_visible' => $this->visible(isset($item['pc_visible']) ? $item['pc_visible'] : null),
                'mobile_visible' => $this->visible(isset($item['mobile_visible']) ? $item['mobile_visible'] : null),
            ];
            if (count($out) >= 5) {
                break;
            }
        }
        return $out;
    }

    private function visible($value)
    {
        if ($value === true || $value === 1 || $value === '1') {
            return 1;
        }
        $value = strtolower(trim((string)$value));
        return in_array($value, ['true', 'yes', 'on'], true) ? 1 : 0;
    }

    public function encode(array $items)
    {
        $items = $this->normalize($items);
        if (!$items) {
            return '[]';
        }
        $flags = 0;
        if (defined('JSON_UNESCAPED_UNICODE')) {
            $flags |= JSON_UNESCAPED_UNICODE;
        }
        if (defined('JSON_UNESCAPED_SLASHES')) {
            $flags |= JSON_UNESCAPED_SLASHES;
        }
        $encoded = json_encode($items, $flags);
        return $encoded === false ? '[]' : $encoded;
    }

    public function normalizeJson($json)
    {
        return $this->encode($this->decode($json));
    }

    public function forTerminal(array $items, $terminal)
    {
        $items = $this->normalize($items);
        $field = (string)$terminal === 'mobile' ? 'mobile_visible' : 'pc_visible';
        $out = [];
        foreach ($items as $item) {
            if (!empty($item[$field])) {
                $out[] = $item;
            }
        }
        return $out;
    }
}

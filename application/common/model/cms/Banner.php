<?php

namespace app\common\model\cms;

class Banner extends BaseModel
{
    protected $name = 'cms_banner';

    protected $append = ['status_text', 'highlight_icon_1', 'highlight_icon_2', 'highlight_icon_3'];

    public function getHighlightIcon1Attr($value, $data)
    {
        return $this->highlightIconAt($data, 0);
    }

    public function getHighlightIcon2Attr($value, $data)
    {
        return $this->highlightIconAt($data, 1);
    }

    public function getHighlightIcon3Attr($value, $data)
    {
        return $this->highlightIconAt($data, 2);
    }

    private function highlightIconAt(array $data, $index)
    {
        $json = isset($data['highlights_json']) ? $data['highlights_json'] : '[]';
        $isHomeHero = isset($data['page_key'], $data['position'])
            && (string)$data['page_key'] === 'home'
            && (string)$data['position'] === 'hero';
        return (new \app\common\service\cms\BannerHighlightCodec())->iconAt($json, $index, $isHomeHero);
    }

    public function getStatusTextAttr($value, $data)
    {
        $list = $this->getNormalStatusList();
        return isset($list[$data['status']]) ? $list[$data['status']] : $data['status'];
    }

    public function setStartTimeAttr($value)
    {
        return $value === '' || $value === null ? null : (is_numeric($value) ? (int)$value : strtotime($value));
    }

    public function setEndTimeAttr($value)
    {
        return $value === '' || $value === null ? null : (is_numeric($value) ? (int)$value : strtotime($value));
    }
}

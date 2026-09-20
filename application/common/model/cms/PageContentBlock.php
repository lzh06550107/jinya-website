<?php

namespace app\common\model\cms;

use app\common\service\cms\HtmlSanitizer;
use app\common\service\cms\StructuredConfigCodec;

class PageContentBlock extends BaseModel
{
    protected $name = 'cms_page_content_block';
    protected $append = ['extra_array', 'extra_mobile_content', 'extra_items_text', 'extra_map_address', 'extra_latitude', 'extra_longitude', 'extra_media_url', 'extra_mobile_media_url'];

    public function setContentAttr($value)
    {
        return HtmlSanitizer::clean($value);
    }

    public function getExtraArrayAttr($value, $data)
    {
        return (new StructuredConfigCodec())->decode(isset($data['extra_json']) ? $data['extra_json'] : '');
    }

    public function getExtraMobileContentAttr($value, $data) { return $this->extraScalar($data, 'mobile_content'); }
    public function getExtraMapAddressAttr($value, $data) { return $this->extraScalar($data, 'address'); }
    public function getExtraLatitudeAttr($value, $data) { return $this->extraScalar($data, 'latitude'); }
    public function getExtraLongitudeAttr($value, $data) { return $this->extraScalar($data, 'longitude'); }
    public function getExtraMediaUrlAttr($value, $data) { return $this->extraScalar($data, 'media_url'); }
    public function getExtraMobileMediaUrlAttr($value, $data) { return $this->extraScalar($data, 'mobile_media_url'); }

    public function getExtraItemsTextAttr($value, $data)
    {
        return (new StructuredConfigCodec())->formatBlockRows(isset($data['extra_json']) ? $data['extra_json'] : '');
    }

    protected function extraScalar(array $data, $key)
    {
        return (new StructuredConfigCodec())->scalar(isset($data['extra_json']) ? $data['extra_json'] : '', $key);
    }
}

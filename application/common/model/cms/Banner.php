<?php

namespace app\common\model\cms;

class Banner extends BaseModel
{
    protected $name = 'cms_banner';

    protected $append = ['status_text'];

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

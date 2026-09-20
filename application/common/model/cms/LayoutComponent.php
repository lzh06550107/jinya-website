<?php

namespace app\common\model\cms;

class LayoutComponent extends BaseModel
{
    protected $name = 'cms_layout_component';

    protected $append = ['config_array', 'status_text'];

    public function getConfigArrayAttr($value, $data)
    {
        $decoded = json_decode(isset($data['config_json']) ? $data['config_json'] : '', true);
        return is_array($decoded) ? $decoded : [];
    }

    public function getStatusTextAttr($value, $data)
    {
        $list = $this->getNormalStatusList();
        return isset($list[$data['status']]) ? $list[$data['status']] : $data['status'];
    }
}

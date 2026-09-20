<?php

namespace app\common\model\cms;

class Navigation extends BaseModel
{
    protected $name = 'cms_navigation';

    protected $append = ['status_text'];

    public function getStatusTextAttr($value, $data)
    {
        $list = $this->getNormalStatusList();
        return isset($list[$data['status']]) ? $list[$data['status']] : $data['status'];
    }

}

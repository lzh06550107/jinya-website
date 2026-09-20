<?php

namespace app\common\validate\cms;

use think\Validate;

class ProductParameter extends Validate
{
    protected $rule = [
        'product_id' => 'require|number|gt:0',
        'parameter_name' => 'require|max:150',
        'parameter_value' => 'require|max:500',
    ];

    protected $message = [
        'product_id.gt' => '缺少产品ID',
        'parameter_name.require' => '请填写参数名称',
        'parameter_value.require' => '请填写参数值',
    ];
}

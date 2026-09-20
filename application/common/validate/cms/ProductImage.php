<?php

namespace app\common\validate\cms;

use think\Validate;

class ProductImage extends Validate
{
    protected $rule = [
        'product_id' => 'require|number|gt:0',
        'image' => 'require',
        'alt' => 'max:255',
        'image_type' => 'in:gallery,color,scene,report',
    ];

    protected $message = [
        'product_id.gt' => '缺少产品ID',
        'image.require' => '请上传图片',
    ];
}

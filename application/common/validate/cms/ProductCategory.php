<?php

namespace app\common\validate\cms;

use think\Validate;

class ProductCategory extends Validate
{
    protected $rule = [
        'name' => 'require|max:100',
        'slug' => 'require|alphaDash|unique:cms_product_category'
    ];

    protected $message = [
        'name.require' => '请填写分类名称',
        'slug.require' => '请填写URL标识'
    ];

    protected $scene = [
        'add' => [],
        'edit' => [],
    ];
}

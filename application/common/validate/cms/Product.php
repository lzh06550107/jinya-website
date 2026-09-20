<?php

namespace app\common\validate\cms;

use think\Validate;

class Product extends Validate
{
    protected $rule = [
        'title' => 'require|max:200',
        'category_id' => 'require|number|gt:0',
        'slug' => 'require|alphaDash|unique:cms_product',
        'summary' => 'max:1000'
    ];

    protected $message = [
        'title.require' => '请填写产品名称',
        'category_id.gt' => '请选择产品分类',
        'slug.require' => '请填写URL标识',
        'slug.unique' => 'URL标识已存在'
    ];

    protected $scene = [
        'add' => [],
        'edit' => [],
    ];
}

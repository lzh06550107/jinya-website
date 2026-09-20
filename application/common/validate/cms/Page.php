<?php

namespace app\common\validate\cms;

use think\Validate;

class Page extends Validate
{
    protected $rule = [
        'title' => 'require|max:200',
        'slug' => 'require|alphaDash|unique:cms_page',
        'content' => 'require'
    ];

    protected $message = [
        'title.require' => '请填写页面标题',
        'slug.require' => '请填写URL标识',
        'content.require' => '请填写页面内容'
    ];

    protected $scene = [
        'add' => [],
        'edit' => [],
    ];
}

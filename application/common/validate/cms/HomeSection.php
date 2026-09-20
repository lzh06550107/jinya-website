<?php

namespace app\common\validate\cms;

use think\Validate;

class HomeSection extends Validate
{
    protected $rule = [
        'section_key' => 'require|alphaDash|unique:cms_home_section',
        'section_name' => 'require',
        'title' => 'require'
    ];

    protected $message = [
        'section_key.require' => '请填写模块标识',
        'section_name.require' => '请填写模块名称',
        'title.require' => '请填写模块标题'
    ];

    protected $scene = [
        'add' => [],
        'edit' => [],
    ];
}

<?php

namespace app\common\validate\cms;

use think\Validate;

class Navigation extends Validate
{
    protected $rule = [
        'title' => 'require|max:100',
        'url' => 'require|max:255'
    ];

    protected $message = [
        'title.require' => '请填写导航名称',
        'url.require' => '请填写链接地址'
    ];

    protected $scene = [
        'add' => [],
        'edit' => [],
    ];
}

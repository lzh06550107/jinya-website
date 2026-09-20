<?php

namespace app\common\validate\cms;

use think\Validate;

class Banner extends Validate
{
    protected $rule = [
        'title' => 'max:150',
        'image' => 'require'
    ];

    protected $message = [
        'image.require' => '请上传Banner图片'
    ];

    protected $scene = [
        'add' => [],
        'edit' => [],
    ];
}

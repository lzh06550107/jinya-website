<?php

namespace app\common\validate\cms;

use think\Validate;

class Article extends Validate
{
    protected $rule = [
        'title' => 'require|max:200',
        'category_id' => 'require|number|gt:0',
        'slug' => 'require|alphaDash|unique:cms_article',
        'content' => 'require'
    ];

    protected $message = [
        'title.require' => '请填写新闻标题',
        'category_id.gt' => '请选择新闻分类',
        'content.require' => '请填写新闻正文'
    ];

    protected $scene = [
        'add' => [],
        'edit' => [],
    ];
}

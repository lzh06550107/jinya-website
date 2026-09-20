<?php

namespace app\common\model\cms;

class ArticleCategory extends BaseModel
{
    protected $name = 'cms_article_category';

    protected $append = ['status_text'];

    public function getStatusTextAttr($value, $data)
    {
        $list = $this->getNormalStatusList();
        return isset($list[$data['status']]) ? $list[$data['status']] : $data['status'];
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'category_id', 'id');
    }

}

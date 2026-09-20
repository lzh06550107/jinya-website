<?php

namespace app\common\model\cms;

class Article extends ContentModel
{
    protected $name = 'cms_article';

    public function category()
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

}

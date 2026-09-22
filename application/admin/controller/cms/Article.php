<?php

namespace app\admin\controller\cms;

use app\common\model\cms\Article as ArticleModel;
use app\common\model\cms\ArticleCategory;

class Article extends Content
{
    protected $modelClass = ArticleModel::class;
    protected $contentType = 'article';
    protected $multiFields = 'weigh,is_recommend,is_top';

    protected function afterCmsInitialize()
    {
        $categories = ArticleCategory::where('status', 'normal')
            ->where('name', 'not in', ['常见问答', '科创美新闻', '新闻动态'])
            ->order('weigh desc,id asc')
            ->column('name', 'id');
        $this->view->assign('categoryList', $categories);
        $this->assignconfig('categoryList', $categories);
    }

    protected function previewPath($row)
    {
        return '/news/' . $row['slug'] . '?preview=1';
    }
}

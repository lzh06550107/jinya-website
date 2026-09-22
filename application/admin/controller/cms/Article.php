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
        $categories = ArticleCategory::where('status', 'normal')->order('weigh desc,id asc')->column('name', 'id');
        $this->view->assign('categoryList', $categories);
        $this->assignconfig('categoryList', $categories);
    }

    /**
     * 新闻 URL 标识由系统维护：
     * - 新增时自动生成一次；
     * - 编辑时忽略任何客户端提交的 slug，保持原 URL 不变。
     */
    protected function preExcludeFields($params)
    {
        $params = parent::preExcludeFields($params);
        unset($params['slug']);

        $action = strtolower((string)$this->request->action());
        if ($action === 'add') {
            $params['slug'] = $this->generateArticleSlug();
        } elseif ($action === 'edit') {
            $id = (int)$this->request->request('ids');
            $row = $id > 0 ? ArticleModel::get($id) : null;
            if ($row && trim((string)$row['slug']) !== '') {
                $params['slug'] = (string)$row['slug'];
            }
        }

        return $params;
    }

    protected function generateArticleSlug()
    {
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $slug = 'news-' . date('Ymd-His') . '-' . mt_rand(1000, 9999);
            if (!ArticleModel::where('slug', $slug)->count()) {
                return $slug;
            }
        }

        return 'news-' . date('Ymd-His') . '-' . str_replace('.', '', uniqid('', true));
    }

    protected function previewPath($row)
    {
        return '/news/' . $row['slug'] . '?preview=1';
    }
}

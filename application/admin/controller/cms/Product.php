<?php

namespace app\admin\controller\cms;

use app\common\model\cms\Product as ProductModel;
use app\common\model\cms\ProductCategory;

class Product extends Content
{
    protected $modelClass = ProductModel::class;
    protected $contentType = 'product';

    protected function afterCmsInitialize()
    {
        $categories = ProductCategory::where('status', 'normal')->order('weigh desc,id asc')->column('name', 'id');
        $this->view->assign('categoryList', $categories);
        $this->assignconfig('categoryList', $categories);
    }

    protected function previewPath($row)
    {
        return '/product/' . $row['slug'] . '?preview=1';
    }
}

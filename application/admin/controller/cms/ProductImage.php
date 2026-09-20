<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;
use app\common\model\cms\Product;
use app\common\model\cms\ProductImage as ProductImageModel;

class ProductImage extends Backend
{
    protected $model = null;
    protected $modelValidate = true;
    protected $modelSceneValidate = false;
    protected $searchFields = 'id,alt,image';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new ProductImageModel();
        $productId = (int)$this->request->request('product_id');
        $this->view->assign('productId', $productId);
        $this->view->assign('imageTypeList', ['gallery' => '产品相册', 'color' => '颜色效果', 'scene' => '应用场景', 'report' => '检测报告']);
        $this->assignconfig('productId', $productId);
        $product = $productId > 0 ? Product::get($productId) : null;
        $parentExists = (bool)$product;
        $parentTitle = $product ? (string)$product['title'] : '';
        $this->view->assign('parentExists', $parentExists);
        $this->view->assign('parentTitle', $parentTitle);
        $this->view->assign('parentContextLabel', '当前产品');
        $this->view->assign('parentId', $productId);
        $this->assignconfig('parentExists', $parentExists);
        $this->assignconfig('parentTitle', $parentTitle);
        $this->assignconfig('parentContextLabel', '当前产品');
        $this->assignconfig('parentId', $productId);
        $this->assignconfig('imageTypeList', ['gallery' => '产品相册', 'color' => '颜色效果', 'scene' => '应用场景', 'report' => '检测报告']);
    }

    public function index()
    {
        $this->request->filter(['strip_tags', 'trim']);
        if (!$this->request->isAjax()) {
            return $this->view->fetch();
        }
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $productId = (int)$this->request->request('product_id');
        $list = $this->model->where($where)->where('product_id', $productId)->order($sort, $order)->paginate($limit);
        return json(['total' => $list->total(), 'rows' => $list->items()]);
    }
}

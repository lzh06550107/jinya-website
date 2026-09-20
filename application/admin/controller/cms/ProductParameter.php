<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;
use app\common\model\cms\Product;
use app\common\model\cms\ProductParameter as ProductParameterModel;

class ProductParameter extends Backend
{
    protected $model = null;
    protected $modelValidate = true;
    protected $modelSceneValidate = false;
    protected $searchFields = 'id,parameter_group,parameter_name,parameter_value';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new ProductParameterModel();
        $productId = (int)$this->request->request('product_id');
        $this->view->assign('productId', $productId);
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

<?php
namespace app\mobile\controller;
use app\common\service\cms\render\ContentNotFoundException;
class Product extends CmsBase
{
    public function index($category = '')
    {
        $category=trim((string)$category);if($category===''){$category=trim((string)$this->request->get('category',''));}
        try { $vm=$this->renderServices()->productList()->render($category?:null,$this->renderContext($this->request->get('page',1))); }
        catch(ContentNotFoundException $e){ return $this->redirectOr404(); }
        $this->setMobileTheme($category?'product-category':'product-list');$this->setMobileSection('products');$this->assignPageViewModel($vm);$this->applyMobileChannelFromViewModel($vm,'products');
        return $this->view->fetch('cms/product/index');
    }
    public function detail($slug='')
    {
        try{$vm=$this->renderServices()->productDetail()->render(trim((string)$slug),$this->renderContext());}
        catch(ContentNotFoundException $e){return $this->redirectOr404();}
        $this->setMobileTheme('product-detail');$this->setMobileSection('products');$this->assignPageViewModel($vm);$this->applyMobileChannelFromViewModel($vm,'products');
        return $this->view->fetch('cms/product/detail');
    }
}

<?php
namespace app\index\controller;
use app\common\service\cms\render\ContentNotFoundException;
class Product extends CmsBase
{
    public function index($category = '', $page = null)
    {
        $category = trim((string)$category);
        if ($category === '') {
            $category = trim((string)$this->request->get('category', ''));
        }
        try {
            $context = $this->renderContext($page ?: $this->request->get('page', 1));
            $vm = $this->renderServices()->productList()->render($category ?: null, $context);
        } catch (ContentNotFoundException $e) { return $this->redirectOr404(); }
        $this->setPcTheme($category ? 'product-category' : 'product-list', 'products');
        $this->assignPageViewModel($vm);
        $this->applyChannelFromViewModel($vm, $category ?: '产品中心', '/products');
        return $this->view->fetch('cms/product/index');
    }
    public function detail($slug = '')
    {
        try { $vm = $this->renderServices()->productDetail()->render(trim((string)$slug), $this->renderContext()); }
        catch (ContentNotFoundException $e) { return $this->redirectOr404(); }
        $this->setPcTheme('product-detail', 'products', 'pc-product-detail');
        $this->assignPageViewModel($vm);
        $this->applyChannelFromViewModel($vm, '产品中心', '/products');
        return $this->view->fetch('cms/product/detail');
    }
}

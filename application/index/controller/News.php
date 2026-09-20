<?php
namespace app\index\controller;
use app\common\service\cms\render\ContentNotFoundException;
class News extends CmsBase
{
    public function index($category='')
    {
        try{$vm=$this->renderServices()->newsList()->render(trim((string)$category)?:null,$this->renderContext($this->request->get('page',1)));}
        catch(ContentNotFoundException $e){return $this->redirectOr404();}
        $this->setPcTheme('article-list','news');$this->assignPageViewModel($vm);$this->applyChannelFromViewModel($vm,$category?:'新闻动态','/news');
        return $this->view->fetch('cms/news/index');
    }
    public function detail($slug='')
    {
        try{$vm=$this->renderServices()->newsDetail()->render(trim((string)$slug),$this->renderContext());}
        catch(ContentNotFoundException $e){return $this->redirectOr404();}
        $this->setPcTheme('article-detail','news','body-color-p102');$this->assignPageViewModel($vm);$this->applyChannelFromViewModel($vm,'新闻动态','/news');
        return $this->view->fetch('cms/news/detail');
    }
}

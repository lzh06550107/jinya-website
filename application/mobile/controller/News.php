<?php
namespace app\mobile\controller;
use app\common\service\cms\render\ContentNotFoundException;
class News extends CmsBase
{
    public function index($category='')
    {
        try{$vm=$this->renderServices()->newsList()->render(trim((string)$category)?:null,$this->renderContext($this->request->get('page',1)));}
        catch(ContentNotFoundException $e){return $this->redirectOr404();}
        $this->setMobileTheme('article-list');$this->setMobileSection('news');$this->view->assign('mobileBodyClass','mobile-news');$this->assignPageViewModel($vm);$this->applyMobileChannelFromViewModel($vm,'news');
        return $this->view->fetch('cms/news/index');
    }
    public function detail($slug='')
    {
        try{$vm=$this->renderServices()->newsDetail()->render(trim((string)$slug),$this->renderContext());}
        catch(ContentNotFoundException $e){return $this->redirectOr404();}
        $this->setMobileTheme('article-detail');$this->setMobileSection('news');$this->view->assign('mobileBodyClass','mobile-news-detail');$this->assignPageViewModel($vm);$this->applyMobileChannelFromViewModel($vm,'news');
        return $this->view->fetch('cms/news/detail');
    }
}

<?php
namespace app\index\controller;
class Index extends CmsBase
{
    public function index()
    {
        $this->setPcTheme('index', 'home', '');
        $vm = $this->renderServices()->home()->render($this->renderContext());
        $this->assignPageViewModel($vm);
        return $this->view->fetch('cms/index/index');
    }
}

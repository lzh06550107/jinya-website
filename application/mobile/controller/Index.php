<?php
namespace app\mobile\controller;
class Index extends CmsBase
{
    public function index()
    {
        $this->setMobileTheme('home');
        $this->setMobileSection('home');
        $vm = $this->renderServices()->home()->render($this->renderContext());
        $this->assignPageViewModel($vm);
        return $this->view->fetch('cms/index/index');
    }
}

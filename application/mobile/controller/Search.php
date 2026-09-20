<?php

namespace app\mobile\controller;

class Search extends CmsBase
{
    public function index()
    {
        $keyword = trim((string)$this->request->get('key', $this->request->get('q', '')));
        $vm = $this->renderServices()->search()->render(
            $keyword,
            $this->renderContext($this->request->get('page', 1))
        );
        $this->setMobileTheme('search');
        $this->setMobileSection('search');
        $this->assignPageViewModel($vm);
        $this->applyMobileChannelFromViewModel($vm, 'search');
        return $this->view->fetch('cms/search/index');
    }

    public function sitemap()
    {
        $vm = $this->renderServices()->sitemap()->render($this->renderContext());
        $this->setMobileTheme('about');
        $this->setMobileSection('page');
        $this->assignPageViewModel($vm);
        $this->applyMobileChannelFromViewModel($vm, 'page');
        return $this->view->fetch('cms/search/sitemap');
    }
}

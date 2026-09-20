<?php

namespace app\index\controller;

class Search extends CmsBase
{
    public function index()
    {
        $keyword = trim((string)$this->request->get('key', $this->request->get('q', '')));
        $vm = $this->renderServices()->search()->render(
            $keyword,
            $this->renderContext($this->request->get('page', 1))
        );
        $this->setPcTheme('search', 'search');
        $this->assignPageViewModel($vm);
        $this->applyChannelFromViewModel($vm, $keyword !== '' ? '搜索：' . $keyword : '站内搜索', '');
        return $this->view->fetch('cms/search/index');
    }

    public function sitemap()
    {
        $vm = $this->renderServices()->sitemap()->render($this->renderContext());
        $this->setPcTheme('sitemap', 'sitemap');
        $this->assignPageViewModel($vm);
        $this->applyChannelFromViewModel($vm, '网站地图', '');
        return $this->view->fetch('cms/search/sitemap');
    }
}

<?php

namespace app\index\controller;

use app\common\service\cms\render\ContentNotFoundException;

class Page extends CmsBase
{
    private $aliases = [
        'zjkcm' => 'about',
        'lxkcm' => 'contact',
    ];

    private $allowed = ['label', 'bags', 'boxes', 'about', 'contact'];

    public function detail($slug = '')
    {
        $slug = trim((string)$slug);
        $slug = isset($this->aliases[$slug]) ? $this->aliases[$slug] : $slug;
        if (!in_array($slug, $this->allowed, true)) {
            return $this->redirectOr404();
        }

        try {
            $vm = $this->renderServices()->singlePage()->render($slug, $this->renderContext());
        } catch (ContentNotFoundException $e) {
            return $this->redirectOr404();
        }

        $theme = in_array($slug, ['label', 'bags', 'boxes'], true) ? $slug . '-page' : 'about';
        $section = $slug === 'contact' ? 'contact' : 'page';
        $this->setPcTheme($theme, $section);
        $this->assignPageViewModel($vm);
        $this->applyChannelFromViewModel($vm, $this->pageTitle($slug), '');

        return $this->view->fetch('cms/page/' . $slug);
    }

    private function pageTitle($slug)
    {
        $titles = [
            'label' => '不干胶/卷标',
            'bags' => '包装袋·无版印刷',
            'boxes' => '彩盒',
            'about' => '走进金亚',
            'contact' => '联系我们',
        ];
        return isset($titles[$slug]) ? $titles[$slug] : '';
    }
}

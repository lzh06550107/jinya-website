<?php

namespace app\mobile\controller;

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
        $this->setMobileTheme($theme);
        $this->setMobileSection($section);
        $this->assignPageViewModel($vm);
        $this->applyMobileChannelFromViewModel($vm, $section);

        return $this->view->fetch('cms/page/' . $slug);
    }
}

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

    public function labels(){ return $this->detail('label'); }
    public function bags(){ return $this->detail('bags'); }
    public function boxes(){ return $this->detail('boxes'); }
    public function about(){ return $this->detail('about'); }
    public function contact(){ return $this->detail('contact'); }

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
        $bodyClasses = [
            'label' => 'mobile-labels',
            'bags' => 'mobile-bags',
            'boxes' => 'mobile-boxes',
            'about' => 'mobile-about',
            'contact' => 'mobile-contact',
        ];
        $this->setMobileTheme($theme);
        $this->setMobileSection($section);
        $this->view->assign('mobileBodyClass', $bodyClasses[$slug]);
        $this->assignPageViewModel($vm);
        $this->applyMobileChannelFromViewModel($vm, $section);

        return $this->view->fetch('cms/page/' . $slug);
    }
}

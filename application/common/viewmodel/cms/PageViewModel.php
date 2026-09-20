<?php
namespace app\common\viewmodel\cms;

class PageViewModel
{
    protected $seo;
    protected $layout;
    protected $banner;
    protected $breadcrumb;
    protected $pageConfig;
    protected $content;

    public function __construct(array $seo = [], array $layout = [], array $banner = [], array $breadcrumb = [], array $pageConfig = [], array $content = [])
    {
        $this->seo = $seo;
        $this->layout = $layout;
        $this->banner = $banner;
        $this->breadcrumb = $breadcrumb;
        $this->pageConfig = $pageConfig;
        $this->content = $content;
    }

    public function toArray()
    {
        return [
            'seo' => $this->seo,
            'layout' => $this->layout,
            'banner' => $this->banner,
            'breadcrumb' => $this->breadcrumb,
            'pageConfig' => $this->pageConfig,
            'content' => $this->content,
        ];
    }
}

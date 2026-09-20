<?php
namespace app\common\service\cms\render;

use app\common\repository\cms\ThinkNavigationRepository;
use app\common\repository\cms\ThinkBannerRepository;
use app\common\repository\cms\ThinkLayoutComponentRepository;
use app\common\repository\cms\ThinkPageConfigRepository;
use app\common\repository\cms\ThinkHomeSectionRepository;
use app\common\repository\cms\ThinkHomeSectionReferenceRepository;
use app\common\repository\cms\ThinkProductRepository;
use app\common\repository\cms\ThinkProductCategoryRepository;
use app\common\repository\cms\ThinkProductImageRepository;
use app\common\repository\cms\ThinkProductParameterRepository;
use app\common\repository\cms\ThinkProductSectionRepository;
use app\common\repository\cms\ThinkArticleRepository;
use app\common\repository\cms\ThinkArticleCategoryRepository;
use app\common\repository\cms\ThinkCaseRepository;
use app\common\repository\cms\ThinkPageRepository;
use app\common\repository\cms\ThinkPageContentBlockRepository;
use app\common\repository\cms\ThinkSearchRepository;

class RenderServiceFactory
{
    private $instances = [];

    public function layout()
    {
        return $this->once('layout', function () {
            return new LayoutRenderService(new ThinkNavigationRepository(), new ThinkLayoutComponentRepository(), new MediaUrlResolver());
        });
    }

    public function banners() { return $this->once('banners', function () { return new ThinkBannerRepository(); }); }
    public function pageConfig() { return $this->once('pageConfig', function () { return new ThinkPageConfigRepository(); }); }

    public function home()
    {
        return $this->once('home', function () {
            return new HomeRenderService(
                $this->layout(), $this->banners(), $this->pageConfig(),
                new ThinkHomeSectionRepository(), new ThinkHomeSectionReferenceRepository(),
                new ThinkProductRepository(), new ThinkArticleRepository(), new ThinkCaseRepository()
            );
        });
    }

    public function productList()
    {
        return $this->once('productList', function () {
            return new ProductListRenderService($this->layout(), $this->banners(), $this->pageConfig(), new ThinkProductRepository(), new ThinkProductCategoryRepository(), new ProductCategoryNavigationBuilder());
        });
    }

    public function productDetail()
    {
        return $this->once('productDetail', function () {
            return new ProductDetailRenderService($this->layout(), $this->banners(), $this->pageConfig(), new ThinkProductRepository(), new ThinkProductCategoryRepository(), new ThinkProductImageRepository(), new ThinkProductParameterRepository(), new ThinkProductSectionRepository());
        });
    }

    public function newsList()
    {
        return $this->once('newsList', function () {
            return new NewsListRenderService($this->layout(), $this->banners(), $this->pageConfig(), new ThinkArticleRepository(), new ThinkArticleCategoryRepository());
        });
    }

    public function newsDetail()
    {
        return $this->once('newsDetail', function () {
            return new NewsDetailRenderService($this->layout(), $this->banners(), $this->pageConfig(), new ThinkArticleRepository(), new ThinkArticleCategoryRepository());
        });
    }

    public function singlePage()
    {
        return $this->once('singlePage', function () {
            return new SinglePageRenderService($this->layout(), $this->banners(), $this->pageConfig(), new ThinkPageRepository(), new ThinkPageContentBlockRepository());
        });
    }

    public function search()
    {
        return $this->once('search', function () {
            return new SearchRenderService($this->layout(), $this->banners(), $this->pageConfig(), new ThinkSearchRepository());
        });
    }

    public function sitemap()
    {
        return $this->once('sitemap', function () {
            return new SitemapRenderService($this->layout(), $this->banners(), $this->pageConfig(), new ThinkProductCategoryRepository(), new ThinkProductRepository(), new ThinkArticleCategoryRepository(), new ThinkArticleRepository(), new ThinkPageRepository());
        });
    }

    public function error()
    {
        return $this->once('error', function () {
            return new ErrorPageRenderService($this->layout(), $this->banners(), $this->pageConfig());
        });
    }

    public function preview()
    {
        return $this->once('preview', function () {
            return new PreviewRenderService(
                $this->layout(), $this->banners(), $this->pageConfig(),
                new ThinkProductRepository(), new ThinkProductCategoryRepository(), new ThinkProductImageRepository(),
                new ThinkProductParameterRepository(), new ThinkProductSectionRepository(),
                new ThinkArticleRepository(), new ThinkPageRepository(), new ThinkPageContentBlockRepository()
            );
        });
    }

    private function once($key, callable $factory)
    {
        if (!isset($this->instances[$key])) {
            $this->instances[$key] = $factory();
        }
        return $this->instances[$key];
    }
}

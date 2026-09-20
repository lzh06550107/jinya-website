<?php
namespace app\common\service\cms\render;

use app\common\repository\cms\ProductCategoryRepositoryInterface;
use app\common\repository\cms\ProductRepositoryInterface;
use app\common\repository\cms\ArticleCategoryRepositoryInterface;
use app\common\repository\cms\ArticleRepositoryInterface;
use app\common\repository\cms\PageRepositoryInterface;
use app\common\viewmodel\cms\SitemapPageViewModel;

class SitemapRenderService extends AbstractRenderService
{
    private $productCategories;
    private $products;
    private $articleCategories;
    private $articles;
    private $pages;
    private $pageSlugs = ['label', 'bags', 'boxes', 'about', 'contact'];

    public function __construct(LayoutRenderService $layout, $banners, $pageConfig, ProductCategoryRepositoryInterface $productCategories, ProductRepositoryInterface $products, ArticleCategoryRepositoryInterface $articleCategories, ArticleRepositoryInterface $articles, PageRepositoryInterface $pages)
    {
        parent::__construct($layout, $banners, $pageConfig);
        $this->productCategories = $productCategories;
        $this->products = $products;
        $this->articleCategories = $articleCategories;
        $this->articles = $articles;
        $this->pages = $pages;
    }

    public function render(RenderContext $context)
    {
        $common = $this->common('sitemap', $context, [], '网站地图', '/sitemap');
        $productRows = $this->products->paginatePublished(null, 1, 1000, 'weigh desc,publish_time desc,id desc')['items'];
        $products = [];
        foreach ($productRows as $row) {
            $products[] = $this->cards->product($row, $context);
        }
        $articleRows = $this->articles->paginatePublished(null, 1, 1000, 'article.publish_time desc,article.id desc')['items'];
        $articles = [];
        foreach ($articleRows as $row) {
            $articles[] = $this->cards->article($row, $context);
        }
        $pages = [];
        foreach ($this->pages->publishedAll() as $row) {
            if (!in_array((string)$row['slug'], $this->pageSlugs, true)) {
                continue;
            }
            $pages[] = $this->cards->page($row, $context);
        }
        $content = ['groups' => [
            'product_categories' => $this->productCategories->publishedTree(),
            'products' => $products,
            'article_categories' => $this->articleCategories->publishedTree(),
            'articles' => $articles,
            'pages' => $pages,
        ]];
        return new SitemapPageViewModel($common['seo'], $common['layout'], $this->banners('sitemap', 'channel', $context), [['title' => '网站地图', 'url' => '']], $common['page_config'], $content);
    }
}

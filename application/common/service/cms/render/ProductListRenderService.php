<?php

namespace app\common\service\cms\render;

use app\common\repository\cms\ProductRepositoryInterface;
use app\common\repository\cms\ProductCategoryRepositoryInterface;
use app\common\viewmodel\cms\ProductListViewModel;

class ProductListRenderService extends AbstractRenderService
{
    private $products;
    private $categories;
    private $categoryNavigation;

    public function __construct(
        LayoutRenderService $layout,
        $banners,
        $pageConfig,
        ProductRepositoryInterface $products,
        ProductCategoryRepositoryInterface $categories,
        ProductCategoryNavigationBuilder $categoryNavigation = null
    ) {
        parent::__construct($layout, $banners, $pageConfig);
        $this->products = $products;
        $this->categories = $categories;
        $this->categoryNavigation = $categoryNavigation ?: new ProductCategoryNavigationBuilder();
    }

    public function render($categorySlug, RenderContext $context)
    {
        $pageKey = $categorySlug ? 'product.category' : 'product.index';
        $category = null;
        if ($categorySlug) {
            $category = $this->categories->findPublishedBySlug($categorySlug);
            if (!$category) {
                throw new ContentNotFoundException('product_category', $categorySlug);
            }
        }

        $categoryPath = $categorySlug ? '/products?category=' . rawurlencode($categorySlug) : '/products';
        $common = $this->common(
            $pageKey,
            $context,
            $category ?: [],
            isset($category['name']) ? $category['name'] : '产品中心',
            $categoryPath
        );
        $cfg = $this->configBlock($common['page_config'], 'list', [
            'page_size' => $context->isMobile() ? 10 : 12,
            'summary_length' => 220,
            'show_summary' => 1,
            'show_cover' => 1,
            'sort_mode' => 'weigh_desc',
        ]);
        $pageSize = max(1, (int)(isset($cfg['page_size']) ? $cfg['page_size'] : 12));
        $order = $this->order(isset($cfg['sort_mode']) ? $cfg['sort_mode'] : 'weigh_desc');
        $data = $this->products->paginatePublished(
            $category ? (int)$category['id'] : null,
            $context->page(),
            $pageSize,
            $order
        );

        $items = [];
        foreach ($data['items'] as $row) {
            $card = $this->cards->product($row, $context);
            if (empty($cfg['show_summary'])) {
                $card['summary'] = '';
            }
            if (empty($cfg['show_cover'])) {
                $card['cover'] = '';
            }
            $items[] = $card;
        }

        $categoryTree = $this->categories->publishedTree();
        $navigationRows = $this->products->paginatePublished(
            null,
            1,
            1000,
            'weigh desc,publish_time desc,id desc'
        );
        $categoryNavigation = $this->categoryNavigation->build(
            $categoryTree,
            isset($navigationRows['items']) ? $navigationRows['items'] : []
        );

        $pagination = $this->pagination->build(
            $context->page(),
            $pageSize,
            $data['total'],
            $categoryPath
        );
        $content = [
            'categories' => $categoryTree,
            'category_navigation' => $categoryNavigation,
            'currentCategory' => $category ?: [],
            'products' => [
                'items' => $items,
                'total' => $data['total'],
                'empty' => empty($items),
                'empty_message' => '暂无产品内容',
            ],
            'pagination' => $pagination,
            'visibility' => [
                'cover' => !empty($cfg['show_cover']),
                'summary' => !empty($cfg['show_summary']),
            ],
        ];
        $banner = $this->banners($pageKey, 'channel', $context);

        return new ProductListViewModel(
            $common['seo'],
            $common['layout'],
            $banner,
            [
                ['title' => '产品中心', 'url' => '/products'],
                ['title' => isset($category['name']) ? $category['name'] : '', 'url' => $categoryPath],
            ],
            $common['page_config'],
            $content
        );
    }

    private function order($mode)
    {
        $map = [
            'publish_desc' => 'publish_time desc,id desc',
            'title_asc' => 'title asc,id asc',
            'weigh_desc' => 'weigh desc,publish_time desc,id desc',
        ];
        return isset($map[$mode]) ? $map[$mode] : $map['weigh_desc'];
    }
}

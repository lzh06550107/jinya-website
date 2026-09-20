<?php

namespace app\common\service\cms\render;

use app\common\repository\cms\ProductRepositoryInterface;
use app\common\repository\cms\ProductCategoryRepositoryInterface;
use app\common\repository\cms\ProductImageRepositoryInterface;
use app\common\repository\cms\ProductParameterRepositoryInterface;
use app\common\repository\cms\ProductSectionRepositoryInterface;
use app\common\repository\cms\ArticleRepositoryInterface;
use app\common\repository\cms\PageRepositoryInterface;
use app\common\repository\cms\PageContentBlockRepositoryInterface;
use app\common\service\cms\MarkdownRenderer;
use app\common\viewmodel\cms\ProductDetailViewModel;
use app\common\viewmodel\cms\NewsDetailViewModel;
use app\common\viewmodel\cms\SinglePageViewModel;

class PreviewRenderService extends AbstractRenderService
{
    private $products;
    private $productCategories;
    private $productImages;
    private $productParameters;
    private $productSections;
    private $articles;
    private $pages;
    private $pageBlocks;
    private $blockFactory;
    private $allowedPageSlugs = ['label', 'bags', 'boxes', 'about', 'contact'];

    public function __construct(
        LayoutRenderService $layout,
        $banners,
        $pageConfig,
        ProductRepositoryInterface $products,
        ProductCategoryRepositoryInterface $productCategories,
        ProductImageRepositoryInterface $productImages,
        ProductParameterRepositoryInterface $productParameters,
        ProductSectionRepositoryInterface $productSections,
        ArticleRepositoryInterface $articles,
        PageRepositoryInterface $pages,
        PageContentBlockRepositoryInterface $pageBlocks,
        PageBlockViewModelFactory $blockFactory = null
    ) {
        parent::__construct($layout, $banners, $pageConfig);
        $this->products = $products;
        $this->productCategories = $productCategories;
        $this->productImages = $productImages;
        $this->productParameters = $productParameters;
        $this->productSections = $productSections;
        $this->articles = $articles;
        $this->pages = $pages;
        $this->pageBlocks = $pageBlocks;
        $this->blockFactory = $blockFactory ?: new PageBlockViewModelFactory();
    }

    public function render($type, $id, RenderContext $context)
    {
        $type = (string)$type;
        $id = (int)$id;
        if ($type === 'product') {
            return $this->product($id, $context);
        }
        if ($type === 'article') {
            return $this->article($id, $context);
        }
        if ($type === 'page') {
            return $this->page($id, $context);
        }
        throw new ContentNotFoundException('preview', $type . ':' . $id);
    }

    private function product($id, RenderContext $context)
    {
        $row = $this->products->findById($id);
        if (!$row) {
            throw new ContentNotFoundException('product', $id);
        }
        $common = $this->common('product.detail', $context, $row, $row['title'], '/product/' . rawurlencode($row['slug']));
        $product = $this->cards->product($row, $context) + [
            'product_code' => isset($row['product_code']) ? $row['product_code'] : '',
            'tags' => isset($row['tags']) ? $row['tags'] : '',
            'content_html' => MarkdownRenderer::render(isset($row['content']) ? $row['content'] : ''),
            'category' => $this->findCategory($this->productCategories->publishedTree(), isset($row['category_id']) ? (int)$row['category_id'] : 0),
            'views' => (int)(isset($row['views']) ? $row['views'] : 0),
        ];
        $gallery = [];
        foreach ($this->productImages->publishedForProduct($id, $context->terminal()) as $image) {
            $type = isset($image['image_type']) && $image['image_type'] === 'video' ? 'video' : 'image';
            $gallery[] = [
                'type' => $type,
                'image' => $type === 'image' ? $image['resolved_image'] : '',
                'video_url' => $type === 'video' ? $image['resolved_image'] : '',
                'alt' => isset($image['alt']) ? $image['alt'] : '',
                'title' => isset($image['alt']) ? $image['alt'] : '',
                'is_cover' => !empty($image['is_cover']),
            ];
        }
        $sections = [];
        $sectionGroups = ['introduction'=>[],'features'=>[],'parameters'=>[],'application'=>[],'construction'=>[],'precautions'=>[],'custom_text'=>[]];
        foreach ($this->productSections->publishedForProduct($id, $context->terminal()) as $section) {
            $type = isset($section['section_type']) ? (string)$section['section_type'] : '';
            if (!array_key_exists($type, $sectionGroups)) continue;
            $item = [
                'type'=>$type,
                'title'=>isset($section['title'])?$section['title']:'',
                'subtitle' => isset($section['subtitle']) ? $section['subtitle'] : '',
                'content_html'=>MarkdownRenderer::render(isset($section['content'])?$section['content']:''),
                'content_inline_html'=>MarkdownRenderer::renderInline(isset($section['content'])?$section['content']:''),
                'image'=>isset($section['resolved_image'])?$section['resolved_image']:'',
                'source_key'=>isset($section['source_key'])?$section['source_key']:'',
            ];
            $sections[] = $item;
            $sectionGroups[$type][] = $item;
        }
        $content = [
            'product'=>$product,
            'gallery'=>$gallery,
            'sections'=>$sections,
            'sectionGroups'=>$sectionGroups,
            'parameters'=>$this->normalizeParameterPairs($this->groupParameters($this->productParameters->visibleForProduct($id))),
            'relatedProducts'=>[],
            'previous'=>[],
            'next'=>[],
            'contact'=>$this->configBlock($common['page_config'], 'contact', []),
            'visibility'=>['gallery'=>true,'parameters'=>true,'content'=>true,'related'=>false],
        ];
        $banner = $this->banners('product.detail.' . $row['slug'], 'channel', $context);
        if (!$banner) $banner = $this->banners('product.detail', 'channel', $context);
        $vm = new ProductDetailViewModel(
            $this->previewSeo($common['seo']), $common['layout'], $banner,
            [['title'=>'产品中心','url'=>'/products'],['title'=>$row['title'],'url'=>'']],
            $common['page_config'], $content
        );
        return $this->result($vm, 'cms/product/detail', 'product-detail', 'product-detail', 'products');
    }

    private function article($id, RenderContext $context)
    {
        $row = $this->articles->findById($id);
        if (!$row) {
            throw new ContentNotFoundException('article', $id);
        }
        $common = $this->common('news.detail', $context, $row, $row['title'], '/news/' . rawurlencode($row['slug']));
        $article = $this->cards->article($row, $context) + [
            'content_html' => MarkdownRenderer::render(isset($row['content']) ? $row['content'] : ''),
            'views' => (int)(isset($row['views']) ? $row['views'] : 0),
            'tags' => $this->tags(isset($row['tags']) ? $row['tags'] : ''),
        ];
        $content = [
            'categories' => [],
            'article' => $article,
            'previous' => [],
            'next' => [],
            'relatedArticles' => [],
            'latestArticles' => [],
            'latestColumns' => [[], []],
            'metadata' => [
                'author' => $article['author'],
                'source' => $article['source'],
                'publish_date' => $article['publish_date'],
                'views' => $article['views'],
            ],
            'visibility' => ['metadata' => true, 'related' => false, 'latest' => false],
        ];
        $vm = new NewsDetailViewModel(
            $this->previewSeo($common['seo']),
            $common['layout'],
            $this->banners('news.detail', 'channel', $context),
            [['title' => '新闻动态', 'url' => '/news'], ['title' => $row['title'], 'url' => '']],
            $common['page_config'],
            $content
        );
        return $this->result($vm, 'cms/news/detail', 'article-detail', 'article-detail', 'news');
    }

    private function page($id, RenderContext $context)
    {
        $row = $this->pages->findById($id);
        if (!$row) {
            throw new ContentNotFoundException('page', $id);
        }
        $slug = isset($row['slug']) ? trim((string)$row['slug']) : '';
        if (!in_array($slug, $this->allowedPageSlugs, true)) {
            throw new ContentNotFoundException('page', $id);
        }
        $key = 'page.' . $slug;
        $common = $this->common($key, $context, $row, $row['title'], '/page/' . rawurlencode($slug));
        $blocks = $this->blockFactory->map(
            $this->pageBlocks->publishedForPage($id, $context->terminal()),
            $context->terminal()
        );
        $body = $context->isMobile() && !empty($row['mobile_content'])
            ? $row['mobile_content']
            : (isset($row['content']) ? $row['content'] : '');
        $content = [
            'page' => $this->cards->page($row, $context) + ['content_html' => MarkdownRenderer::render($body)],
            'blocks' => $blocks,
            'template_key' => $slug,
            'empty' => empty($blocks) && trim(strip_tags($body)) === '',
        ];
        $vm = new SinglePageViewModel(
            $this->previewSeo($common['seo']),
            $common['layout'],
            $this->banners($key, 'channel', $context),
            [['title' => $row['title'], 'url' => '']],
            $common['page_config'],
            $content
        );
        $theme = in_array($slug, ['label', 'bags', 'boxes'], true) ? $slug . '-page' : 'about';
        $section = $slug === 'contact' ? 'contact' : 'page';
        return $this->result($vm, 'cms/page/' . $slug, $theme, $theme, $section);
    }

    private function normalizeParameterPairs(array $groups)
    {
        foreach ($groups as &$group) {
            if (empty($group['items']) || !is_array($group['items'])) continue;
            foreach ($group['items'] as &$item) {
                $item['paired_name'] = '';
                $item['paired_value'] = '';
                $parts = array_map('trim', explode(' / ', isset($item['parameter_value']) ? (string)$item['parameter_value'] : ''));
                if (count($parts) === 3 && $parts[1] !== '' && $parts[2] !== '') {
                    $item['parameter_value'] = $parts[0];
                    $item['paired_name'] = $parts[1];
                    $item['paired_value'] = $parts[2];
                }
            }
            unset($item);
        }
        unset($group);
        return $groups;
    }

    private function findCategory(array $tree, $id)
    {
        foreach ($tree as $row) {
            if ((int)$row['id'] === (int)$id) return $row;
            if (!empty($row['children'])) {
                $found = $this->findCategory($row['children'], $id);
                if ($found) return $found;
            }
        }
        return [];
    }

    private function result($vm, $template, $pcTheme, $mobileTheme, $section)
    {
        return ['view_model' => $vm, 'template' => $template, 'pc_theme' => $pcTheme, 'mobile_theme' => $mobileTheme, 'section' => $section];
    }

    private function previewSeo(array $seo)
    {
        $seo['robots'] = 'noindex,nofollow';
        return $seo;
    }

    private function tags($value)
    {
        return array_values(array_filter(array_map('trim', preg_split('/[,，;；]+/u', (string)$value))));
    }
}

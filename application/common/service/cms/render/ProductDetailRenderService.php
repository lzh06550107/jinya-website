<?php
namespace app\common\service\cms\render;

use app\common\repository\cms\ProductRepositoryInterface;
use app\common\repository\cms\ProductCategoryRepositoryInterface;
use app\common\repository\cms\ProductImageRepositoryInterface;
use app\common\repository\cms\ProductParameterRepositoryInterface;
use app\common\repository\cms\ProductSectionRepositoryInterface;
use app\common\service\cms\MarkdownRenderer;
use app\common\viewmodel\cms\ProductDetailViewModel;

class ProductDetailRenderService extends AbstractRenderService
{
    private $products;
    private $categories;
    private $images;
    private $parameters;
    private $sections;

    public function __construct(
        LayoutRenderService $layout,
        $banners,
        $pageConfig,
        ProductRepositoryInterface $products,
        ProductCategoryRepositoryInterface $categories,
        ProductImageRepositoryInterface $images,
        ProductParameterRepositoryInterface $parameters,
        ProductSectionRepositoryInterface $sections
    ) {
        parent::__construct($layout, $banners, $pageConfig);
        $this->products = $products;
        $this->categories = $categories;
        $this->images = $images;
        $this->parameters = $parameters;
        $this->sections = $sections;
    }

    public function render($slug, RenderContext $context)
    {
        $row = $this->products->findPublishedBySlug($slug);
        if (!$row) throw new ContentNotFoundException('product', $slug);

        $common = $this->common('product.detail', $context, $row, $row['title'], '/product/' . rawurlencode($slug));
        $category = $this->findCategory($this->categories->publishedTree(), (int)$row['category_id']);
        $product = $this->cards->product($row, $context);
        $product = array_merge($product, [
            'product_code' => isset($row['product_code']) ? $row['product_code'] : '',
            'tags' => isset($row['tags']) ? $row['tags'] : '',
            'content_html' => MarkdownRenderer::render(isset($row['content']) ? $row['content'] : ''),
            'category' => $category,
            'views' => (int)(isset($row['views']) ? $row['views'] : 0),
        ]);

        $gallery = [];
        foreach ($this->images->publishedForProduct($row['id'], $context->terminal()) as $img) {
            $type = isset($img['image_type']) && $img['image_type'] === 'video' ? 'video' : 'image';
            $gallery[] = [
                'type' => $type,
                'image' => $type === 'image' ? $img['resolved_image'] : '',
                'video_url' => $type === 'video' ? $img['resolved_image'] : '',
                'alt' => isset($img['alt']) ? $img['alt'] : '',
                'title' => isset($img['alt']) ? $img['alt'] : '',
                'is_cover' => !empty($img['is_cover']),
            ];
        }

        $sections = [];
        $sectionGroups = [
            'introduction' => [],
            'features' => [],
            'parameters' => [],
            'application' => [],
            'construction' => [],
            'precautions' => [],
            'custom_text' => [],
        ];
        $allowedSectionTypes = array_keys($sectionGroups);
        foreach ($this->sections->publishedForProduct($row['id'], $context->terminal()) as $section) {
            $type = isset($section['section_type']) ? (string)$section['section_type'] : '';
            if (!in_array($type, $allowedSectionTypes, true)) {
                continue;
            }
            $rawContent = isset($section['content']) ? (string)$section['content'] : '';
            $item = [
                'type' => $type,
                'title' => isset($section['title']) ? $section['title'] : '',
                'subtitle' => isset($section['subtitle']) ? $section['subtitle'] : '',
                'content_html' => MarkdownRenderer::render($rawContent),
                'content_inline_html' => MarkdownRenderer::renderInline($rawContent),
                'list_items' => $this->parseListItems($rawContent),
                'image' => isset($section['resolved_image']) ? $section['resolved_image'] : '',
                'source_key' => isset($section['source_key']) ? $section['source_key'] : '',
            ];
            $sections[] = $item;
            $sectionGroups[$type][] = $item;
        }

        $params = $this->normalizeParameterPairs($this->groupParameters($this->parameters->visibleForProduct($row['id'])));
        $summaryParameters = $this->summaryParameters($params, 6);
        $featureItems = $this->sectionListItems($sectionGroups['features']);
        if (empty($featureItems)) {
            $featureItems = $this->parseListItems(isset($row['features']) ? $row['features'] : '');
        }
        $applicationItems = $this->sectionListItems($sectionGroups['application']);
        if (empty($applicationItems)) {
            $applicationItems = $this->parseListItems(isset($row['applications']) ? $row['applications'] : '');
        }
        $processSteps = $this->processSteps($sectionGroups, isset($row['construction']) ? $row['construction'] : '');
        $relatedIds = $this->referenceIds($common['page_config'], 'related', 'product');
        $relatedRows = $relatedIds
            ? $this->products->publishedByIds($relatedIds)
            : $this->products->related($row['id'], $row['category_id'], 4);
        $related = [];
        foreach ($relatedRows as $relatedRow) $related[] = $this->cards->product($relatedRow, $context);

        $content = [
            'product' => $product,
            'gallery' => $gallery,
            'sections' => $sections,
            'sectionGroups' => $sectionGroups,
            'parameters' => $params,
            'summaryParameters' => $summaryParameters,
            'featureItems' => $featureItems,
            'applicationItems' => $applicationItems,
            'processSteps' => $processSteps,
            'relatedProducts' => $related,
            'contact' => $this->configBlock($common['page_config'], 'contact', [
                'button_text' => '在线咨询',
                'link_url' => 'http://wpa.qq.com/msgrd?v=3&uin=&site=qq&menu=yes',
            ]),
            'visibility' => [
                'gallery' => isset($common['page_config']['blocks']['gallery']),
                'parameters' => isset($common['page_config']['blocks']['parameters']),
                'content' => isset($common['page_config']['blocks']['content']),
                'related' => isset($common['page_config']['blocks']['related']),
            ],
        ];
        return new ProductDetailViewModel(
            $common['seo'],
            $common['layout'],
            [],
            $this->detailBreadcrumb($category, $row),
            $common['page_config'],
            $content
        );
    }

    /**
     * Historical clone imports sometimes stored two visual parameter pairs in
     * one row separated by " / ". Normalize that legacy shape for the clone
     * template without changing the stored business value.
     */
    private function summaryParameters(array $groups, $limit)
    {
        $out = [];
        foreach ($groups as $group) {
            if (empty($group['items']) || !is_array($group['items'])) {
                continue;
            }
            foreach ($group['items'] as $item) {
                $out[] = $item;
                if (count($out) >= $limit) {
                    return $out;
                }
            }
        }
        return $out;
    }

    private function sectionListItems(array $sections)
    {
        $out = [];
        foreach ($sections as $section) {
            if (!empty($section['list_items']) && is_array($section['list_items'])) {
                foreach ($section['list_items'] as $item) {
                    if ($item !== '') $out[] = $item;
                }
            } elseif (!empty($section['content_inline_html'])) {
                $text = trim(strip_tags((string)$section['content_inline_html']));
                if ($text !== '') $out[] = $text;
            }
        }
        return array_values(array_unique($out));
    }

    private function processSteps(array $sectionGroups, $fallback)
    {
        $steps = [];
        if (!empty($sectionGroups['construction'])) {
            foreach ($sectionGroups['construction'] as $section) {
                $title = trim(isset($section['title']) ? (string)$section['title'] : '');
                $description = trim(strip_tags(isset($section['content_inline_html']) ? (string)$section['content_inline_html'] : ''));
                if ($title === '' && $description === '') continue;
                $steps[] = [
                    'title' => $title !== '' ? $title : '定制步骤',
                    'description' => $description,
                    'image' => isset($section['image']) ? $section['image'] : '',
                ];
            }
        }
        if (empty($steps) && !empty($sectionGroups['custom_text'])) {
            foreach ($sectionGroups['custom_text'] as $section) {
                $items = isset($section['list_items']) && is_array($section['list_items']) ? $section['list_items'] : [];
                foreach ($items as $item) {
                    $steps[] = ['title' => '', 'description' => $item, 'image' => ''];
                }
                if (!empty($steps)) break;
            }
        }
        if (empty($steps)) {
            foreach ($this->parseListItems($fallback) as $item) {
                $steps[] = ['title' => '', 'description' => $item, 'image' => ''];
            }
        }
        return $steps;
    }

    private function parseListItems($text)
    {
        $text = trim((string)$text);
        if ($text === '') return [];
        $out = [];
        foreach (preg_split('/\r\n|\r|\n/', $text) as $line) {
            $line = trim($line);
            if ($line === '') continue;
            if (preg_match('/^(?:[-*+]\s+|\d+[\.、\)]\s*)(.+)$/u', $line, $match)) {
                $value = trim($match[1]);
                $value = preg_replace('/[*_`#]+/u', '', $value);
                if ($value !== '') $out[] = $value;
            }
        }
        return array_values(array_unique($out));
    }

    private function normalizeParameterPairs(array $groups)
    {
        foreach ($groups as &$group) {
            if (empty($group['items']) || !is_array($group['items'])) {
                continue;
            }
            foreach ($group['items'] as &$item) {
                $item['paired_name'] = '';
                $item['paired_value'] = '';
                $value = isset($item['parameter_value']) ? (string)$item['parameter_value'] : '';
                $parts = array_map('trim', explode(' / ', $value));
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

    private function detailBreadcrumb(array $category, array $row)
    {
        $items = [
            ['title' => '产品中心', 'url' => '/products'],
        ];
        if (!empty($category['name'])) {
            $items[] = [
                'title' => $category['name'],
                'url' => !empty($category['slug'])
                    ? '/products?category=' . rawurlencode($category['slug'])
                    : '',
            ];
        }
        $items[] = [
            'title' => isset($row['title']) ? $row['title'] : '',
            'url' => '',
        ];
        return $items;
    }

    private function findCategory(array $tree, $id)
    {
        foreach ($tree as $row) {
            if ((int)$row['id'] === $id) return $row;
            $found = $this->findCategory(isset($row['children']) ? $row['children'] : [], $id);
            if ($found) return $found;
        }
        return [];
    }

    private function truncate($text, $length)
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$text)));
        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            return mb_strlen($text, 'UTF-8') > $length ? mb_substr($text, 0, $length, 'UTF-8') . '...' : $text;
        }
        return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
    }
}

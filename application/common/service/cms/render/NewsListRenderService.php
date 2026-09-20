<?php
namespace app\common\service\cms\render;

use app\common\exception\cms\ContentNotFoundException;
use app\common\repository\cms\ArticleCategoryRepositoryInterface;
use app\common\repository\cms\ArticleRepositoryInterface;
use app\common\viewmodel\cms\NewsListViewModel;

class NewsListRenderService extends AbstractRenderService
{
    private $articles;
    private $categories;

    public function __construct(
        LayoutRenderService $layout,
        $banners,
        $pageConfig,
        ArticleRepositoryInterface $articles,
        ArticleCategoryRepositoryInterface $categories
    ) {
        parent::__construct($layout, $banners, $pageConfig);
        $this->articles = $articles;
        $this->categories = $categories;
    }

    public function render($categorySlug, RenderContext $context)
    {
        $key = $categorySlug ? 'news.category' : 'news.index';
        $category = null;
        if ($categorySlug) {
            $category = $this->categories->findPublishedBySlug($categorySlug);
            if (!$category) {
                throw new ContentNotFoundException('article_category', $categorySlug);
            }
        }

        $path = $categorySlug ? '/news-list/' . rawurlencode($categorySlug) : '/news';
        $common = $this->common(
            $key,
            $context,
            $category ?: [],
            isset($category['name']) ? $category['name'] : '新闻动态',
            $path
        );

        $cfg = $this->configBlock($common['page_config'], 'list', [
            'page_size' => 10,
            'pc_page_size' => 10,
            'mobile_page_size' => 10,
            'summary_length' => 75,
            'pc_summary_length' => 75,
            'mobile_summary_length' => 28,
            'show_summary' => 1,
            'show_cover' => 1,
            'show_date' => 0,
            'sort_mode' => 'publish_desc',
        ]);

        $pageSizeKey = $context->isMobile() ? 'mobile_page_size' : 'pc_page_size';
        $summaryLengthKey = $context->isMobile() ? 'mobile_summary_length' : 'pc_summary_length';
        $pageSize = max(1, (int)(isset($cfg[$pageSizeKey]) ? $cfg[$pageSizeKey] : $cfg['page_size']));
        $summaryLength = max(20, (int)(isset($cfg[$summaryLengthKey]) ? $cfg[$summaryLengthKey] : $cfg['summary_length']));

        $data = $this->articles->paginatePublished(
            $category ? (int)$category['id'] : null,
            $context->page(),
            $pageSize,
            $this->order($cfg['sort_mode'])
        );

        $items = [];
        foreach ($data['items'] as $row) {
            $item = $this->cards->article($row, $context);
            $item['summary'] = empty($cfg['show_summary'])
                ? ''
                : $this->cloneSummary(isset($row['summary']) ? $row['summary'] : '', $summaryLength);
            if (empty($cfg['show_cover'])) {
                $item['cover'] = '';
            }
            $items[] = $item;
        }

        $content = [
            'categories' => $this->categories->publishedTree(),
            'currentCategory' => $category ?: [],
            'articles' => [
                'items' => $items,
                'total' => $data['total'],
                'empty' => empty($items),
                'empty_message' => '暂无新闻内容',
            ],
            'pagination' => $this->pagination->build($context->page(), $pageSize, $data['total'], $path),
            'visibility' => [
                'cover' => !empty($cfg['show_cover']),
                'summary' => !empty($cfg['show_summary']),
                'date' => !empty($cfg['show_date']),
            ],
        ];

        return new NewsListViewModel(
            $common['seo'],
            $common['layout'],
            $this->banners($key, 'channel', $context),
            [
                ['title' => '新闻动态', 'url' => '/news'],
                ['title' => isset($category['name']) ? $category['name'] : '', 'url' => $path],
            ],
            $common['page_config'],
            $content
        );
    }

    private function cloneSummary($value, $limit)
    {
        $value = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$value)));
        if ($limit <= 0 || $value === '') {
            return $value;
        }
        if (function_exists('mb_strlen')) {
            if (mb_strlen($value, 'UTF-8') <= $limit) {
                return $value;
            }
            return rtrim(mb_substr($value, 0, max(1, $limit - 3), 'UTF-8')) . '...';
        }
        if (strlen($value) <= $limit) {
            return $value;
        }
        return rtrim(substr($value, 0, max(1, $limit - 3))) . '...';
    }

    private function order($mode)
    {
        return $mode === 'is_top_desc'
            ? 'article.is_top desc,article.publish_time desc,article.id desc'
            : 'article.publish_time desc,article.id desc';
    }
}

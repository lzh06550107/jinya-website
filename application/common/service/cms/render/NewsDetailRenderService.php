<?php
namespace app\common\service\cms\render;

use app\common\repository\cms\ArticleRepositoryInterface;
use app\common\repository\cms\ArticleCategoryRepositoryInterface;
use app\common\service\cms\MarkdownRenderer;
use app\common\viewmodel\cms\NewsDetailViewModel;

class NewsDetailRenderService extends AbstractRenderService
{
    private $articles;
    private $categories;

    public function __construct(
        LayoutRenderService $layout,
        $banners,
        $pageConfig,
        ArticleRepositoryInterface $articles,
        ArticleCategoryRepositoryInterface $categories = null
    ) {
        parent::__construct($layout, $banners, $pageConfig);
        $this->articles = $articles;
        $this->categories = $categories;
    }

    public function render($slug, RenderContext $context)
    {
        $row = $this->articles->findPublishedBySlug($slug);
        if (!$row) {
            throw new ContentNotFoundException('article', $slug);
        }

        $common = $this->common('news.detail', $context, $row, $row['title'], '/news/' . rawurlencode($slug));
        $article = $this->cards->article($row, $context);
        $article = array_merge($article, [
            'content_html' => MarkdownRenderer::render(isset($row['content']) ? $row['content'] : ''),
            'views' => (int)(isset($row['views']) ? $row['views'] : 0),
            'tags' => $this->tags(isset($row['tags']) ? $row['tags'] : ''),
        ]);

        $nav = $this->articles->previousNext($row['id'], $row['publish_time']);
        $manual = $this->referenceIds($common['page_config'], 'related', 'article');
        $relatedRows = $manual
            ? $this->articles->publishedByIds($manual)
            : $this->articles->related($row['id'], $row['category_id'], 2);
        $related = [];
        foreach (array_slice($relatedRows, 0, 2) as $relatedRow) {
            $relatedItem = $this->cards->article($relatedRow, $context);
            $relatedItem['summary'] = $this->cloneSummary($relatedItem['summary'], 30);
            $related[] = $relatedItem;
        }

        $latest = [];
        foreach ($this->articles->latest($row['id'], 8) as $latestRow) {
            $latest[] = $this->cards->article($latestRow, $context);
        }

        $content = [
            'categories' => $this->categories ? $this->categories->publishedTree() : [],
            'article' => $article,
            'previous' => $nav['next'] ? $this->cards->article($nav['next'], $context) : [],
            'next' => $nav['previous'] ? $this->cards->article($nav['previous'], $context) : [],
            'relatedArticles' => $related,
            'latestArticles' => $latest,
            'latestColumns' => [array_slice($latest, 0, 4), array_slice($latest, 4, 4)],
            'metadata' => [
                'source' => $article['source'],
                'publish_date' => $article['publish_date'],
                'views' => $article['views'],
            ],
            'visibility' => [
                'metadata' => isset($common['page_config']['blocks']['metadata']),
                'related' => isset($common['page_config']['blocks']['related']),
                'latest' => isset($common['page_config']['blocks']['latest']),
            ],
        ];

        return new NewsDetailViewModel(
            $common['seo'],
            $common['layout'],
            $this->banners('news.detail', 'channel', $context),
            [
                ['title' => '新闻动态', 'url' => '/news'],
                ['title' => $article['category_name'], 'url' => $article['category_slug'] ? '/news-list/' . rawurlencode($article['category_slug']) : ''],
                ['title' => $article['title'], 'url' => ''],
            ],
            $common['page_config'],
            $content
        );
    }

    private function cloneSummary($value, $limit)
    {
        $value = trim((string)$value);
        $limit = max(0, (int)$limit);
        if ($limit === 0 || $value === '') {
            return $value;
        }
        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($value, 'UTF-8') <= $limit) {
                return $value;
            }
            return mb_substr($value, 0, $limit, 'UTF-8') . '...';
        }
        $chars = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY);
        if (is_array($chars) && count($chars) > $limit) {
            return implode('', array_slice($chars, 0, $limit)) . '...';
        }
        return $value;
    }

    private function tags($value)
    {
        $out = [];
        foreach (preg_split('/[,，;；]+/u', (string)$value) as $tag) {
            $tag = trim($tag);
            if ($tag !== '') {
                $out[] = ['name' => $tag, 'url' => '/search?key=' . rawurlencode($tag)];
            }
        }
        return $out;
    }
}

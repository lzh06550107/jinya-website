<?php
namespace app\common\service\cms\render;

use app\common\repository\cms\PageRepositoryInterface;
use app\common\repository\cms\PageContentBlockRepositoryInterface;
use app\common\service\cms\MarkdownRenderer;
use app\common\viewmodel\cms\SinglePageViewModel;

class SinglePageRenderService extends AbstractRenderService
{
    private $pages;
    private $blocks;
    private $blockFactory;
    private $allowed = ['label', 'bags', 'boxes', 'about', 'contact'];

    public function __construct(
        LayoutRenderService $layout,
        $banners,
        $pageConfig,
        PageRepositoryInterface $pages,
        PageContentBlockRepositoryInterface $blocks,
        PageBlockViewModelFactory $factory = null
    ) {
        parent::__construct($layout, $banners, $pageConfig);
        $this->pages = $pages;
        $this->blocks = $blocks;
        $this->blockFactory = $factory ?: new PageBlockViewModelFactory();
    }

    public function render($slug, RenderContext $context)
    {
        $slug = trim((string)$slug);
        if (!in_array($slug, $this->allowed, true)) {
            throw new ContentNotFoundException('page', $slug);
        }
        $page = $this->pages->findPublishedBySlug($slug);
        if (!$page) {
            throw new ContentNotFoundException('page', $slug);
        }

        $key = 'page.' . $slug;
        $common = $this->common($key, $context, $page, $page['title'], '/page/' . rawurlencode($slug));
        $blocks = $this->blockFactory->map(
            $this->blocks->publishedForPage($page['id'], $context->terminal()),
            $context->terminal()
        );
        $body = $context->isMobile() && !empty($page['mobile_content'])
            ? $page['mobile_content']
            : (isset($page['content']) ? $page['content'] : '');
        $content = [
            'page' => $this->cards->page($page, $context) + ['content_html' => MarkdownRenderer::render($body)],
            'blocks' => $blocks,
            'template_key' => $slug,
            'empty' => empty($blocks) && trim(strip_tags($body)) === '',
        ];

        return new SinglePageViewModel(
            $common['seo'],
            $common['layout'],
            [],
            [['title' => $page['title'], 'url' => '']],
            $common['page_config'],
            $content
        );
    }
}

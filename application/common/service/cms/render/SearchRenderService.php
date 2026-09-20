<?php
namespace app\common\service\cms\render;

use app\common\repository\cms\SearchRepositoryInterface;
use app\common\viewmodel\cms\SearchPageViewModel;

class SearchRenderService extends AbstractRenderService
{
    private $search;

    public function __construct(LayoutRenderService $layout, $banners, $pageConfig, SearchRepositoryInterface $search)
    {
        parent::__construct($layout, $banners, $pageConfig);
        $this->search = $search;
    }

    public function render($keyword, RenderContext $context)
    {
        $keyword = trim((string)$keyword);
        $common = $this->common('search', $context, [], $keyword !== '' ? '搜索：' . $keyword : '搜索', '/search?key=' . rawurlencode($keyword));
        $cfg = $this->configBlock($common['page_config'], 'results', [
            'page_size' => $context->isMobile() ? 10 : 12,
            'show_product' => 1,
            'show_article' => 1,
            'show_page' => 1,
        ]);
        $types = [];
        foreach (['product' => 'show_product', 'article' => 'show_article', 'page' => 'show_page'] as $type => $flag) {
            if (!empty($cfg[$flag])) {
                $types[] = $type;
            }
        }
        $size = max(1, (int)$cfg['page_size']);
        $data = $keyword === '' ? ['items' => [], 'total' => 0] : $this->search->searchPublished($keyword, $context->page(), $size, $types);
        $items = [];
        foreach ($data['items'] as $row) {
            $type = $row['_type'];
            unset($row['_type']);
            $item = $type === 'product' ? $this->cards->product($row, $context) : ($type === 'article' ? $this->cards->article($row, $context) : $this->cards->page($row, $context));
            $item['type'] = $type;
            $items[] = $item;
        }
        $content = [
            'keyword' => $keyword,
            'items' => $items,
            'total' => $data['total'],
            'pagination' => $this->pagination->build($context->page(), $size, $data['total'], '/search', ['key' => $keyword]),
            'empty' => empty($items),
            'empty_message' => $keyword === '' ? '请输入搜索关键词' : '暂无相关内容',
        ];
        return new SearchPageViewModel($common['seo'], $common['layout'], $this->banners('search', 'channel', $context), [['title' => '搜索', 'url' => '']], $common['page_config'], $content);
    }
}

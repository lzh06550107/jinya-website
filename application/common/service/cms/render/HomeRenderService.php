<?php
namespace app\common\service\cms\render;

if (!class_exists('app\\common\\service\\cms\\MarkdownRenderer', false)) {
    require_once dirname(__DIR__) . '/MarkdownRenderer.php';
}

use app\common\repository\cms\HomeSectionRepositoryInterface;
use app\common\repository\cms\HomeSectionReferenceRepositoryInterface;
use app\common\repository\cms\ProductRepositoryInterface;
use app\common\repository\cms\ArticleRepositoryInterface;
use app\common\repository\cms\CaseRepositoryInterface;
use app\common\viewmodel\cms\HomePageViewModel;
use app\common\service\cms\StructuredConfigCodec;
use app\common\service\cms\MarkdownRenderer;

class HomeRenderService extends AbstractRenderService
{
    private $sections;
    private $references;
    private $products;
    private $articles;
    private $cases;

    public function __construct(
        LayoutRenderService $layout,
        $banners,
        $pageConfig,
        HomeSectionRepositoryInterface $sections,
        HomeSectionReferenceRepositoryInterface $references,
        ProductRepositoryInterface $products,
        ArticleRepositoryInterface $articles,
        CaseRepositoryInterface $cases
    ) {
        parent::__construct($layout, $banners, $pageConfig);
        $this->sections = $sections;
        $this->references = $references;
        $this->products = $products;
        $this->articles = $articles;
        $this->cases = $cases;
    }

    public function render(RenderContext $context)
    {
        $common = $this->common('home', $context, [], '', '/');
        $map = $this->sections->publishedMap($context->terminal());
        $keys = ['about', 'products', 'service', 'workshop', 'cases', 'advantages', 'news', 'company', 'culture'];
        $content = [];
        foreach ($keys as $key) {
            $content[$key] = [];
        }

        $sectionOrder = [];
        foreach ($map as $key => $row) {
            if (!in_array($key, $keys, true)) {
                continue;
            }
            $content[$key] = $this->section($row);
            $sectionOrder[] = $key;
        }
        $content['section_order'] = $sectionOrder;

        $heroItems = $this->banners('home', 'hero', $context);
        $content['hero'] = $heroItems ? ['items' => $heroItems] : [];

        if (!empty($content['products'])) {
            $content['products']['items'] = $this->homeProductItems(
                $this->loadReferenced('products', 'product', $context, $this->products, 'product')
            );
        }

        if (!empty($content['workshop'])) {
            $content['workshop']['config']['items'] = $this->workshopItems(
                isset($content['workshop']['config']['items']) ? $content['workshop']['config']['items'] : [],
                $context->terminal()
            );
            $content['workshop']['items'] = $content['workshop']['config']['items'];
        }

        if (!empty($content['culture'])) {
            $content['culture']['config']['items'] = $this->cultureItems(
                isset($content['culture']['config']['items']) ? $content['culture']['config']['items'] : [],
                $context->terminal()
            );
            $content['culture']['items'] = $content['culture']['config']['items'];
        }

        if (!empty($content['cases'])) {
            $caseItems = $this->loadReferenced('cases', 'case', $context, $this->cases, 'caseItem');
            foreach ($caseItems as &$caseItem) {
                $caseItem['summary'] = $this->homeCaseSummary(isset($caseItem['summary']) ? $caseItem['summary'] : '');
            }
            unset($caseItem);
            $content['cases']['items'] = $caseItems;
        }

        if (!empty($content['news'])) {
            $articles = $this->loadReferenced('news', 'article', $context, $this->articles, 'article');
            $count = (int)$content['news']['display_count'];
            if ($count > 0) {
                $articles = array_slice($articles, 0, $count);
            }
            $content['news']['items'] = $articles;
            $content['news']['featured'] = isset($articles[0]) ? $articles[0] : [];
            $content['news']['secondary'] = array_slice($articles, 1, 4);
            $content['news']['list'] = $this->homeNewsItems(array_slice($articles, 5));
        }

        return new HomePageViewModel(
            $common['seo'],
            $common['layout'],
            $heroItems,
            [],
            $common['page_config'],
            $content
        );
    }

    private function section(array $row)
    {
        $config = isset($row['config']) && is_array($row['config']) ? $row['config'] : [];
        $config = (new StructuredConfigCodec())->normalizeHome($config);
        $config = $this->decorateHomeConfigIcons($config);
        $sectionKey = isset($row['section_key']) ? (string)$row['section_key'] : '';
        if (in_array($sectionKey, ['about', 'company'], true) && isset($config['media_items']) && is_array($config['media_items'])) {
            $config['media_items'] = $this->resolveHomeMediaItems($config['media_items']);
        }
        if ($sectionKey === 'about' && isset($config['media_items']) && is_array($config['media_items'])) {
            $config['media_items'] = array_slice($config['media_items'], 0, 4);
        }
        if ($sectionKey === 'cases') {
            $config['other_url'] = '#';
        }
        if ($sectionKey === 'products') {
            if (empty($config['other_url']) || $this->isRetiredInternalUrl($config['other_url'])) {
                $config['other_url'] = '/products';
            }
        }
        if ($sectionKey === 'company' && isset($config['items']) && is_array($config['items'])) {
            foreach ($config['items'] as &$configItem) {
                if (is_array($configItem) && isset($configItem['url']) && $this->isRetiredInternalUrl($configItem['url'])) {
                    $configItem['url'] = '#';
                }
            }
            unset($configItem);
        }
        $source = isset($row['resolved_content'])
            ? $row['resolved_content']
            : (isset($row['content']) ? $row['content'] : '');
        return [
            'key' => isset($row['section_key']) ? $row['section_key'] : '',
            'title' => isset($row['resolved_title']) ? $row['resolved_title'] : '',
            'subtitle' => isset($row['resolved_subtitle']) ? $row['resolved_subtitle'] : '',
            'description' => isset($row['resolved_description']) ? $row['resolved_description'] : '',
            'content' => $source,
            'content_html' => MarkdownRenderer::render($source),
            'content_text' => MarkdownRenderer::plainText($source),
            'background' => isset($row['resolved_background']) ? $row['resolved_background'] : '',
            'more_text' => isset($row['more_text']) ? $row['more_text'] : '',
            'more_url' => $sectionKey === 'products'
                ? (isset($row['more_url']) && trim((string)$row['more_url']) !== '' && !$this->isRetiredInternalUrl($row['more_url']) ? $row['more_url'] : '/products')
                : ($sectionKey === 'cases' ? '#' : (isset($row['more_url']) && !$this->isRetiredInternalUrl($row['more_url']) ? $row['more_url'] : '#')),
            'display_count' => isset($row['display_count']) ? (int)$row['display_count'] : 0,
            'config' => $config,
            'items' => [],
            'empty' => true,
        ];
    }

    private function decorateHomeConfigIcons(array $config)
    {
        if (isset($config['metrics']) && is_array($config['metrics'])) {
            $config['metrics'] = $this->icons->decorateRows($config['metrics']);
        }
        if (isset($config['items']) && is_array($config['items'])) {
            $config['items'] = $this->icons->decorateRows($config['items']);
        }
        $config['social_icon_1_view'] = $this->icons->view(isset($config['social_icon_1']) ? $config['social_icon_1'] : '');
        $config['social_icon_2_view'] = $this->icons->view(isset($config['social_icon_2']) ? $config['social_icon_2'] : '');
        return $config;
    }

    private function resolveHomeMediaItems(array $items)
    {
        $resolver = new HomeMediaResolver();
        foreach ($items as &$item) {
            if (is_array($item)) {
                $item = $resolver->resolve($item);
            }
        }
        unset($item);
        return $items;
    }

    private function workshopItems(array $items, $terminal)
    {
        $visibleField = $terminal === 'mobile' ? 'mobile_visible' : 'pc_visible';
        $resolved = [];
        foreach ($items as $item) {
            if (!is_array($item) || !$this->workshopItemVisible($item, $visibleField)) {
                continue;
            }
            $image = isset($item['image']) ? trim((string)$item['image']) : '';
            if ($terminal === 'mobile' && !empty($item['mobile_image'])) {
                $image = trim((string)$item['mobile_image']);
            }
            $item['resolved_image'] = $image;
            $resolved[] = $item;
        }
        return $resolved;
    }

    private function workshopItemVisible(array $item, $field)
    {
        if (!array_key_exists($field, $item) || $item[$field] === '') {
            return true;
        }
        return in_array($item[$field], [1, '1', true, 'true', 'on', 'yes'], true);
    }

    private function cultureItems(array $items, $terminal)
    {
        $visibleField = $terminal === 'mobile' ? 'mobile_visible' : 'pc_visible';
        $resolved = [];
        foreach ($items as $item) {
            if (!is_array($item) || !$this->workshopItemVisible($item, $visibleField)) {
                continue;
            }
            $resolved[] = $item;
        }
        return $resolved;
    }

    private function homeProductItems(array $items)
    {
        foreach ($items as &$item) {
            $item['home_summary'] = $this->homeText(isset($item['summary']) ? $item['summary'] : '', 60);
        }
        unset($item);
        return $items;
    }

    private function homeNewsItems(array $items)
    {
        foreach ($items as &$item) {
            $item['home_title'] = $this->homeText(isset($item['title']) ? $item['title'] : '', 24);
            $item['home_summary'] = $this->homeText(isset($item['summary']) ? $item['summary'] : '', 40);
        }
        unset($item);
        return $items;
    }

    private function homeText($text, $limit)
    {
        $text = trim((string)$text);
        $limit = max(0, (int)$limit);
        if ($limit === 0) {
            return '';
        }
        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            return mb_strlen($text, 'UTF-8') > $limit ? mb_substr($text, 0, $limit, 'UTF-8') . '...' : $text;
        }
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        if ($chars === false || count($chars) <= $limit) {
            return $text;
        }
        return implode('', array_slice($chars, 0, $limit)) . '...';
    }

    private function homeCaseSummary($text)
    {
        $text = trim((string)$text);
        $limit = 40;
        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            return mb_strlen($text, 'UTF-8') > $limit ? mb_substr($text, 0, $limit, 'UTF-8') . '...' : $text;
        }
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        if ($chars === false || count($chars) <= $limit) {
            return $text;
        }
        return implode('', array_slice($chars, 0, $limit)) . '...';
    }

    private function loadReferenced($section, $type, RenderContext $context, $repository, $factoryMethod)
    {
        $refs = $this->references->ordered($section, $type, $context->terminal());
        $ids = array_column($refs, 'content_id');
        $rows = $repository->publishedByIds($ids);
        $refMap = [];
        foreach ($refs as $ref) {
            $refMap[(int)$ref['content_id']] = $ref;
        }

        $items = [];
        foreach ($rows as $row) {
            $item = $this->cards->{$factoryMethod}($row, $context);
            if ($section === 'products' && $type === 'product') {
                $contentId = isset($row['id']) ? (int)$row['id'] : 0;
                if ($contentId > 0 && isset($refMap[$contentId])) {
                    $referenceCover = $this->homeReferenceCover($refMap[$contentId], $context);
                    if ($referenceCover !== '') {
                        $item['cover'] = $this->media->resolve(
                            $referenceCover,
                            '',
                            isset($item['title']) ? (string)$item['title'] : ''
                        )['url'];
                    }
                }
            }
            $items[] = $item;
        }
        $map = $this->sections->publishedMap($context->terminal());
        $limit = isset($map[$section]['display_count']) ? (int)$map[$section]['display_count'] : 0;
        return $limit > 0 ? array_slice($items, 0, $limit) : $items;
    }

    private function homeReferenceCover(array $reference, RenderContext $context)
    {
        $pcImage = trim((string)(isset($reference['pc_image']) ? $reference['pc_image'] : ''));
        if (!$context->isMobile()) {
            return $pcImage;
        }

        $mobileImage = trim((string)(isset($reference['mobile_image']) ? $reference['mobile_image'] : ''));
        if ($mobileImage !== '') {
            return $mobileImage;
        }
        return $pcImage;
    }
    private function isRetiredInternalUrl($url)
    {
        $url = trim((string)$url);
        if ($url === '' || $url === '#') {
            return false;
        }
        $path = parse_url($url, PHP_URL_PATH);
        $path = $path === null ? $url : $path;
        return (bool)preg_match('#^/(?:cases(?:/|$)|case(?:/|$)|page/(?:honor|patent|gallery|video|construction)(?:/|$))#i', (string)$path);
    }

}

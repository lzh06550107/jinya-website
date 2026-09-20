<?php
namespace app\common\service\cms\render;
use app\common\repository\cms\BannerRepositoryInterface;
use app\common\repository\cms\PageConfigRepositoryInterface;
use app\common\service\cms\PageSeoResolver;
use app\common\service\cms\BannerHighlightCodec;
use app\common\service\cms\CmsIconValue;

abstract class AbstractRenderService
{
    protected $layoutService; protected $bannerRepository; protected $pageConfigRepository; protected $media; protected $cards; protected $pagination; protected $seoResolver; protected $bannerHighlights; protected $icons;
    public function __construct(LayoutRenderService $layout, BannerRepositoryInterface $banners, PageConfigRepositoryInterface $pageConfig, MediaUrlResolver $media = null, ViewModelFactory $cards = null, PaginationService $pagination = null, PageSeoResolver $seo = null)
    { $this->layoutService=$layout;$this->bannerRepository=$banners;$this->pageConfigRepository=$pageConfig;$this->media=$media?:new MediaUrlResolver();$this->cards=$cards?:new ViewModelFactory($this->media);$this->pagination=$pagination?:new PaginationService();$this->seoResolver=$seo?:new PageSeoResolver();$this->bannerHighlights=new BannerHighlightCodec();$this->icons=new CmsIconValue(); }
    protected function common($pageKey,RenderContext $context,$row=[],$fallbackTitle='',$canonical='')
    { $config=$this->pageConfigRepository->resolved($pageKey,$context->terminal()); return ['layout'=>$this->layoutService->render($context),'page_config'=>$config,'seo'=>$this->seoResolver->resolve($row,$config,$fallbackTitle,$canonical)]; }
    protected function banners($pageKey, $position, RenderContext $context)
    {
        $rows = $this->bannerRepository->published($pageKey, $position, $context->terminal());
        $out = [];
        foreach ($rows as $row) {
            $mobile = $context->isMobile();
            $image = $mobile && !empty($row['mobile_image'])
                ? $row['mobile_image']
                : (isset($row['image']) ? $row['image'] : '');
            $mediaType = $mobile && !empty($row['mobile_media_type'])
                ? $row['mobile_media_type']
                : (isset($row['media_type']) ? $row['media_type'] : 'image');
            $video = $mobile && !empty($row['mobile_video_url'])
                ? $row['mobile_video_url']
                : (isset($row['video_url']) ? $row['video_url'] : '');
            $highlightItems = $this->bannerHighlights->decode(isset($row['highlights_json']) ? $row['highlights_json'] : '');
            if ((string)$pageKey === 'home' && (string)$position === 'hero') {
                $highlightItems = $this->bannerHighlights->homeHero($highlightItems);
            }
            $highlightItems = $this->bannerHighlights->forTerminal($highlightItems, $context->terminal());

            $title = $mobile && !empty($row['mobile_title'])
                ? $row['mobile_title']
                : (isset($row['title']) ? $row['title'] : '');
            $subtitle = $mobile && !empty($row['mobile_subtitle'])
                ? $row['mobile_subtitle']
                : (isset($row['subtitle']) ? $row['subtitle'] : '');
            // Upgrade only the exact historical PC baseline copy. Any copy
            // changed by an administrator remains authoritative.
            if (!$mobile
                && (string)$pageKey === 'home'
                && (string)$position === 'hero'
                && trim((string)$title) === '高品质包装印刷 一站式按需定制'
                && trim((string)$subtitle) === 'JINYA PACKAGE · 一站式按需定制') {
                $title = '高质量无版印刷';
                $subtitle = '不干胶·包装袋 一站式按需定制';
            }

            $out[] = [
                'id' => (int)$row['id'],
                'title' => $title,
                'subtitle' => $subtitle,
                'description' => $mobile && !empty($row['mobile_description'])
                    ? $row['mobile_description']
                    : (isset($row['description']) ? $row['description'] : ''),
                // The terminal image is also the video poster; independent poster columns are retired.
                'image' => $this->media->resolve($image, '', isset($row['title']) ? $row['title'] : '')['url'],
                'media_type' => $mediaType,
                'video_url' => $video,
                'overlay_image' => isset($row['overlay_image']) ? $row['overlay_image'] : '',
                'link_url' => $mobile && !empty($row['mobile_link_url'])
                    ? $row['mobile_link_url']
                    : (isset($row['link_url']) ? $row['link_url'] : ''),
                'button_text' => isset($row['button_text']) ? $row['button_text'] : '',
                'highlights' => $this->icons->decorateRows($highlightItems),
            ];
        }
        return $out;
    }
    protected function bannersFirst(array $pageKeys,$position,RenderContext $context)
    { foreach(array_unique($pageKeys) as $pageKey){if($pageKey==='')continue;$rows=$this->banners($pageKey,$position,$context);if(!empty($rows))return$rows;}return[]; }
    protected function configBlock(array $pageConfig,$key,array $defaults=[]){$block=isset($pageConfig['blocks'][$key])?$pageConfig['blocks'][$key]:[];$cfg=isset($block['config'])&&is_array($block['config'])?$block['config']:[];return array_merge($defaults,$cfg);}
    protected function referenceIds(array $pageConfig,$key,$type){$block=isset($pageConfig['blocks'][$key])?$pageConfig['blocks'][$key]:[];$refs=isset($block['references'])?$block['references']:[];$ids=[];foreach($refs as $r)if(isset($r['content_type'])&&$r['content_type']===$type)$ids[]=(int)$r['content_id'];return$ids;}
    protected function groupParameters(array $rows)
    {
        $groups = [];
        foreach ($rows as $row) {
            $name = isset($row['parameter_group']) && $row['parameter_group'] !== ''
                ? $row['parameter_group']
                : '基本参数';
            if (!isset($groups[$name])) {
                $groups[$name] = ['name' => $name, 'items' => []];
            }
            $groups[$name]['items'][] = $row;
        }
        return array_values($groups);
    }
}

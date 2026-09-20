<?php
namespace app\common\service\cms\render;

use app\common\service\cms\CmsUrlService;

class ViewModelFactory
{
    private $media;
    private $urls;

    public function __construct(MediaUrlResolver $media = null, CmsUrlService $urls = null)
    {
        $this->media = $media ?: new MediaUrlResolver();
        $this->urls = $urls ?: new CmsUrlService();
    }

    public function product(array $row, RenderContext $context)
    {
        $cover=$context->isMobile()?(isset($row['mobile_cover_image'])&&$row['mobile_cover_image']!==''?$row['mobile_cover_image']:(isset($row['cover_image'])?$row['cover_image']:'')):(isset($row['cover_image'])?$row['cover_image']:'');
        return ['id'=>(int)$row['id'],'title'=>(string)$row['title'],'subtitle'=>(string)(isset($row['subtitle'])?$row['subtitle']:''),'summary'=>$this->plain(isset($row['summary'])?$row['summary']:'',220),'cover'=>$this->media->resolve($cover,'',(string)$row['title'])['url'],'url'=>$this->urls->productDetail((string)$row['slug']),'slug'=>(string)$row['slug'],'category_name'=>(string)(isset($row['category_name'])?$row['category_name']:'')];
    }

    public function article(array $row, RenderContext $context)
    {
        $cover=$context->isMobile()?(isset($row['mobile_cover_image'])&&$row['mobile_cover_image']!==''?$row['mobile_cover_image']:(isset($row['cover_image'])?$row['cover_image']:'')):(isset($row['cover_image'])?$row['cover_image']:'');
        $time=(int)(isset($row['publish_time'])?$row['publish_time']:0);
        return ['id'=>(int)$row['id'],'title'=>(string)$row['title'],'summary'=>$this->plain(isset($row['summary'])?$row['summary']:'',220),'cover'=>$this->media->resolve($cover,'',(string)$row['title'])['url'],'url'=>$this->urls->newsDetail((string)$row['slug']),'slug'=>(string)$row['slug'],'category_name'=>(string)(isset($row['category_name'])?$row['category_name']:''),'category_slug'=>(string)(isset($row['category_slug'])?$row['category_slug']:''),'publish_time'=>$time,'publish_date'=>$time?date('Y-m-d',$time):'','author'=>(string)(isset($row['author'])?$row['author']:''),'source'=>(string)(isset($row['source'])?$row['source']:'')];
    }

    public function caseItem(array $row, RenderContext $context)
    {
        $cover=$context->isMobile()?(isset($row['mobile_cover_image'])&&$row['mobile_cover_image']!==''?$row['mobile_cover_image']:(isset($row['cover_image'])?$row['cover_image']:'')):(isset($row['cover_image'])?$row['cover_image']:'');
        return ['id'=>(int)$row['id'],'title'=>(string)$row['title'],'summary'=>$this->plain(isset($row['summary'])?$row['summary']:'',220),'cover'=>$this->media->resolve($cover,'',(string)$row['title'])['url'],'url'=>'#','slug'=>(string)$row['slug'],'case_type'=>(string)(isset($row['case_type'])?$row['case_type']:''),'region'=>(string)(isset($row['region'])?$row['region']:''),'construction_area'=>(string)(isset($row['construction_area'])?$row['construction_area']:'')];
    }

    public function page(array $row, RenderContext $context)
    {
        $cover=$context->isMobile()?(isset($row['mobile_cover_image'])&&$row['mobile_cover_image']!==''?$row['mobile_cover_image']:(isset($row['cover_image'])?$row['cover_image']:'')):(isset($row['cover_image'])?$row['cover_image']:'');
        return ['id'=>(int)$row['id'],'title'=>(string)$row['title'],'summary'=>$this->plain(isset($row['summary'])?$row['summary']:'',220),'cover'=>$cover,'url'=>$this->urls->page((string)$row['slug']),'slug'=>(string)$row['slug'],'page_type'=>(string)(isset($row['page_type'])?$row['page_type']:'general')];
    }

    public function plain($value,$limit=0)
    {
        $v=trim(preg_replace('/\s+/u',' ',strip_tags((string)$value)));
        if($limit>0&&function_exists('mb_strlen')&&mb_strlen($v,'UTF-8')>$limit)$v=mb_substr($v,0,$limit,'UTF-8').'…';
        return $v;
    }
}

<?php
namespace app\common\service\cms\render;
use app\common\viewmodel\cms\ErrorPageViewModel;
class ErrorPageRenderService extends AbstractRenderService
{
 public function render($statusCode,RenderContext $context){$common=$this->common('error.404',$context,[],'404','');$cfg=$this->configBlock($common['page_config'],'message',['title'=>'','description'=>'','button_text'=>'','link_url'=>'/','image'=>'']);$content=['status_code'=>(int)$statusCode,'title'=>isset($cfg['title'])?$cfg['title']:'','description'=>isset($cfg['description'])?$cfg['description']:'','button_text'=>isset($cfg['button_text'])?$cfg['button_text']:'','link_url'=>isset($cfg['link_url'])?$cfg['link_url']:'/','image'=>isset($cfg['image'])?$cfg['image']:''];return new ErrorPageViewModel($common['seo'],$common['layout'],[],[], $common['page_config'],$content);}
}

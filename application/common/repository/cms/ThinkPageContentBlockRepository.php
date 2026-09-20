<?php
namespace app\common\repository\cms;
use app\common\model\cms\PageContentBlock;
class ThinkPageContentBlockRepository extends RepositorySupport implements PageContentBlockRepositoryInterface
{ public function publishedForPage($pageId,$terminal){$field=$terminal==='mobile'?'mobile_visible':'pc_visible';$rows=$this->rows(PageContentBlock::where('page_id',(int)$pageId)->where('status','normal')->where($field,1)->order('weigh desc,id asc')->select());foreach($rows as &$r){$r['resolved_image']=$terminal==='mobile'&&!empty($r['mobile_image'])?$r['mobile_image']:$r['image'];$e=json_decode(isset($r['extra_json'])?$r['extra_json']:'',true);$r['extra']=is_array($e)?$e:[];}unset($r);return$rows;} }

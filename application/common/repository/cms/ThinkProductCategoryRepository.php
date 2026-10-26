<?php
namespace app\common\repository\cms;
use app\common\model\cms\ProductCategory;
class ThinkProductCategoryRepository extends RepositorySupport implements ProductCategoryRepositoryInterface
{
    public function publishedTree(){ $rows=$this->rows(ProductCategory::where('status','normal')->where('slug','<>','html-home-display')->order('weigh desc,id asc')->select());$by=[];foreach($rows as $r){$r['children']=[];$by[(int)$r['parent_id']][]=$r;}$build=function($id)use(&$build,&$by){$items=isset($by[$id])?$by[$id]:[];foreach($items as &$it)$it['children']=$build((int)$it['id']);unset($it);return $items;};return $build(0); }
    public function findPublishedBySlug($slug){if((string)$slug==='html-home-display')return null;return $this->row(ProductCategory::where('slug',$slug)->where('status','normal')->find());}
}

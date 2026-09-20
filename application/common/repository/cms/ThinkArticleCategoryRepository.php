<?php
namespace app\common\repository\cms;
use app\common\model\cms\ArticleCategory;
class ThinkArticleCategoryRepository extends RepositorySupport implements ArticleCategoryRepositoryInterface
{
 public function publishedTree(){ $rows=$this->rows(ArticleCategory::where('status','normal')->order('weigh desc,id asc')->select());$by=[];foreach($rows as $r){$r['children']=[];$by[(int)$r['parent_id']][]=$r;}$build=function($id)use(&$build,&$by){$items=isset($by[$id])?$by[$id]:[];foreach($items as &$it)$it['children']=$build((int)$it['id']);unset($it);return$items;};return$build(0); }
 public function findPublishedBySlug($slug){return$this->row(ArticleCategory::where('slug',$slug)->where('status','normal')->find());}
}

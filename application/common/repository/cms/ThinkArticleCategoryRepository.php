<?php
namespace app\common\repository\cms;
use app\common\model\cms\ArticleCategory;
class ThinkArticleCategoryRepository extends RepositorySupport implements ArticleCategoryRepositoryInterface
{
 public function publishedTree(){ $rows=$this->rows(ArticleCategory::where('status','normal')->where('name','not in',['常见问答','科创美新闻','新闻动态'])->order('weigh desc,id asc')->select());$by=[];foreach($rows as $r){$r['children']=[];$by[(int)$r['parent_id']][]=$r;}$build=function($id)use(&$build,&$by){$items=isset($by[$id])?$by[$id]:[];foreach($items as &$it)$it['children']=$build((int)$it['id']);unset($it);return$items;};return$build(0); }
 public function findPublishedBySlug($slug){$row=$this->row(ArticleCategory::where('slug',$slug)->where('status','normal')->find());if($row&&in_array((string)$row['name'],['常见问答','科创美新闻','新闻动态'],true))return null;return$row;}
}

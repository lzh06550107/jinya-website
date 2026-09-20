<?php
namespace app\common\repository\cms;
use app\common\model\cms\Article;
class ThinkArticleRepository extends RepositorySupport implements ArticleRepositoryInterface
{
 private function base(){return Article::alias('article')->where('article.status','published')->where('article.publish_time','<=',time());}
 private function enrich(array $rows){$ids=[];foreach($rows as $r)if(!empty($r['category_id']))$ids[]=(int)$r['category_id'];$cats=[];if($ids){$model=new \app\common\model\cms\ArticleCategory();$cr=$model->where('id','in',array_unique($ids))->select();foreach($this->rows($cr) as $c)$cats[(int)$c['id']]=$c;}foreach($rows as &$r){$c=isset($cats[(int)$r['category_id']])?$cats[(int)$r['category_id']]:[];$r['category_name']=isset($c['name'])?$c['name']:'';$r['category_slug']=isset($c['slug'])?$c['slug']:'';}unset($r);return$rows;}
 public function publishedByIds(array $ids){if(!$ids)return[];$rows=$this->enrich($this->rows($this->base()->where('article.id','in',$ids)->select()));return$this->orderedByIds($rows,$ids);}
 public function paginatePublished($categoryId,$page,$pageSize,$order){$q=$this->base();if($categoryId)$q->where('article.category_id',(int)$categoryId);$p=$q->field('article.*')->order($order?:'article.is_top desc,article.weigh desc,article.publish_time desc,article.id desc')->paginate($pageSize,false,['page'=>$page]);$out=$this->pagination($p);$out['items']=$this->enrich($out['items']);return$out;}
 public function findById($id){$r=$this->row(Article::get((int)$id));return$r?$this->enrich([$r])[0]:null;}
 public function findPublishedBySlug($slug){$r=$this->row($this->base()->field('article.*')->where('article.slug',$slug)->find());return$r?$this->enrich([$r])[0]:null;}
 public function previousNext($articleId,$publishTime){return['previous'=>$this->row($this->base()->field('article.*')->where('article.id','<>',(int)$articleId)->where('article.publish_time','<',(int)$publishTime)->order('article.publish_time desc,article.id desc')->find()),'next'=>$this->row($this->base()->field('article.*')->where('article.id','<>',(int)$articleId)->where('article.publish_time','>',(int)$publishTime)->order('article.publish_time asc,article.id asc')->find())];}
 public function related($articleId,$categoryId,$limit){$q=$this->base()->field('article.*')->where('article.id','<>',(int)$articleId);if($categoryId)$q->where('article.category_id',(int)$categoryId);return$this->enrich($this->rows($q->order('article.is_recommend desc,article.weigh desc,article.publish_time desc')->limit($limit)->select()));}
 public function latest($excludeId,$limit){return$this->enrich($this->rows($this->base()->field('article.*')->where('article.id','<>',(int)$excludeId)->order('article.is_top desc,article.publish_time desc,article.id desc')->limit($limit)->select()));}
}

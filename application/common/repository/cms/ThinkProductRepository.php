<?php
namespace app\common\repository\cms;
use app\common\model\cms\Product;
class ThinkProductRepository extends RepositorySupport implements ProductRepositoryInterface
{
    private function base(){return Product::where('status','published')->where('publish_time','<=',time());}
    public function publishedByIds(array $ids){if(!$ids)return[];$rows=$this->rows($this->base()->where('id','in',$ids)->select());return $this->orderedByIds($rows,$ids);}
    public function paginatePublished($categoryId,$page,$pageSize,$order){$q=$this->base();if($categoryId)$q->where('category_id',(int)$categoryId);$p=$q->order($order?:'weigh desc,publish_time desc,id desc')->paginate($pageSize,false,['page'=>$page]);return $this->pagination($p);}
    public function findById($id){return $this->row(Product::get((int)$id));}
    public function findPublishedBySlug($slug){return $this->row($this->base()->where('slug',$slug)->find());}
    public function previousNext($id,$publishTime){return ['previous'=>$this->row($this->base()->where('id','<>',(int)$id)->where('publish_time','<',(int)$publishTime)->order('publish_time desc,id desc')->find()),'next'=>$this->row($this->base()->where('id','<>',(int)$id)->where('publish_time','>',(int)$publishTime)->order('publish_time asc,id asc')->find())];}
    public function related($id,$categoryId,$limit){$q=$this->base()->where('id','<>',(int)$id);if($categoryId)$q->where('category_id',(int)$categoryId);return $this->rows($q->order('is_recommend desc,weigh desc,publish_time desc')->limit($limit)->select());}
}

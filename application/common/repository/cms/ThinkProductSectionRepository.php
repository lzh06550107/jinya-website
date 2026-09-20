<?php
namespace app\common\repository\cms;
use app\common\model\cms\ProductSection;
class ThinkProductSectionRepository extends RepositorySupport implements ProductSectionRepositoryInterface
{ public function publishedForProduct($productId,$terminal){$field=$terminal==='mobile'?'mobile_visible':'pc_visible';$rows=$this->rows(ProductSection::where('product_id',(int)$productId)->where('status','normal')->where($field,1)->order('weigh desc,id asc')->select());foreach($rows as &$r)$r['resolved_image']=$terminal==='mobile'&&!empty($r['mobile_image'])?$r['mobile_image']:$r['image'];unset($r);return$rows;} }

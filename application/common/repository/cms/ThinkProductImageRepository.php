<?php
namespace app\common\repository\cms;
use app\common\model\cms\ProductImage;
class ThinkProductImageRepository extends RepositorySupport implements ProductImageRepositoryInterface
{ public function publishedForProduct($productId,$terminal){$rows=$this->rows(ProductImage::where('product_id',(int)$productId)->order('weigh desc,id asc')->select());foreach($rows as &$r)$r['resolved_image']=$terminal==='mobile'&&!empty($r['mobile_image'])?$r['mobile_image']:$r['image'];unset($r);return$rows;} }

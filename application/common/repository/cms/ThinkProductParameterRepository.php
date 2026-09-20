<?php
namespace app\common\repository\cms;
use app\common\model\cms\ProductParameter;
class ThinkProductParameterRepository extends RepositorySupport implements ProductParameterRepositoryInterface
{ public function visibleForProduct($productId){return $this->rows(ProductParameter::where('product_id',(int)$productId)->where('is_visible',1)->order('weigh desc,id asc')->select());} }

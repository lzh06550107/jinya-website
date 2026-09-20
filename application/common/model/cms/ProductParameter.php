<?php

namespace app\common\model\cms;

class ProductParameter extends BaseModel
{
    protected $name = 'cms_product_parameter';

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

}

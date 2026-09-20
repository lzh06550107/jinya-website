<?php

namespace app\common\model\cms;

class ProductCategory extends BaseModel
{
    protected $name = 'cms_product_category';

    protected $append = ['status_text'];

    public function getStatusTextAttr($value, $data)
    {
        $list = $this->getNormalStatusList();
        return isset($list[$data['status']]) ? $list[$data['status']] : $data['status'];
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

}

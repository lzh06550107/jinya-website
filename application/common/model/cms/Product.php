<?php

namespace app\common\model\cms;

use app\common\service\cms\HtmlSanitizer;

class Product extends ContentModel
{
    protected $name = 'cms_product';


    public function setFeaturesAttr($value)
    {
        return HtmlSanitizer::clean($value);
    }

    public function setApplicationsAttr($value)
    {
        return HtmlSanitizer::clean($value);
    }

    public function setConstructionAttr($value)
    {
        return HtmlSanitizer::clean($value);
    }

    public function setPrecautionsAttr($value)
    {
        return HtmlSanitizer::clean($value);
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id')->order('weigh desc,id asc');
    }

    public function parameters()
    {
        return $this->hasMany(ProductParameter::class, 'product_id', 'id')->order('weigh desc,id asc');
    }

}

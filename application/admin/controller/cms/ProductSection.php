<?php

namespace app\admin\controller\cms;

use app\common\model\cms\Product;
use app\common\model\cms\ProductSection as CmsModel;

class ProductSection extends StructuredChild
{
    protected $modelClass = CmsModel::class;
    protected $parentField = 'product_id';
    protected $parentModelClass = Product::class;
    protected $parentTitleField = 'title';
    protected $parentContextLabel = '当前产品';
    protected $searchFields = 'id,section_type,title';

    public function _initialize()
    {
        parent::_initialize();
        $types = [
            'introduction' => '产品简介',
            'features' => '性能特点',
            'parameters' => '技术参数',
            'application' => '适用范围',
            'construction' => '施工说明',
            'precautions' => '注意事项',
            'custom_text' => '自定义文本',
        ];
        $this->view->assign('sectionTypeList', $types);
        $this->assignconfig('sectionTypeList', $types);
    }
}

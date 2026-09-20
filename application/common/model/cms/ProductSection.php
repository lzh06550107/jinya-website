<?php
namespace app\common\model\cms;

/**
 * Product section content is authored and stored as raw Markdown.
 * Rendering/sanitizing happens at the RenderService boundary.
 */
class ProductSection extends BaseModel
{
    protected $name = 'cms_product_section';
}

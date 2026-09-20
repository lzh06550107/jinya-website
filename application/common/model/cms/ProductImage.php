<?php

namespace app\common\model\cms;

class ProductImage extends BaseModel
{
    protected $name = 'cms_product_image';

    public static function init()
    {
        self::afterWrite(function ($row) {
            if ((int)$row['is_cover'] === 1) {
                self::where('product_id', (int)$row['product_id'])
                    ->where('id', '<>', (int)$row['id'])
                    ->update(['is_cover' => 0]);
                self::syncProductCover((int)$row['product_id'], (string)$row['image']);
            }
        });

        self::afterDelete(function ($row) {
            if ((int)$row['is_cover'] !== 1) {
                return;
            }
            $replacement = self::where('product_id', (int)$row['product_id'])
                ->order('weigh desc,id asc')
                ->find();
            if ($replacement) {
                self::where('id', (int)$replacement['id'])->update(['is_cover' => 1]);
                self::syncProductCover((int)$row['product_id'], (string)$replacement['image']);
            } else {
                self::syncProductCover((int)$row['product_id'], '');
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    protected static function syncProductCover($productId, $image)
    {
        Product::where('id', $productId)->update([
            'cover_image' => $image,
            'updatetime' => time(),
        ]);
    }
}

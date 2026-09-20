<?php

namespace app\common\service\cms\render;

/**
 * Builds the strict PC product navigation from published categories and products.
 *
 * Top-level rows come from cms_product_category. Direct products are shown as
 * normal navigation items. Child categories (for example 配套产品) are shown as
 * hover items whose children are the products assigned to that category.
 */
class ProductCategoryNavigationBuilder
{
    public function build(array $categories, array $products)
    {
        $productsByCategory = [];
        foreach ($products as $product) {
            $categoryId = isset($product['category_id']) ? (int)$product['category_id'] : 0;
            if ($categoryId <= 0) {
                continue;
            }
            if (!isset($productsByCategory[$categoryId])) {
                $productsByCategory[$categoryId] = [];
            }
            $productsByCategory[$categoryId][] = $this->productItem($product);
        }

        $groups = [];
        foreach ($categories as $category) {
            $categoryId = isset($category['id']) ? (int)$category['id'] : 0;
            if ($categoryId <= 0) {
                continue;
            }

            $items = isset($productsByCategory[$categoryId])
                ? $productsByCategory[$categoryId]
                : [];

            foreach (isset($category['children']) && is_array($category['children']) ? $category['children'] : [] as $child) {
                $items[] = $this->categoryItem($child, $productsByCategory);
            }

            $slug = isset($category['slug']) ? (string)$category['slug'] : '';
            $groups[] = [
                'id' => $categoryId,
                'name' => isset($category['name']) ? (string)$category['name'] : '',
                'slug' => $slug,
                'url' => $this->categoryUrl($slug),
                'items' => $items,
            ];
        }

        return $groups;
    }

    private function categoryItem(array $category, array $productsByCategory)
    {
        $categoryId = isset($category['id']) ? (int)$category['id'] : 0;
        $slug = isset($category['slug']) ? (string)$category['slug'] : '';
        $children = isset($productsByCategory[$categoryId])
            ? $productsByCategory[$categoryId]
            : [];

        foreach (isset($category['children']) && is_array($category['children']) ? $category['children'] : [] as $nested) {
            $children[] = [
                'type' => 'category',
                'id' => isset($nested['id']) ? (int)$nested['id'] : 0,
                'title' => isset($nested['name']) ? (string)$nested['name'] : '',
                'slug' => isset($nested['slug']) ? (string)$nested['slug'] : '',
                'url' => $this->categoryUrl(isset($nested['slug']) ? (string)$nested['slug'] : ''),
                'children' => [],
            ];
        }

        return [
            'type' => 'category',
            'id' => $categoryId,
            'title' => isset($category['name']) ? (string)$category['name'] : '',
            'slug' => $slug,
            'url' => $this->categoryUrl($slug),
            'children' => $children,
        ];
    }

    private function productItem(array $product)
    {
        $slug = isset($product['slug']) ? (string)$product['slug'] : '';
        return [
            'type' => 'product',
            'id' => isset($product['id']) ? (int)$product['id'] : 0,
            'title' => isset($product['title']) ? (string)$product['title'] : '',
            'slug' => $slug,
            'url' => '/product/' . rawurlencode($slug),
            'children' => [],
        ];
    }

    private function categoryUrl($slug)
    {
        return '/products?category=' . rawurlencode((string)$slug);
    }
}

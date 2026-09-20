<?php

namespace app\common\service\cms;

/**
 * 页面 SEO 回退规则：内容专属 SEO → 页面默认 SEO → 请求安全默认值。
 */
class PageSeoResolver
{
    public function resolve($row, array $pageConfig, $fallbackTitle, $fallbackCanonical)
    {
        $row = $this->toArray($row);
        $page = isset($pageConfig['config']) && is_array($pageConfig['config']) ? $pageConfig['config'] : [];

        $title = $this->firstNonEmpty([
            isset($row['seo_title']) ? $row['seo_title'] : '',
            isset($row['title']) ? $row['title'] : '',
            isset($page['seo_title']) ? $page['seo_title'] : '',
            $fallbackTitle,
        ]);
        $keywords = $this->firstNonEmpty([
            isset($row['seo_keywords']) ? $row['seo_keywords'] : '',
            isset($page['seo_keywords']) ? $page['seo_keywords'] : '',
        ]);
        $description = $this->firstNonEmpty([
            isset($row['seo_description']) ? $row['seo_description'] : '',
            isset($row['summary']) ? $row['summary'] : '',
            isset($page['seo_description']) ? $page['seo_description'] : '',
        ]);
        $robots = $this->firstNonEmpty([
            isset($row['robots']) ? $row['robots'] : '',
            isset($page['robots']) ? $page['robots'] : '',
            'index,follow',
        ]);
        $canonical = $this->firstNonEmpty([
            isset($row['canonical_url']) ? $row['canonical_url'] : '',
            isset($page['canonical_url']) ? $page['canonical_url'] : '',
            $fallbackCanonical,
        ]);

        return compact('title', 'keywords', 'description', 'robots', 'canonical');
    }

    protected function toArray($row)
    {
        if (!$row) {
            return [];
        }
        if (is_object($row) && method_exists($row, 'toArray')) {
            return (array)$row->toArray();
        }
        return (array)$row;
    }

    protected function firstNonEmpty(array $values)
    {
        foreach ($values as $value) {
            if ($value !== null && trim((string)$value) !== '') {
                return trim((string)$value);
            }
        }
        return '';
    }
}

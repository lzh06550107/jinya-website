<?php
namespace app\common\repository\cms;
use app\common\model\cms\Navigation;
use app\common\service\cms\CmsUrlService;
use app\common\service\cms\NavigationDeduplicationService;

class ThinkNavigationRepository implements NavigationRepositoryInterface
{
    public function publishedTree($position, $terminal)
    {
        $field = $terminal === 'mobile' ? 'mobile_visible' : 'pc_visible';
        $rows = Navigation::where('position', $position)->where('status', 'normal')->where($field, 1)
            ->order('weigh desc,id asc')->select();
        $rows = $rows ? collection($rows)->toArray() : [];
        foreach ($rows as &$row) {
            $row['url'] = $this->resolveUrl($row);
        }
        unset($row);
        $rows = (new NavigationDeduplicationService())->deduplicate($rows);
        $byParent = [];
        foreach ($rows as $row) {
            $row['children'] = [];
            $byParent[(int)$row['parent_id']][] = $row;
        }
        $build = function ($parentId) use (&$build, &$byParent) {
            $items = isset($byParent[$parentId]) ? $byParent[$parentId] : [];
            foreach ($items as &$item) { $item['children'] = $build((int)$item['id']); }
            unset($item);
            return $items;
        };
        return $build(0);
    }

    private function resolveUrl(array $row)
    {
        $type = isset($row['link_type']) ? $row['link_type'] : 'url';
        $value = isset($row['link_value']) && $row['link_value'] !== '' ? $row['link_value'] : (isset($row['url']) ? $row['url'] : '');
        $urls = new CmsUrlService();
        if ($type === 'product_category') return '#';
        if ($type === 'article_category') return $urls->newsCategory(trim($value, '/'));
        if ($type === 'page') return $urls->page(trim($value, '/'));
        return $value !== '' ? $urls->normalizeInternal($value) : '#';
    }
}

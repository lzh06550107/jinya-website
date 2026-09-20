<?php

namespace app\common\service\cms;

use think\Db;

/**
 * 前台只返回仍处于已发布状态的手动引用，同时保留数据库中的原始关系。
 */
class PageReferenceVisibility
{
    protected $publishedIdsProvider;

    public function __construct(callable $publishedIdsProvider = null)
    {
        $this->publishedIdsProvider = $publishedIdsProvider ?: function ($contentType, array $ids) {
            $tableMap = [
                'product' => 'cms_product',
                'article' => 'cms_article',
                'case' => 'cms_case',
                'page' => 'cms_page',
            ];
            if (!isset($tableMap[$contentType]) || !$ids) {
                return [];
            }
            return Db::name($tableMap[$contentType])
                ->where('id', 'in', $ids)
                ->where('status', 'published')
                ->whereNull('deletetime')
                ->column('id');
        };
    }

    public function filter(array $rows)
    {
        $grouped = [];
        foreach ($rows as $row) {
            $type = isset($row['content_type']) ? (string)$row['content_type'] : '';
            $id = isset($row['content_id']) ? (int)$row['content_id'] : 0;
            if ($type === '' || $id <= 0) {
                continue;
            }
            $grouped[$type][] = $id;
        }

        $visibleMap = [];
        foreach ($grouped as $type => $ids) {
            $ids = array_values(array_unique($ids));
            $published = call_user_func($this->publishedIdsProvider, $type, $ids);
            foreach (array_values(array_unique(array_map('intval', (array)$published))) as $id) {
                $visibleMap[$type . ':' . $id] = true;
            }
        }

        $result = [];
        foreach ($rows as $row) {
            $key = (isset($row['content_type']) ? $row['content_type'] : '') . ':' . (isset($row['content_id']) ? (int)$row['content_id'] : 0);
            if (isset($visibleMap[$key])) {
                $result[] = $row;
            }
        }
        return $result;
    }
}

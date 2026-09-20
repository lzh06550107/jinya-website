<?php
namespace app\common\service\cms;

/**
 * Collapse duplicate navigation rows that resolve to the same URL at the same level.
 * The input order defines priority: the first row wins. This preserves the repository's
 * weigh-desc/id-asc ordering while allowing children of removed parents to be re-parented.
 */
class NavigationDeduplicationService
{
    /** @return array<int,int> duplicate id => keeper id */
    public function plan(array $rows)
    {
        $duplicateOf = [];
        $maxPasses = max(1, count($rows) + 1);
        for ($pass = 0; $pass < $maxPasses; $pass++) {
            $changed = false;
            $seen = [];
            foreach ($rows as $row) {
                $id = isset($row['id']) ? (int)$row['id'] : 0;
                if ($id <= 0 || isset($duplicateOf[$id])) {
                    continue;
                }
                $url = isset($row['url']) ? trim((string)$row['url']) : '';
                if ($url === '' || $url === '#') {
                    continue;
                }
                $parentId = $this->canonicalParent(isset($row['parent_id']) ? (int)$row['parent_id'] : 0, $duplicateOf);
                $position = isset($row['position']) ? (string)$row['position'] : 'header';
                $key = $position . "\n" . $parentId . "\n" . $url;
                if (isset($seen[$key])) {
                    $duplicateOf[$id] = (int)$seen[$key];
                    $changed = true;
                    continue;
                }
                $seen[$key] = $id;
            }
            if (!$changed) {
                break;
            }
        }
        foreach ($duplicateOf as $id => $keeper) {
            $duplicateOf[$id] = $this->canonicalParent($keeper, $duplicateOf);
        }
        return $duplicateOf;
    }

    /** @return array<int,array<string,mixed>> */
    public function deduplicate(array $rows)
    {
        $plan = $this->plan($rows);
        $out = [];
        foreach ($rows as $row) {
            $id = isset($row['id']) ? (int)$row['id'] : 0;
            if ($id > 0 && isset($plan[$id])) {
                continue;
            }
            $parentId = isset($row['parent_id']) ? (int)$row['parent_id'] : 0;
            $row['parent_id'] = $this->canonicalParent($parentId, $plan);
            $out[] = $row;
        }
        return $out;
    }

    private function canonicalParent($parentId, array $duplicateOf)
    {
        $parentId = (int)$parentId;
        $visited = [];
        while ($parentId > 0 && isset($duplicateOf[$parentId]) && !isset($visited[$parentId])) {
            $visited[$parentId] = true;
            $parentId = (int)$duplicateOf[$parentId];
        }
        return $parentId;
    }
}

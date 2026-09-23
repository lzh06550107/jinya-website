<?php

namespace app\common\service\cms;

use app\common\service\cms\render\CmsCacheInvalidator;
use think\Db;

/**
 * 首页固定功能块显示顺序的唯一写入边界。
 *
 * cms_page_block 保存后台页面管理的固定块顺序；真正的首页模块由
 * cms_home_section 消费 weigh。两处必须在同一事务中保持一致。
 */
class HomePageBlockOrderService
{
    private $schema;
    private $cache;

    public function __construct(HomeSectionEditorSchema $schema = null, CmsCacheInvalidator $cache = null)
    {
        $this->schema = $schema ?: new HomeSectionEditorSchema();
        $this->cache = $cache ?: new CmsCacheInvalidator();
    }

    /**
     * @param array $rows 当前全部未删除的 home page blocks，至少含 id/block_key
     * @param array $requestedIds 前端拖拽后的完整 ID 顺序
     * @return array<int,array{id:int,block_key:string,weigh:int}>
     */
    public function plan(array $rows, array $requestedIds)
    {
        $byId = [];
        $heroId = 0;
        foreach ($rows as $row) {
            if (!is_array($row) || !isset($row['id'], $row['block_key'])) {
                throw new \InvalidArgumentException('首页功能块排序数据不完整');
            }
            $id = (int)$row['id'];
            if ($id <= 0 || isset($byId[$id])) {
                throw new \InvalidArgumentException('首页功能块 ID 无效或重复');
            }
            $blockKey = trim((string)$row['block_key']);
            $byId[$id] = ['id' => $id, 'block_key' => $blockKey];
            if ($blockKey === 'hero') {
                $heroId = $id;
            }
        }
        if (!$byId || $heroId <= 0) {
            throw new \InvalidArgumentException('首页轮播功能块不存在，无法排序');
        }

        $requested = [];
        foreach ($requestedIds as $id) {
            $id = (int)$id;
            if ($id <= 0 || isset($requested[$id])) {
                throw new \InvalidArgumentException('排序 ID 无效或重复');
            }
            $requested[$id] = true;
        }
        $knownIds = array_keys($byId);
        $requestedKeys = array_keys($requested);
        sort($knownIds, SORT_NUMERIC);
        sort($requestedKeys, SORT_NUMERIC);
        if ($knownIds !== $requestedKeys) {
            throw new \InvalidArgumentException('请在未筛选的完整首页功能块列表中拖拽排序');
        }

        $orderedIds = [$heroId];
        foreach ($requestedIds as $id) {
            $id = (int)$id;
            if ($id !== $heroId) {
                $orderedIds[] = $id;
            }
        }

        $weight = count($orderedIds) * 10;
        $plan = [];
        foreach ($orderedIds as $id) {
            $plan[] = [
                'id' => $id,
                'block_key' => $byId[$id]['block_key'],
                'weigh' => $weight,
            ];
            $weight -= 10;
        }
        return $plan;
    }

    public function reorder(array $requestedIds)
    {
        $rows = $this->homeRowsById();
        $plan = $this->plan($rows, $requestedIds);
        $now = time();

        Db::startTrans();
        try {
            foreach ($plan as $entry) {
                Db::name('cms_page_block')
                    ->where('id', (int)$entry['id'])
                    ->where('page_key', 'home')
                    ->whereNull('deletetime')
                    ->update(['weigh' => (int)$entry['weigh'], 'updatetime' => $now]);

                $key = (string)$entry['block_key'];
                if ($key === 'hero' || !$this->schema->has($key)) {
                    continue;
                }
                Db::name('cms_home_section')
                    ->where('section_key', $key)
                    ->whereNull('deletetime')
                    ->update(['weigh' => (int)$entry['weigh'], 'updatetime' => $now]);
            }
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }

        $this->cache->invalidateLayout();
        return $plan;
    }

    public function reset()
    {
        $rows = $this->homeRowsById();
        $ids = array_map(function ($row) {
            return (int)$row['id'];
        }, $rows);
        return $this->reorder($ids);
    }

    private function homeRowsById()
    {
        $rows = Db::name('cms_page_block')
            ->where('page_key', 'home')
            ->where('block_key', 'not in', PageSchemaRegistry::retiredHomeBlockKeys())
            ->whereNull('deletetime')
            ->field('id,block_key')
            ->order('id asc')
            ->select();
        if ($rows instanceof \think\Collection) {
            $rows = $rows->toArray();
        } elseif (is_object($rows) && method_exists($rows, 'toArray')) {
            $rows = $rows->toArray();
        }
        return is_array($rows) ? $rows : [];
    }
}

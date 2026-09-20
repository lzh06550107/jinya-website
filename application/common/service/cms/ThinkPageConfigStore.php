<?php

namespace app\common\service\cms;

use think\Db;

/**
 * PageConfigStoreInterface 的 ThinkPHP 5 实现。
 */
class ThinkPageConfigStore implements PageConfigStoreInterface
{
    protected $tables = [
        'page_config' => 'cms_page_config',
        'page_block' => 'cms_page_block',
        'layout_component' => 'cms_layout_component',
    ];

    public function transaction(callable $callback)
    {
        Db::startTrans();
        try {
            $result = $callback();
            Db::commit();
            return $result;
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }
    }

    public function find($entity, $id)
    {
        return Db::name($this->table($entity))
            ->where('id', (int)$id)
            ->whereNull('deletetime')
            ->find();
    }

    public function updateVersioned($entity, $id, $version, array $data)
    {
        $data['version'] = (int)$version + 1;
        $data['updatetime'] = time();
        $affected = Db::name($this->table($entity))
            ->where('id', (int)$id)
            ->where('version', (int)$version)
            ->whereNull('deletetime')
            ->update($data);
        if ((int)$affected !== 1) {
            return false;
        }
        return $this->find($entity, $id);
    }

    public function assertReferencesPublishable(array $references)
    {
        $tableMap = [
            'product' => 'cms_product',
            'article' => 'cms_article',
            'case' => 'cms_case',
            'page' => 'cms_page',
        ];
        $grouped = [];
        foreach ($references as $reference) {
            $type = $reference['content_type'];
            if (!isset($tableMap[$type])) {
                throw new \InvalidArgumentException('不支持的业务内容引用类型：' . $type);
            }
            $grouped[$type][] = (int)$reference['content_id'];
        }
        foreach ($grouped as $type => $ids) {
            $ids = array_values(array_unique(array_filter($ids)));
            if (!$ids) {
                continue;
            }
            $count = Db::name($tableMap[$type])
                ->where('id', 'in', $ids)
                ->where('status', 'published')
                ->whereNull('deletetime')
                ->count();
            if ((int)$count !== count($ids)) {
                throw new \InvalidArgumentException('引用内容未发布、已下架或不存在');
            }
        }
    }

    public function replaceReferences($blockId, array $references)
    {
        $now = time();
        Db::name('cms_page_block_reference')->where('page_block_id', (int)$blockId)->delete();
        foreach ($references as $reference) {
            Db::name('cms_page_block_reference')->insert([
                'page_block_id' => (int)$blockId,
                'content_type' => $reference['content_type'],
                'content_id' => (int)$reference['content_id'],
                'weigh' => (int)$reference['weigh'],
                'status' => 'normal',
                'createtime' => $now,
                'updatetime' => $now,
                'deletetime' => null,
            ]);
        }
    }

    public function findPageByKey($pageKey)
    {
        return Db::name('cms_page_config')
            ->where('page_key', (string)$pageKey)
            ->whereNull('deletetime')
            ->find();
    }

    public function findBlocksByPage($pageKey)
    {
        return Db::name('cms_page_block')
            ->where('page_key', (string)$pageKey)
            ->whereNull('deletetime')
            ->order('weigh desc,id asc')
            ->select();
    }

    public function findLayoutsByKeys(array $keys)
    {
        $keys = array_values(array_unique(array_filter(array_map('strval', $keys))));
        if (!$keys) {
            return [];
        }
        $rows = Db::name('cms_layout_component')
            ->where('component_key', 'in', $keys)
            ->whereNull('deletetime')
            ->select();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['component_key']] = $row;
        }
        return $result;
    }

    public function findReferencesByBlockIds(array $blockIds)
    {
        $blockIds = array_values(array_unique(array_filter(array_map('intval', $blockIds))));
        $result = [];
        foreach ($blockIds as $blockId) {
            $result[$blockId] = [];
        }
        if (!$blockIds) {
            return $result;
        }
        $rows = Db::name('cms_page_block_reference')
            ->where('page_block_id', 'in', $blockIds)
            ->where('status', 'normal')
            ->whereNull('deletetime')
            ->order('weigh desc,id asc')
            ->select();
        $rows = (new PageReferenceVisibility())->filter($rows ?: []);
        foreach ($rows as $row) {
            $result[(int)$row['page_block_id']][] = $row;
        }
        return $result;
    }

    public function findPageKeysByLayout($componentKey)
    {
        $rows = Db::name('cms_page_config')
            ->where(function ($query) use ($componentKey) {
                $query->where('pc_header_key', $componentKey)
                    ->whereOr('pc_footer_key', $componentKey)
                    ->whereOr('mobile_header_key', $componentKey)
                    ->whereOr('mobile_footer_key', $componentKey);
            })
            ->whereNull('deletetime')
            ->column('page_key');
        return array_values(array_unique(array_map('strval', $rows ?: [])));
    }

    protected function table($entity)
    {
        if (!isset($this->tables[$entity])) {
            throw new \InvalidArgumentException('不支持的页面配置实体：' . $entity);
        }
        return $this->tables[$entity];
    }

    protected function encode($value)
    {
        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \InvalidArgumentException('配置无法转换为 JSON');
        }
        return $json;
    }
}

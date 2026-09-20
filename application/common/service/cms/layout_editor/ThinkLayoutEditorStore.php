<?php

namespace app\common\service\cms\layout_editor;

use app\common\service\cms\PageConfigConflictException;
use app\common\service\cms\SiteConfigDefinitionRegistry;
use think\Db;

/**
 * ThinkPHP 5 数据库存储实现。
 */
class ThinkLayoutEditorStore implements LayoutEditorStoreInterface
{
    public function findLayoutById($id)
    {
        $row = Db::name('cms_layout_component')
            ->where('id', (int)$id)
            ->whereNull('deletetime')
            ->find();
        return $this->rowArray($row);
    }

    public function findLayoutByKey($key)
    {
        $row = Db::name('cms_layout_component')
            ->where('component_key', (string)$key)
            ->whereNull('deletetime')
            ->find();
        return $this->rowArray($row);
    }

    public function loadSiteConfig(array $names)
    {
        $names = array_values(array_unique(array_filter(array_map('strval', $names))));
        if (!$names) {
            return [];
        }
        $rows = Db::name('config')->where('name', 'in', $names)->select();
        $values = [];
        foreach ($this->rowsArray($rows) as $row) {
            $values[(string)$row['name']] = isset($row['value']) ? (string)$row['value'] : '';
        }
        $definitions = SiteConfigDefinitionRegistry::pick($names);
        foreach ($names as $name) {
            if (!array_key_exists($name, $values)) {
                $values[$name] = isset($definitions[$name]['default']) ? (string)$definitions[$name]['default'] : '';
            }
        }
        return $values;
    }

    public function loadNavigation($position)
    {
        $rows = Db::name('cms_navigation')
            ->where('position', (string)$position)
            ->whereNull('deletetime')
            ->order('weigh desc,id asc')
            ->select();
        return $this->rowsArray($rows);
    }

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

    public function updateLayoutVersioned($id, $expectedVersion, array $fields)
    {
        $fields['version'] = (int)$expectedVersion + 1;
        $fields['updatetime'] = time();
        $affected = Db::name('cms_layout_component')
            ->where('id', (int)$id)
            ->where('version', (int)$expectedVersion)
            ->whereNull('deletetime')
            ->update($fields);
        if ((int)$affected !== 1) {
            throw new PageConfigConflictException('公共布局已被其他管理员修改，请重新打开后再保存');
        }
        return $this->findLayoutById($id);
    }

    public function saveSiteConfig(array $values)
    {
        if (!$values) {
            return;
        }
        $definitions = SiteConfigDefinitionRegistry::pick(array_keys($values));
        foreach ($values as $name => $value) {
            $definition = $definitions[$name];
            $existing = Db::name('config')->where('name', $name)->find();
            if ($existing) {
                Db::name('config')->where('id', (int)$existing['id'])->update(['value' => (string)$value]);
                continue;
            }
            Db::name('config')->insert([
                'name' => $name,
                'group' => 'basic',
                'title' => $definition['title'],
                'tip' => '',
                'type' => $definition['type'],
                'visible' => '',
                'value' => (string)$value,
                'content' => '',
                'rule' => $definition['rule'],
                'extend' => '',
                'setting' => '',
            ]);
        }
    }

    public function saveNavigation($position, array $items)
    {
        $position = (string)$position;
        $existingRows = $this->loadNavigation($position);
        $existing = [];
        foreach ($existingRows as $row) {
            $existing[(int)$row['id']] = $row;
        }

        $submittedIds = [];
        foreach ($items as $item) {
            $id = isset($item['id']) ? (int)$item['id'] : 0;
            if ($id > 0) {
                if (!isset($existing[$id])) {
                    throw new \InvalidArgumentException('导航项不存在或不属于当前导航位置：' . $id);
                }
                $submittedIds[$id] = true;
            }
        }
        foreach ($items as $item) {
            $parentId = isset($item['parent_id']) ? (int)$item['parent_id'] : 0;
            if ($parentId > 0 && (!isset($existing[$parentId]) || !isset($submittedIds[$parentId]))) {
                throw new \InvalidArgumentException('父导航必须是当前列表中已保存的导航项');
            }
        }

        $now = time();
        foreach ($items as $item) {
            $id = isset($item['id']) ? (int)$item['id'] : 0;
            $data = [
                'parent_id' => isset($item['parent_id']) ? (int)$item['parent_id'] : 0,
                'title' => isset($item['title']) ? (string)$item['title'] : '',
                'url' => isset($item['url']) ? (string)$item['url'] : '',
                'target' => isset($item['target']) ? (string)$item['target'] : '_self',
                'icon' => isset($item['icon']) ? (string)$item['icon'] : '',
                'position' => $position,
                'pc_visible' => !empty($item['pc_visible']) ? 1 : 0,
                'mobile_visible' => !empty($item['mobile_visible']) ? 1 : 0,
                'weigh' => isset($item['weigh']) ? (int)$item['weigh'] : 0,
                'status' => isset($item['status']) ? (string)$item['status'] : 'normal',
                'updatetime' => $now,
                'deletetime' => null,
            ];
            if ($id > 0) {
                Db::name('cms_navigation')->where('id', $id)->where('position', $position)->whereNull('deletetime')->update($data);
            } else {
                $data['slug'] = '';
                $data['link_type'] = 'url';
                $data['link_value'] = $data['url'];
                $data['createtime'] = $now;
                Db::name('cms_navigation')->insert($data);
            }
        }

        foreach ($existing as $id => $row) {
            if (!isset($submittedIds[$id])) {
                Db::name('cms_navigation')->where('id', $id)->where('position', $position)->whereNull('deletetime')->update([
                    'deletetime' => $now,
                    'updatetime' => $now,
                ]);
            }
        }
    }

    private function rowArray($row)
    {
        if (!$row) {
            return null;
        }
        if (is_array($row)) {
            return $row;
        }
        if (is_object($row) && method_exists($row, 'toArray')) {
            return $row->toArray();
        }
        return (array)$row;
    }

    private function rowsArray($rows)
    {
        $result = [];
        foreach ($rows ?: [] as $row) {
            $result[] = $this->rowArray($row);
        }
        return $result;
    }
}

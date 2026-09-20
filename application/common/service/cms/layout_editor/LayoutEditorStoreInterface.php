<?php

namespace app\common\service\cms\layout_editor;

/**
 * 公共布局聚合编辑器持久化边界。
 */
interface LayoutEditorStoreInterface
{
    public function findLayoutById($id);
    public function findLayoutByKey($key);
    public function loadSiteConfig(array $names);
    public function loadNavigation($position);
    public function transaction(callable $callback);
    public function updateLayoutVersioned($id, $expectedVersion, array $fields);
    public function saveSiteConfig(array $values);
    public function saveNavigation($position, array $items);
}

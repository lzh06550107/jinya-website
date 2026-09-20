<?php

namespace app\common\service\cms;

/**
 * 页面化 CMS 的持久化边界。
 *
 * 服务层只依赖本接口，便于在不启动 ThinkPHP 的情况下进行事务、
 * 乐观锁和缓存联动测试。
 */
interface PageConfigStoreInterface
{
    public function transaction(callable $callback);

    public function find($entity, $id);

    public function updateVersioned($entity, $id, $version, array $data);

    public function assertReferencesPublishable(array $references);

    public function replaceReferences($blockId, array $references);

    public function findPageByKey($pageKey);

    public function findBlocksByPage($pageKey);

    public function findLayoutsByKeys(array $keys);

    public function findReferencesByBlockIds(array $blockIds);

    public function findPageKeysByLayout($componentKey);
}

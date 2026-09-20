<?php

namespace app\common\service\cms;

use app\common\model\cms\Article;
use app\common\model\cms\Product;
use app\common\model\cms\ProductImage;
use app\common\model\cms\ProductParameter;
use app\common\model\cms\ProductSection;
use app\common\model\cms\Page;
use think\Db;

class PublishService
{
    protected $models = [
        'product' => Product::class,
        'article' => Article::class,
        'page' => Page::class,
    ];

    public function submit($type, $id, $adminId)
    {
        return $this->transition($type, $id, PublishStateMachine::PENDING, $adminId);
    }

    public function approve($type, $id, $adminId, $publishTime = null)
    {
        $target = $publishTime && $publishTime > time() ? PublishStateMachine::SCHEDULED : PublishStateMachine::PUBLISHED;
        return $this->transition($type, $id, $target, $adminId, '', $publishTime);
    }

    public function reject($type, $id, $adminId, $reason)
    {
        if (!$reason) {
            throw new \InvalidArgumentException('驳回时必须填写原因');
        }
        return $this->transition($type, $id, PublishStateMachine::REJECTED, $adminId, $reason);
    }

    public function offline($type, $id, $adminId, $reason = '')
    {
        return $this->transition($type, $id, PublishStateMachine::OFFLINE, $adminId, $reason);
    }

    /**
     * 将到期的定时内容切换为已发布。
     *
     * @param int|null $now
     * @return array{published:int,failed:array}
     */
    public function publishDueScheduled($now = null)
    {
        $now = $now === null ? time() : (int)$now;
        $result = ['published' => 0, 'failed' => []];
        foreach ($this->models as $type => $class) {
            $rows = $class::where('status', PublishStateMachine::SCHEDULED)
                ->where('publish_time', '<=', $now)
                ->order('publish_time asc,id asc')
                ->select();
            foreach ($rows as $row) {
                Db::startTrans();
                try {
                    PublishStateMachine::assertTransition($row['status'], PublishStateMachine::PUBLISHED);
                    ContentGuard::assertPublishable($type, $row->getData());
                    $row->allowField(true)->save([
                        'status' => PublishStateMachine::PUBLISHED,
                        'reject_reason' => '',
                    ]);
                    Db::commit();
                    CacheService::clearByType($type, $row['id'], isset($row['slug']) ? $row['slug'] : null);
                    $result['published']++;
                } catch (\Exception $e) {
                    Db::rollback();
                    $result['failed'][] = [
                        'type' => $type,
                        'id' => isset($row['id']) ? (int)$row['id'] : 0,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }
        return $result;
    }

    public function duplicate($type, $id, $adminId)
    {
        $class = $this->modelClass($type);
        $row = $class::get($id);
        if (!$row) {
            throw new \InvalidArgumentException('内容不存在');
        }
        Db::startTrans();
        try {
            $data = $row->getData();
            unset($data['id'], $data['createtime'], $data['updatetime'], $data['deletetime']);
            $data['title'] = (isset($data['title']) ? $data['title'] : '') . '（副本）';
            if (!empty($data['slug'])) {
                $suffix = '-copy-' . date('YmdHis') . '-' . mt_rand(100, 999);
                $data['slug'] = substr((string)$data['slug'], 0, max(1, 180 - strlen($suffix))) . $suffix;
            }
            $data['status'] = PublishStateMachine::DRAFT;
            $data['publish_time'] = null;
            $data['publish_admin_id'] = 0;
            $data['audit_admin_id'] = 0;
            $data['reject_reason'] = '';
            $data['views'] = 0;
            $data['source_key'] = '';
            $data['edited_by_admin'] = 1;
            $model = new $class();
            $model->allowField(true)->save($data);
            $this->duplicateRelations($type, (int)$row['id'], (int)$model['id']);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }
        return $model;
    }

    protected function duplicateRelations($type, $sourceId, $targetId)
    {
        if ($type !== 'product') {
            return;
        }
        foreach (ProductImage::where('product_id', $sourceId)->select() as $row) {
            $data = $row->getData();
            unset($data['id'], $data['createtime'], $data['updatetime'], $data['deletetime']);
            $data['product_id'] = $targetId;
            (new ProductImage())->allowField(true)->save($data);
        }
        foreach (ProductParameter::where('product_id', $sourceId)->select() as $row) {
            $data = $row->getData();
            unset($data['id'], $data['createtime'], $data['updatetime'], $data['deletetime']);
            $data['product_id'] = $targetId;
            (new ProductParameter())->allowField(true)->save($data);
        }
        foreach (ProductSection::where('product_id', $sourceId)->select() as $row) {
            $data = $row->getData();
            unset($data['id'], $data['createtime'], $data['updatetime'], $data['deletetime']);
            $data['product_id'] = $targetId;
            $data['edited_by_admin'] = 1;
            (new ProductSection())->allowField(true)->save($data);
        }
    }

    protected function transition($type, $id, $to, $adminId, $remark = '', $publishTime = null)
    {
        $class = $this->modelClass($type);
        $row = $class::get($id);
        if (!$row) {
            throw new \InvalidArgumentException('内容不存在');
        }
        $from = $row['status'];
        PublishStateMachine::assertTransition($from, $to);
        if (in_array($to, [PublishStateMachine::PUBLISHED, PublishStateMachine::SCHEDULED], true)) {
            ContentGuard::assertPublishable($type, $row->getData());
        }

        Db::startTrans();
        try {
            $data = [
                'status' => $to,
                'audit_admin_id' => $adminId,
                'reject_reason' => $to === PublishStateMachine::REJECTED ? $remark : '',
            ];
            if (in_array($to, [PublishStateMachine::PUBLISHED, PublishStateMachine::SCHEDULED], true)) {
                $data['publish_admin_id'] = $adminId;
                $data['publish_time'] = $publishTime ?: time();
            }
            $row->allowField(true)->save($data);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
        CacheService::clearByType($type, $row['id'], isset($row['slug']) ? $row['slug'] : null);
        return $row;
    }

    protected function modelClass($type)
    {
        if (!isset($this->models[$type])) {
            throw new \InvalidArgumentException('不支持的内容类型：' . $type);
        }
        return $this->models[$type];
    }
}

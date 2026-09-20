<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;
use app\common\service\cms\PageReferenceGuard;
use app\common\service\cms\PublishService;
use app\common\service\cms\AdminContentMutationService;
use app\common\service\cms\render\CmsCacheInvalidator;
use think\Cache;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;

abstract class Content extends Backend
{
    protected $modelClass = '';
    protected $contentType = '';
    protected $modelValidate = true;
    protected $modelSceneValidate = false;
    protected $searchFields = 'id,title,slug';
    protected $multiFields = 'weigh,is_recommend';

    public function _initialize()
    {
        parent::_initialize();
        if (!$this->modelClass || !$this->contentType) {
            throw new \RuntimeException('CMS内容控制器未配置模型或类型');
        }
        $class = $this->modelClass;
        $this->model = new $class();
        $this->view->assign('statusList', $this->model->getStatusList());
        $this->assignconfig('statusList', $this->model->getStatusList());
        $this->afterCmsInitialize();
    }

    protected function afterCmsInitialize()
    {
    }


    protected function preExcludeFields($params)
    {
        $params = parent::preExcludeFields($params);
        return (new AdminContentMutationService())->markEdited($params);
    }

    public function add()
    {
        if (!$this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->preExcludeFields($this->request->post('row/a', []));
        if (!$params) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $result = false;
        Db::startTrans();
        try {
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }
            $result = $this->model->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        (new CmsCacheInvalidator())->invalidateContent($this->contentType, (int)$this->model->getAttr('id'), isset($params['slug']) ? $params['slug'] : '');
        $this->success();
    }

    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (!$this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->preExcludeFields($this->request->post('row/a', []));
        if (!$params) {
            $this->error(__('Parameter %s can not be empty', ''));
        }

        // ThinkPHP's unique validator excludes the current row only when the
        // submitted validation data contains the model primary key. Edit forms
        // do not post the id, so inject the authoritative current id here.
        $pk = $row->getPk();
        if (is_string($pk) && $pk !== '') {
            $params[$pk] = $row->getAttr($pk);
        }

        $newSlug = isset($params['slug']) ? (string)$params['slug'] : (isset($row['slug']) ? (string)$row['slug'] : '');
        $result = false;
        Db::startTrans();
        try {
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were updated'));
        }
        (new CmsCacheInvalidator())->invalidateContent($this->contentType, (int)$row['id'], $newSlug);
        $this->success();
    }

    public function submit($ids = null)
    {
        return $this->runPublishAction('submit', $ids, '已提交审核');
    }

    public function publish($ids = null)
    {
        $publishTime = $this->request->post('publish_time', '');
        $timestamp = $publishTime ? strtotime($publishTime) : null;
        return $this->runPublishAction('approve', $ids, $timestamp && $timestamp > time() ? '已设置定时发布' : '发布成功', [$timestamp]);
    }

    public function reject($ids = null)
    {
        $reason = trim($this->request->post('reason', ''));
        return $this->runPublishAction('reject', $ids, '已驳回', [$reason]);
    }

    public function offline($ids = null)
    {
        $reason = trim($this->request->post('reason', ''));
        return $this->runPublishAction('offline', $ids, '已下架', [$reason]);
    }

    public function del($ids = '')
    {
        $rawIds = $ids ?: $this->request->request('ids');
        $idList = array_values(array_unique(array_filter(array_map('intval', explode(',', (string)$rawIds)))));
        try {
            (new PageReferenceGuard())->assertDeletable($this->contentType, $idList);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        return parent::del($ids);
    }

    public function duplicate($ids = null)
    {
        $id = $this->resolveId($ids);
        try {
            $row = (new PublishService())->duplicate($this->contentType, $id, (int)$this->auth->id);
        } catch (\Throwable $e) {
            $message = trim((string)$e->getMessage());
            $this->error('复制失败：' . ($message !== '' ? $message : get_class($e)));
        }
        $this->success('复制成功', '', ['id' => $row['id']]);
    }

    public function preview($ids = null)
    {
        $id = $this->resolveId($ids);
        $row = $this->model->get($id);
        if (!$row) {
            $this->error('内容不存在');
        }
        $token = hash('sha256', $this->contentType . '|' . $row['id'] . '|' . microtime(true) . '|' . mt_rand());
        Cache::set('cms:preview:' . $token, ['type' => $this->contentType, 'id' => $row['id']], 300);
        $this->redirect('/cms-preview/' . $token);
    }

    protected function previewPath($row)
    {
        return '/';
    }

    protected function runPublishAction($method, $ids, $successMessage, array $extra = [])
    {
        $idList = $this->resolveIds($ids);
        $service = new PublishService();
        $successCount = 0;
        $failures = [];

        foreach ($idList as $id) {
            try {
                $args = array_merge([$this->contentType, $id, (int)$this->auth->id], $extra);
                call_user_func_array([$service, $method], $args);
                $successCount++;
            } catch (\Throwable $e) {
                $failures[] = [
                    'id' => (int)$id,
                    'message' => trim((string)$e->getMessage()) ?: get_class($e),
                ];
            }
        }

        if ($successCount === 0) {
            $first = isset($failures[0]['message']) ? $failures[0]['message'] : '操作失败';
            $this->error($first);
        }

        $message = $successMessage;
        if (count($idList) > 1 || $failures) {
            $message = sprintf('成功 %d 条，失败 %d 条', $successCount, count($failures));
            if ($failures) {
                $parts = [];
                foreach (array_slice($failures, 0, 3) as $failure) {
                    $parts[] = '#' . $failure['id'] . ' ' . $failure['message'];
                }
                $message .= '：' . implode('；', $parts);
                if (count($failures) > 3) {
                    $message .= '；其余 ' . (count($failures) - 3) . ' 条失败原因请逐条检查';
                }
            }
        }

        $this->success($message);
    }

    protected function resolveIds($ids)
    {
        $raw = $ids ?: $this->request->request('ids');
        $idList = array_values(array_unique(array_filter(array_map('intval', explode(',', (string)$raw)))));
        if (!$idList) {
            $this->error('请选择至少一条记录');
        }
        return $idList;
    }

    protected function resolveId($ids)
    {
        $idList = $this->resolveIds($ids);
        if (count($idList) !== 1) {
            $this->error('请选择一条记录');
        }
        return $idList[0];
    }
}

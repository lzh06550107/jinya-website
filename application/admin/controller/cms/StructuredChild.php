<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;
use app\common\service\cms\render\CmsCacheInvalidator;

abstract class StructuredChild extends Backend
{
    protected $model = null;
    protected $modelClass = '';
    protected $parentField = '';
    protected $parentValue = null;
    protected $parentModelClass = '';
    protected $parentTitleField = 'title';
    protected $parentContextLabel = '';
    protected $markAdminEdit = true;
    protected $usesSourceKey = true;
    protected $searchFields = 'id,title';

    public function _initialize()
    {
        parent::_initialize();
        $class = $this->modelClass;
        $this->model = new $class();
        $this->parentValue = $this->request->request($this->parentField, null);
        if ($this->parentValue === null || $this->parentValue === '') {
            $this->parentValue = $this->request->get($this->parentField, '');
        }
        $this->view->assign('parentField', $this->parentField);
        $this->view->assign('parentValue', $this->parentValue);
        $this->view->assign('statusList', ['normal' => '启用', 'hidden' => '停用']);
        $this->assignconfig('parentField', $this->parentField);
        $this->assignconfig('parentValue', $this->parentValue);
        $this->assignParentContext();
    }

    protected function assignParentContext()
    {
        $parentId = $this->parentValue;
        $parentExists = true;
        $parentTitle = '';

        if ($this->parentModelClass !== '') {
            $parentExists = false;
            if ($parentId !== '' && $parentId !== null) {
                $class = $this->parentModelClass;
                $parent = $class::get((int)$parentId);
                if ($parent) {
                    $parentExists = true;
                    $field = $this->parentTitleField ?: 'title';
                    $parentTitle = isset($parent[$field]) ? (string)$parent[$field] : '';
                }
            }
        }

        $this->view->assign('parentExists', $parentExists);
        $this->view->assign('parentTitle', $parentTitle);
        $this->view->assign('parentContextLabel', $this->parentContextLabel);
        $this->view->assign('parentId', $parentId);
        $this->assignconfig('parentExists', $parentExists);
        $this->assignconfig('parentTitle', $parentTitle);
        $this->assignconfig('parentContextLabel', $this->parentContextLabel);
        $this->assignconfig('parentId', $parentId);
    }

    public function index()
    {
        $this->request->filter(['strip_tags', 'trim']);
        if (!$this->request->isAjax()) {
            return $this->view->fetch();
        }
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $query = $this->model->where($where);
        if ($this->parentValue !== '' && $this->parentValue !== null) {
            $query->where($this->parentField, $this->parentValue);
        }
        $list = $query->order($sort, $order)->paginate($limit);
        return json(['total' => $list->total(), 'rows' => $list->items()]);
    }

    protected function preExcludeFields($params)
    {
        $params = parent::preExcludeFields($params);
        if ($this->parentField !== '' && (!isset($params[$this->parentField]) || $params[$this->parentField] === '')) {
            $params[$this->parentField] = $this->parentValue;
        }
        if ($this->markAdminEdit) {
            $params['edited_by_admin'] = 1;
        }
        if ($this->usesSourceKey && empty($params['source_key'])) {
            $params['source_key'] = 'admin:' . str_replace('.', '', uniqid('', true));
        }
        (new CmsCacheInvalidator())->invalidateLayout();
        return $params;
    }

    public function del($ids = '')
    {
        (new CmsCacheInvalidator())->invalidateLayout();
        return parent::del($ids);
    }

    public function multi($ids = '')
    {
        (new CmsCacheInvalidator())->invalidateLayout();
        return parent::multi($ids);
    }
}

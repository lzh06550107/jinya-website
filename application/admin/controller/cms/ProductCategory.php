<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;
use app\common\model\cms\ProductCategory as CmsModel;

class ProductCategory extends Backend
{
    protected $model = null;
    protected $modelValidate = true;
    protected $modelSceneValidate = false;
    protected $searchFields = 'id,name,slug';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new CmsModel();

        $parents = [0 => '无'];
        foreach ($this->model->where('slug', '<>', 'html-home-display')->order('weigh desc,id asc')->select() as $item) {
            $parents[$item['id']] = $item['name'];
        }
        $this->view->assign('parentList', $parents);
        $this->view->assign('statusList', $this->model->getNormalStatusList());
        $this->assignconfig('statusList', $this->model->getNormalStatusList());

    }
    public function index()
    {
        $this->request->filter(['strip_tags', 'trim']);
        if (!$this->request->isAjax()) {
            return $this->view->fetch();
        }

        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $list = $this->model
            ->where($where)
            ->where('slug', '<>', 'html-home-display')
            ->order($sort, $order)
            ->paginate($limit);
        $rows = $list->items();

        $parentNames = $this->model->column('name', 'id');
        $childCounts = [];
        foreach ($this->model->field('parent_id,COUNT(*) AS total')->group('parent_id')->select() as $stat) {
            $childCounts[(int)$stat['parent_id']] = (int)$stat['total'];
        }
        $contentCounts = [];
        foreach (\app\common\model\cms\Product::field('category_id,COUNT(*) AS total')->group('category_id')->select() as $stat) {
            $contentCounts[(int)$stat['category_id']] = (int)$stat['total'];
        }

        foreach ($rows as &$row) {
            $id = (int)$row['id'];
            $parentId = (int)$row['parent_id'];
            if ($parentId === 0) {
                $row['parent_name'] = '顶级分类';
                $row['parent_missing'] = 0;
            } elseif (isset($parentNames[$parentId])) {
                $row['parent_name'] = $parentNames[$parentId];
                $row['parent_missing'] = 0;
            } else {
                $row['parent_name'] = '上级分类已不存在';
                $row['parent_missing'] = 1;
            }
            $row['content_count'] = isset($contentCounts[$id]) ? (int)$contentCounts[$id] : 0;
            $row['child_count'] = isset($childCounts[$id]) ? (int)$childCounts[$id] : 0;
        }
        unset($row);

        return json(['total' => $list->total(), 'rows' => $rows]);
    }

    public function del($ids = '')
    {
        $ids = $ids ?: $this->request->request('ids');
        $idList = array_values(array_filter(array_map('intval', explode(',', (string)$ids))));
        if (!$idList) {
            $this->error('请选择需要删除的分类');
        }
        if ($this->hasRelatedContent($idList)) {
            $this->error('该分类存在产品或子分类，请先移动内容或停用分类');
        }
        return parent::del($ids);
    }

    protected function hasRelatedContent(array $idList)
    {
        if (CmsModel::where('parent_id', 'in', $idList)->count() > 0) {
            return true;
        }
        return \app\common\model\cms\Product::where('category_id', 'in', $idList)->count() > 0;
    }

}

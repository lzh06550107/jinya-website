<?php

namespace app\admin\controller\cms;

use app\common\model\cms\Article;
use app\common\model\cms\Cases;
use app\common\model\cms\HomeSectionReference as CmsModel;
use app\common\model\cms\Page;
use app\common\model\cms\Product;

class HomeSectionReference extends StructuredChild
{
    protected $modelClass = CmsModel::class;
    protected $parentField = 'section_key';
    protected $markAdminEdit = false;
    protected $usesSourceKey = false;
    protected $searchFields = 'id,section_key,content_type,content_id';

    public function _initialize()
    {
        parent::_initialize();
        $types = ['product' => '产品', 'article' => '新闻', 'case' => '工程案例', 'page' => '单页'];
        $terminals = ['all' => '全部终端', 'pc' => 'PC', 'mobile' => '移动端'];
        $this->view->assign('contentTypeList', $types);
        $this->view->assign('terminalList', $terminals);
        $this->assignconfig('contentTypeList', $types);
        $this->assignconfig('terminalList', $terminals);
    }

    protected function preExcludeFields($params)
    {
        $params = parent::preExcludeFields($params);
        $sectionKey = isset($params['section_key']) ? (string)$params['section_key'] : (string)$this->parentValue;
        if ($sectionKey === 'products') {
            $params['terminal'] = 'all';
        }
        return $params;
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
        $rows = $list->items();

        $idsByType = [];
        foreach ($rows as $row) {
            $type = (string)$row['content_type'];
            $id = (int)$row['content_id'];
            if ($id > 0) {
                if (!isset($idsByType[$type])) {
                    $idsByType[$type] = [];
                }
                $idsByType[$type][$id] = $id;
            }
        }

        $modelMap = [
            'product' => Product::class,
            'article' => Article::class,
            'case' => Cases::class,
            'page' => Page::class,
        ];
        $titles = [];
        foreach ($idsByType as $type => $idMap) {
            if (!isset($modelMap[$type]) || !$idMap) {
                continue;
            }
            $modelClass = $modelMap[$type];
            $titles[$type] = $modelClass::where('id', 'in', array_values($idMap))->column('title', 'id');
        }

        foreach ($rows as &$row) {
            $type = (string)$row['content_type'];
            $id = (int)$row['content_id'];
            if (isset($titles[$type]) && isset($titles[$type][$id])) {
                $row['content_title'] = (string)$titles[$type][$id];
                $row['content_missing'] = 0;
            } else {
                $row['content_title'] = '内容已不存在（ID: ' . $id . '）';
                $row['content_missing'] = 1;
            }
        }
        unset($row);

        return json(['total' => $list->total(), 'rows' => $rows]);
    }
}

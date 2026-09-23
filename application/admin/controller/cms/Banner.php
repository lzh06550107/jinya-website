<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;
use app\common\model\cms\Banner as CmsModel;
use app\common\service\cms\render\CmsCacheInvalidator;
use app\common\service\cms\BannerHighlightCodec;
use app\common\service\cms\BannerOperationalSummary;

class Banner extends Backend
{
    protected $model = null;
    protected $modelValidate = true;
    protected $modelSceneValidate = false;
    protected $searchFields = 'id,title,subtitle';

    public function index()
    {
        $this->request->filter(['strip_tags', 'trim']);
        if (!$this->request->isAjax()) {
            return $this->view->fetch();
        }

        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $list = $this->model->where($where)->order($sort, $order)->paginate($limit);
        $rows = $list->items();
        $summary = new BannerOperationalSummary();
        foreach ($rows as &$row) {
            $source = is_object($row) && method_exists($row, 'toArray') ? $row->toArray() : (array)$row;
            foreach ($summary->summarize($source) as $field => $value) {
                $row[$field] = $value;
            }
        }
        unset($row);

        return json(['total' => $list->total(), 'rows' => $rows]);
    }

    protected function preExcludeFields($params)
    {
        $params = parent::preExcludeFields($params);
        $codec = new BannerHighlightCodec();
        $iconOverrides = [];
        foreach ([1, 2, 3] as $position) {
            $field = 'highlight_icon_' . $position;
            if (!array_key_exists($field, $params)) {
                continue;
            }
            $iconOverrides[$position - 1] = $params[$field];
            unset($params[$field]);
        }

        if (array_key_exists('highlights_json', $params)) {
            $isHomeHero = isset($params['page_key'], $params['position'])
                && (string)$params['page_key'] === 'home'
                && (string)$params['position'] === 'hero';
            $params['highlights_json'] = $iconOverrides
                ? $codec->applyIconOverrides($params['highlights_json'], $iconOverrides, $isHomeHero)
                : $codec->normalizeJson($params['highlights_json']);
        }
        $params['edited_by_admin'] = 1;
        (new CmsCacheInvalidator())->invalidateLayout();
        return $params;
    }

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new CmsModel();

        $this->view->assign('statusList', $this->model->getNormalStatusList());
        $this->assignconfig('statusList', $this->model->getNormalStatusList());

    }
}

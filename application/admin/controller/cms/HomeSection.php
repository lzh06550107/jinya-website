<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;
use app\common\model\cms\HomeSection as CmsModel;
use app\common\model\cms\HomeSectionReference;
use app\common\service\cms\render\CmsCacheInvalidator;
use app\common\service\cms\HomeSectionConfigService;

class HomeSection extends Backend
{
    protected $model = null;
    protected $modelValidate = true;
    protected $modelSceneValidate = false;
    protected $searchFields = 'id,section_key,section_name,title';

    public function index()
    {
        $this->request->filter(['strip_tags', 'trim']);
        if (!$this->request->isAjax()) {
            return $this->view->fetch();
        }

        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $list = $this->model->where($where)->order($sort, $order)->paginate($limit);
        $rows = $list->items();

        $referenceCounts = [];
        $stats = HomeSectionReference::where('status', 'normal')
            ->field('section_key,terminal,COUNT(*) AS total')
            ->group('section_key,terminal')
            ->select();
        foreach ($stats as $stat) {
            $key = (string)$stat['section_key'];
            $terminal = (string)$stat['terminal'];
            $total = (int)$stat['total'];
            if (!isset($referenceCounts[$key])) {
                $referenceCounts[$key] = ['pc' => 0, 'mobile' => 0];
            }
            if ($terminal === 'all' || $terminal === 'pc') {
                $referenceCounts[$key]['pc'] += $total;
            }
            if ($terminal === 'all' || $terminal === 'mobile') {
                $referenceCounts[$key]['mobile'] += $total;
            }
        }

        $referenceKeys = ['products', 'cases', 'news'];
        $configKeys = ['about', 'service', 'workshop', 'advantages', 'company'];
        foreach ($rows as &$row) {
            $key = (string)$row['section_key'];
            if (in_array($key, $referenceKeys, true)) {
                $row['source_type_text'] = '内容引用';
                $row['pc_reference_count'] = isset($referenceCounts[$key]) ? (int)$referenceCounts[$key]['pc'] : 0;
                $row['mobile_reference_count'] = isset($referenceCounts[$key]) ? (int)$referenceCounts[$key]['mobile'] : 0;
            } elseif (in_array($key, $configKeys, true)) {
                $row['source_type_text'] = '模块配置';
                $row['pc_reference_count'] = null;
                $row['mobile_reference_count'] = null;
            } else {
                $row['source_type_text'] = '配置';
                $row['pc_reference_count'] = null;
                $row['mobile_reference_count'] = null;
            }
            if ($row['status'] !== 'normal') {
                $row['front_status_text'] = '已隐藏';
            } else {
                $pcVisible = (int)$row['pc_visible'] === 1;
                $mobileVisible = (int)$row['mobile_visible'] === 1;
                if ($pcVisible && $mobileVisible) {
                    $row['front_status_text'] = '双端显示';
                } elseif ($pcVisible) {
                    $row['front_status_text'] = '仅 PC 显示';
                } elseif ($mobileVisible) {
                    $row['front_status_text'] = '仅移动端显示';
                } else {
                    $row['front_status_text'] = '双端隐藏';
                }
            }
        }
        unset($row);

        return json(['total' => $list->total(), 'rows' => $rows]);
    }

    protected function preExcludeFields($params)
    {
        $params = (array)$params;
        $sectionKey = isset($params['section_key']) ? trim((string)$params['section_key']) : '';
        $identity = [];
        foreach (['section_key', 'section_name'] as $field) {
            if (array_key_exists($field, $params)) {
                $identity[$field] = $params[$field];
            }
        }

        $existingConfigJson = '';
        if ($this->model && $sectionKey !== '') {
            $existing = $this->model->where('section_key', $sectionKey)->find();
            if ($existing) {
                $existingConfigJson = isset($existing['config_json']) ? $existing['config_json'] : '';
            }
        }

        $prepared = (new HomeSectionConfigService())->preparePatch($sectionKey, $params, $existingConfigJson);
        $params = array_merge($identity, $prepared);
        $params = parent::preExcludeFields($params);
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

<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;
use app\common\model\cms\Navigation as CmsModel;
use app\common\service\cms\render\CmsCacheInvalidator;

class Navigation extends Backend
{
    protected $model = null;
    protected $modelValidate = true;
    protected $modelSceneValidate = false;
    protected $searchFields = 'id,title,url';

    protected function preExcludeFields($params)
    {
        $params = parent::preExcludeFields($params);
        (new CmsCacheInvalidator())->invalidateLayout();
        return $params;
    }

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new CmsModel();

        $parents = [0 => '顶级导航'];
        foreach ($this->model->order('weigh desc,id asc')->select() as $item) {
            $parents[$item['id']] = $item['title'];
        }
        $this->view->assign('parentList', $parents);
        $this->view->assign('positionList', ['header' => '顶部导航', 'footer' => '底部导航']);
        $this->view->assign('statusList', $this->model->getNormalStatusList());
        $this->assignconfig('statusList', $this->model->getNormalStatusList());

    }
}

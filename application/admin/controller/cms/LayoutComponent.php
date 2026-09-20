<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;
use app\common\model\cms\LayoutComponent as LayoutComponentModel;
use app\common\service\cms\PageConfigConflictException;
use app\common\service\cms\ThinkPageConfigStore;
use app\common\service\cms\layout_editor\LayoutUnifiedEditorService;

/**
 * 固定公共布局组件管理。
 */
class LayoutComponent extends Backend
{
    protected $model = null;
    protected $searchFields = 'id,component_key,component_name,component_type';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new LayoutComponentModel();
        $this->assignconfig('componentTypeList', $this->componentTypeList());
        $this->assignconfig('deviceList', $this->deviceList());
    }

    public function index()
    {
        $this->request->filter(['strip_tags', 'trim']);
        if (!$this->request->isAjax()) {
            return $this->view->fetch();
        }
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $list = $this->model->where($where)->order($sort, $order)->paginate($limit);
        $rows = $list->items();
        $store = new ThinkPageConfigStore();
        foreach ($rows as &$row) {
            $affected = $store->findPageKeysByLayout($row['component_key']);
            $row['affected_page_count'] = count($affected);
            $row['component_type_text'] = isset($this->componentTypeList()[$row['component_type']])
                ? $this->componentTypeList()[$row['component_type']]
                : $row['component_type'];
            $row['device_text'] = isset($this->deviceList()[$row['device']])
                ? $this->deviceList()[$row['device']]
                : $row['device'];
        }
        unset($row);
        return json(['total' => $list->total(), 'rows' => $rows]);
    }

    public function edit($ids = null)
    {
        $row = LayoutComponentModel::get((int)$ids);
        if (!$row) {
            $this->error('公共布局组件不存在');
        }

        $service = new LayoutUnifiedEditorService();

        if (!$this->request->isPost()) {
            try {
                $editor = $service->load((int)$row['id']);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            $this->view->assign('row', $editor['component']);
            $this->view->assign('editor', $editor);
            $contentSiteFields = $editor['site_fields'];
            if ($editor['component']['component_key'] === 'layout.header.pc') {
                // PC Header 的服务热线在“内容设置 → 联系电话区域”集中编辑，避免与共享站点内容重复出现。
                unset($contentSiteFields['cms_phone']);
            }
            $hasSocialEditor = $editor['definition']['list_type'] === 'social'
                || in_array('layout.footer.qrcode', $editor['definition']['related_components'], true);
            $hasFriendLinksEditor = $editor['definition']['list_type'] === 'friend_links'
                || in_array('layout.friend_links', $editor['definition']['related_components'], true);
            if ($hasSocialEditor) {
                foreach (['cms_wechat_qr','cms_douyin_qr','cms_kuaishou_qr','cms_xiaohongshu_qr','cms_video_qr','cms_bilibili_qr'] as $qrField) {
                    unset($contentSiteFields[$qrField]);
                }
            }
            $hasListTab = $editor['definition']['navigation_position'] !== null || $editor['definition']['list_type'] !== null || $hasSocialEditor || $hasFriendLinksEditor;
            $this->view->assign('siteFields', $editor['site_fields']);
            $this->view->assign('contentSiteFields', $contentSiteFields);
            $this->view->assign('navigationRows', $editor['navigation']);
            $this->view->assign('hotSearchItems', $editor['hot_search_items']);
            $this->view->assign('socialItems', $editor['social_items']);
            $this->view->assign('friendLinkItems', $editor['friend_link_items']);
            $this->view->assign('relatedComponents', $editor['related_components']);
            $this->view->assign('hasSocialEditor', $hasSocialEditor);
            $this->view->assign('hasFriendLinksEditor', $hasFriendLinksEditor);
            $this->view->assign('hasListTab', $hasListTab);
            return $this->view->fetch();
        }

        $this->token();
        $params = $this->request->post('row/a', []);
        if (!$params || !isset($params['version'])) {
            $this->error('公共布局配置参数不完整');
        }

        try {
            $service->save((int)$row['id'], (int)$params['version'], $params, (int)$this->auth->id);
        } catch (PageConfigConflictException $e) {
            $this->error($e->getMessage());
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());
        } catch (\Exception $e) {
            $this->error('公共布局保存失败：' . $e->getMessage());
        }
        $this->success('公共布局内容与显示设置已保存并立即生效');
    }

    public function add()
    {
        $this->error('固定公共布局不能新增');
    }

    public function del($ids = '')
    {
        $this->error('固定公共布局不能删除');
    }

    public function multi($ids = '')
    {
        $this->error('固定公共布局不能批量修改');
    }

    protected function componentTypeList()
    {
        return [
            'header' => '公共页头',
            'footer' => '公共页尾',
            'search' => '公共热搜',
            'floating_service' => '浮动客服',
            'mobile_toolbar' => '移动端底部工具栏',
        ];
    }

    protected function deviceList()
    {
        return ['pc' => 'PC', 'mobile' => '移动端', 'all' => '两端'];
    }
}

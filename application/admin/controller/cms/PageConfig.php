<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;
use app\common\model\cms\LayoutComponent;
use app\common\model\cms\PageBlock;
use app\common\model\cms\PageContentBlock;
use app\common\model\cms\Page as PageModel;
use app\common\model\cms\PageConfig as PageConfigModel;
use app\common\service\cms\PageConfigConflictException;
use app\common\service\cms\PageConfigService;
use app\common\service\cms\PageContentSourceRegistry;
use app\common\service\cms\PageSchemaRegistry;

/**
 * 固定页面管理。
 *
 * 页面由注册表预定义，只允许编辑布局绑定，禁止新增、删除或批量改状态。
 */
class PageConfig extends Backend
{
    protected $model = null;
    protected $searchFields = 'id,page_key,page_name,route_pattern';
    protected $noNeedRight = ['blocks'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new PageConfigModel();
        $pages = PageSchemaRegistry::pages();
        $pageTypeList = [];
        foreach ($pages as $pageKey => $schema) {
            $pageTypeList[$schema['type']] = $this->pageTypeTitle($schema['type']);
        }
        $this->assignconfig('pageTypeList', $pageTypeList);
        $this->view->assign('pageTypeList', $pageTypeList);
    }

    public function index()
    {
        $this->request->filter(['strip_tags', 'trim']);
        if (!$this->request->isAjax()) {
            return $this->view->fetch();
        }
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $registeredPageKeys = array_keys(PageSchemaRegistry::pages());
        $list = $this->model
            ->where($where)
            ->where('page_key', 'in', $registeredPageKeys)
            ->order($sort, $order)
            ->paginate($limit);
        $rows = $list->items();
        $blockCounts = PageBlock::where('page_key', 'in', array_column($rows, 'page_key'))
            ->whereNull('deletetime')
            ->group('page_key')
            ->column('COUNT(*)', 'page_key');
        $pageBySlug = [];
        $fixedSlugs = array_values(PageContentSourceRegistry::fixedSlugs());
        if ($fixedSlugs) {
            $contentRows = PageModel::where('slug', 'in', $fixedSlugs)->select();
            foreach ($contentRows as $contentRow) {
                $pageBySlug[(string)$contentRow['slug']] = $contentRow;
            }
        }
        $pageCount = $fixedSlugs ? (int)PageModel::where('slug', 'not in', $fixedSlugs)->count() : (int)PageModel::count();
        $registeredPages = PageSchemaRegistry::pages();
        foreach ($rows as &$row) {
            if (isset($registeredPages[$row['page_key']])) {
                $row['page_name'] = $registeredPages[$row['page_key']]['name'];
            }
            $row['block_count'] = isset($blockCounts[$row['page_key']]) ? (int)$blockCounts[$row['page_key']] : 0;
            $row['page_type_text'] = $this->pageTypeTitle($row['page_type']);
            foreach ($this->contentMeta($row['page_key'], $pageBySlug, $pageCount) as $key => $value) {
                $row[$key] = $value;
            }
            $blockMeta = $this->blockMeta($row['page_key'], $pageBySlug);
            if ($blockMeta['block_count'] !== null) {
                $row['block_count'] = (int)$blockMeta['block_count'];
            }
            $row['blocks_url'] = $blockMeta['blocks_url'];
            $row['blocks_title'] = $blockMeta['blocks_title'];
        }
        unset($row);
        return json(['total' => $list->total(), 'rows' => $rows]);
    }

    public function edit($ids = null)
    {
        $row = PageConfigModel::get((int)$ids);
        if (!$row || !isset(PageSchemaRegistry::pages()[(string)$row['page_key']])) {
            $this->error('固定页面不存在');
        }
        if (!$this->request->isPost()) {
            $this->assignLayoutLists();
            $this->view->assign('row', $row);
            $schema = PageSchemaRegistry::page($row['page_key']);
            $pageConfig = PageSchemaRegistry::sanitizePageConfig($row['page_key'], $row['config_array']);
            $this->view->assign('schema', $schema);
            $this->view->assign('pageConfig', $pageConfig);
            $this->view->assign('pageContent', $this->contentMeta($row['page_key']));
            $this->view->assign('pageBlocks', $this->blockMeta($row['page_key']));
            return $this->view->fetch();
        }

        $this->token();
        $params = $this->request->post('row/a', [], 'trim');
        if (!$params || !isset($params['version'])) {
            $this->error('页面配置参数不完整');
        }
        try {
            (new PageConfigService())->savePage((int)$row['id'], (int)$params['version'], [
                'pc_header_key' => isset($params['pc_header_key']) ? $params['pc_header_key'] : $row['pc_header_key'],
                'pc_footer_key' => isset($params['pc_footer_key']) ? $params['pc_footer_key'] : $row['pc_footer_key'],
                'mobile_header_key' => isset($params['mobile_header_key']) ? $params['mobile_header_key'] : $row['mobile_header_key'],
                'mobile_footer_key' => isset($params['mobile_footer_key']) ? $params['mobile_footer_key'] : $row['mobile_footer_key'],
                'config' => isset($params['config']) && is_array($params['config']) ? $params['config'] : [],
            ], (int)$this->auth->id);
        } catch (PageConfigConflictException $e) {
            $this->error($e->getMessage());
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('页面配置已保存并立即生效');
    }

    public function blocks($ids = null)
    {
        $row = PageConfigModel::get((int)$ids);
        if (!$row) {
            $this->error('固定页面不存在');
        }
        $meta = $this->blockMeta($row['page_key']);
        $this->redirect($meta['blocks_url']);
    }

    public function add()
    {
        $this->error('固定页面不能新增');
    }

    public function del($ids = '')
    {
        $this->error('固定页面不能删除');
    }

    public function multi($ids = '')
    {
        $this->error('固定页面不能批量修改');
    }

    protected function assignLayoutLists()
    {
        $rows = LayoutComponent::where('status', 'normal')->order('component_type asc,device asc,id asc')->select();
        $lists = [
            'pcHeaderList' => [],
            'pcFooterList' => [],
            'mobileHeaderList' => [],
            'mobileFooterList' => [],
        ];
        foreach ($rows as $layout) {
            $key = $layout['component_key'];
            $name = $layout['component_name'];
            if ($layout['component_type'] === 'header' && in_array($layout['device'], ['pc', 'all'], true)) {
                $lists['pcHeaderList'][$key] = $name;
            }
            if ($layout['component_type'] === 'footer' && in_array($layout['device'], ['pc', 'all'], true)) {
                $lists['pcFooterList'][$key] = $name;
            }
            if ($layout['component_type'] === 'header' && in_array($layout['device'], ['mobile', 'all'], true)) {
                $lists['mobileHeaderList'][$key] = $name;
            }
            if ($layout['component_type'] === 'footer' && in_array($layout['device'], ['mobile', 'all'], true)) {
                $lists['mobileFooterList'][$key] = $name;
            }
        }
        foreach ($lists as $name => $list) {
            $this->view->assign($name, $list);
        }
    }



    protected function blockMeta($pageKey, array $pageBySlug = null)
    {
        $source = PageContentSourceRegistry::resolve($pageKey);
        $blockMode = isset($source['block_mode']) ? (string)$source['block_mode'] : 'page_schema';
        if ($blockMode !== 'page_content') {
            return [
                'block_count' => null,
                'blocks_url' => url('cms/page_block/index', ['page_key' => $pageKey]),
                'blocks_title' => '配置功能块',
            ];
        }

        $slug = isset($source['slug']) ? (string)$source['slug'] : '';
        $contentRow = null;
        if ($pageBySlug !== null && isset($pageBySlug[$slug])) {
            $contentRow = $pageBySlug[$slug];
        } elseif ($slug !== '') {
            $contentRow = PageModel::where('slug', $slug)->find();
        }
        if (!$contentRow) {
            return [
                'block_count' => 0,
                'blocks_url' => url('cms/page/index', ['from' => 'page_config']),
                'blocks_title' => '页面区块（页面记录未初始化）',
            ];
        }

        return [
            'block_count' => (int)PageContentBlock::where('page_id', (int)$contentRow['id'])->whereNull('deletetime')->count(),
            'blocks_url' => url('cms/page_content_block/index', ['page_id' => (int)$contentRow['id']]),
            'blocks_title' => '配置页面区块',
        ];
    }

    protected function contentMeta($pageKey, array $pageBySlug = null, $pageCount = null)
    {
        $source = PageContentSourceRegistry::resolve($pageKey);
        $mode = isset($source['mode']) ? $source['mode'] : 'none';
        $meta = [
            'content_mode' => $mode,
            'content_count' => 0,
            'content_url' => '',
            'content_title' => isset($source['title']) ? $source['title'] : '',
        ];
        if ($mode === 'collection') {
            $meta['content_count'] = $pageCount === null ? (int)PageModel::count() : (int)$pageCount;
            $meta['content_url'] = url('cms/page/index', ['from' => 'page_config']);
            return $meta;
        }
        if ($mode !== 'single') {
            return $meta;
        }

        $slug = isset($source['slug']) ? (string)$source['slug'] : '';
        $contentRow = null;
        if ($pageBySlug !== null && isset($pageBySlug[$slug])) {
            $contentRow = $pageBySlug[$slug];
        } elseif ($slug !== '') {
            $contentRow = PageModel::where('slug', $slug)->find();
        }
        if ($contentRow) {
            $meta['content_count'] = 1;
            $meta['content_url'] = url('cms/page/edit', ['ids' => (int)$contentRow['id'], 'from' => 'page_config']);
        } else {
            $meta['content_url'] = url('cms/page/index', ['from' => 'page_config']);
            $meta['content_title'] = '页面内容（记录未初始化）';
        }
        return $meta;
    }

    protected function pageTypeTitle($type)
    {
        $titles = [
            'fixed' => '固定页面',
            'list' => '列表页面',
            'dynamic_list' => '动态列表',
            'dynamic_detail' => '动态详情',
            'fixed_page' => '固定单页',
            'system' => '系统页面',
        ];
        return isset($titles[$type]) ? $titles[$type] : $type;
    }
}

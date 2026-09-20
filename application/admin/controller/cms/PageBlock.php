<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;
use app\common\model\cms\PageBlock as PageBlockModel;
use app\common\model\cms\PageBlockReference;
use app\common\model\cms\PageConfig as PageConfigModel;
use app\common\model\cms\Product as ProductModel;
use app\common\model\cms\Cases as CasesModel;
use app\common\service\cms\PageBlockRealSourceEditorRegistry;
use app\common\service\cms\PageBlockRealSourceService;
use app\common\service\cms\PageBlockRealSourceListService;
use app\common\service\cms\HomeSectionEditorSchema;
use app\common\service\cms\HomePageBlockOrderService;
use app\common\service\cms\PageConfigConflictException;
use app\common\service\cms\PageConfigService;
use app\common\service\cms\PageSchemaRegistry;

/**
 * 固定页面功能块管理。
 */
class PageBlock extends Backend
{
    protected $model = null;
    protected $searchFields = 'id,page_key,block_key,block_name';
    protected $noNeedRight = ['referenceoptions'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new PageBlockModel();
    }

    public function index()
    {
        $pageKey = trim((string)$this->request->get('page_key', ''));
        if ($pageKey === '') {
            $pageKey = trim((string)$this->request->request('page_key', 'home'));
        }
        try {
            $pageSchema = PageSchemaRegistry::page($pageKey);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());
        }
        $page = PageConfigModel::where('page_key', $pageKey)->find();
        if (!$page) {
            $this->error('页面配置不存在，请先执行 cms:install');
        }
        $this->assignconfig('pageKey', $pageKey);
        $this->view->assign('page', $page);
        $this->view->assign('pageSchema', $pageSchema);

        if (!$this->request->isAjax()) {
            return $this->view->fetch();
        }
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $query = $this->model
            ->where('page_key', $pageKey)
            ->where($where);
        if ($pageKey === 'home' && $sort === 'weigh') {
            $query->order('weigh ' . ($order === 'asc' ? 'asc' : 'desc') . ',id asc');
        } else {
            $query->order($sort, $order);
        }
        $list = $query->paginate($limit);
        $rows = $list->items();
        $rows = (new PageBlockRealSourceListService())->decorate($pageKey, $rows);
        foreach ($rows as &$row) {
            $schema = PageSchemaRegistry::block($pageKey, $row['block_key']);
            $row['core_text'] = $schema['core'] ? '核心' : '可选';
            $row['source_type_text'] = $schema['source_type'];
            $row['pc_visible_text'] = (int)$row['pc_visible'] === 1 ? '显示' : '隐藏';
            $row['mobile_visible_text'] = (int)$row['mobile_visible'] === 1 ? '显示' : '隐藏';
            $row['status_text'] = $row['status'] === 'normal' ? '启用' : '停用';
        }
        unset($row);
        return json(['total' => $list->total(), 'rows' => $rows]);
    }


    /**
     * Read-only SelectPage source for homepage product/case references.
     * Product/case CRUD controllers are retired; this endpoint only exposes
     * existing records so homepage references remain editable.
     */
    public function referenceoptions()
    {
        $type = trim((string)$this->request->request('content_type', ''));
        if ($type === 'product') {
            $this->model = new ProductModel();
        } elseif ($type === 'case') {
            $this->model = new CasesModel();
        } else {
            $this->error('不支持的首页引用类型');
        }
        $this->selectpageFields = 'id,title,weigh,status';
        return $this->selectpage();
    }

    public function reorder()
    {
        if (!$this->request->isPost()) {
            $this->error('排序请求方式无效');
        }
        $ids = $this->request->post('ids', '');
        $ids = is_array($ids) ? $ids : explode(',', (string)$ids);
        try {
            (new HomePageBlockOrderService())->reorder($ids);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
        $this->success('首页功能块顺序已更新');
    }

    public function resetorder()
    {
        if (!$this->request->isPost()) {
            $this->error('恢复默认顺序请求方式无效');
        }
        if (trim((string)$this->request->post('page_key', 'home')) !== 'home') {
            $this->error('仅首页功能块支持恢复默认顺序');
        }
        try {
            (new HomePageBlockOrderService())->reset();
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
        $this->success('已恢复为 ID 升序');
    }

    public function edit($ids = null)
    {
        $row = PageBlockModel::get((int)$ids);
        if (!$row) {
            $this->error('固定功能块不存在');
        }
        $schema = PageSchemaRegistry::block($row['page_key'], $row['block_key']);
        $realSource = (new PageBlockRealSourceEditorRegistry())->resolve($row['page_key'], $row['block_key'], $row['block_type']);
        $mode = isset($realSource['mode']) ? $realSource['mode'] : 'generic';
        $this->view->assign('realSourceMode', $mode);

        if (!$this->request->isPost()) {
            if ($mode === 'banner_collection') {
                $this->assignBannerEditData($row, $schema, $realSource);
            } elseif ($mode === 'home_section') {
                $this->assignHomeSectionEditData($row, $schema, $realSource);
            } else {
                $this->assignEditData($row, $schema);
            }
            return $this->view->fetch();
        }

        $this->token();
        if ($mode === 'banner_collection') {
            $real = $this->request->post('real/a', []);
            $banners = isset($real['banners']) && is_array($real['banners']) ? $real['banners'] : [];
            try {
                (new PageBlockRealSourceService())->saveBannerCollection(
                    $realSource['page_key'],
                    $realSource['position'],
                    $banners,
                    (int)$this->auth->id
                );
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success('Banner 资源已保存并立即生效');
        }

        if ($mode === 'home_section') {
            $real = $this->request->post('real/a', []);
            $section = isset($real['section']) && is_array($real['section']) ? $real['section'] : [];
            $references = isset($real['references']) && is_array($real['references']) ? $real['references'] : [];
            try {
                (new PageBlockRealSourceService())->saveHomeSection(
                    $realSource['section_key'],
                    $section,
                    $references,
                    (int)$this->auth->id,
                    isset($realSource['content_type']) ? $realSource['content_type'] : ''
                );
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success('首页模块内容已保存并立即生效');
        }

        $params = $this->request->post('row/a', []);
        if (!$params || !isset($params['version'])) {
            $this->error('功能块配置参数不完整');
        }
        $config = isset($params['config']) && is_array($params['config']) ? $params['config'] : [];
        $references = $this->buildReferences($schema, $config, isset($params['reference_ids']) ? $params['reference_ids'] : '');
        try {
            (new PageConfigService())->saveBlock((int)$row['id'], (int)$params['version'], [
                'config' => $config,
            ], $references, (int)$this->auth->id);
        } catch (PageConfigConflictException $e) {
            $this->error($e->getMessage());
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('功能块配置已保存并立即生效');
    }

    public function add()
    {
        $this->error('固定功能块不能新增');
    }

    public function del($ids = '')
    {
        $this->error('固定功能块不能删除');
    }

    public function multi($ids = '')
    {
        $this->error('固定功能块不能批量修改');
    }

    protected function assignBannerEditData($row, array $schema, array $realSource)
    {
        $service = new PageBlockRealSourceService();
        $rows = $service->loadBannerCollection($realSource['page_key'], $realSource['position']);
        if (!$rows) {
            $rows = [$this->emptyBannerRow()];
        }
        $this->view->assign('row', $row);
        $this->view->assign('schema', $schema);
        $this->view->assign('bannerRows', $rows);
        $this->view->assign('bannerPageKey', $realSource['page_key']);
        $this->view->assign('bannerPosition', $realSource['position']);
    }

    protected function assignHomeSectionEditData($row, array $schema, array $realSource)
    {
        $homeEditorSchema = new HomeSectionEditorSchema();
        $sectionKey = isset($realSource['section_key']) ? trim((string)$realSource['section_key']) : '';
        $contentType = $homeEditorSchema->referenceType($sectionKey);
        $homeHasReferences = $homeEditorSchema->hasReferences($sectionKey);
        $home = (new PageBlockRealSourceService())->loadHomeSection($sectionKey, $contentType);
        $this->view->assign('row', $row);
        $this->view->assign('schema', $schema);
        $this->view->assign('homeSection', $home);
        $this->view->assign('homeContentType', $contentType);
        $this->view->assign('homeHasReferences', $homeHasReferences);
        $this->view->assign('homePcReferenceIds', implode(',', isset($home['pc_reference_ids']) ? $home['pc_reference_ids'] : []));
        $this->view->assign('homeMobileReferenceIds', implode(',', isset($home['mobile_reference_ids']) ? $home['mobile_reference_ids'] : []));
        $this->view->assign('homeReferenceSource', $this->referenceSource($contentType));
    }

    protected function emptyBannerRow()
    {
        return [
            'id' => 0,
            'title' => '', 'mobile_title' => '', 'subtitle' => '', 'mobile_subtitle' => '',
            'description' => '', 'mobile_description' => '',
            'image' => '', 'mobile_image' => '',
            'media_type' => 'image', 'mobile_media_type' => '',
            'video_url' => '', 'mobile_video_url' => '',
            'overlay_image' => '',
            'link_url' => '', 'mobile_link_url' => '', 'button_text' => '',
            'start_time' => null, 'end_time' => null,
            'pc_visible' => 1, 'mobile_visible' => 1, 'weigh' => 0, 'status' => 'normal',
        ];
    }

    protected function assignEditData($row, array $schema)
    {
        $config = $row['config_array'];
        $config['pc_visible'] = (int)$row['pc_visible'];
        $config['mobile_visible'] = (int)$row['mobile_visible'];
        $config['enabled'] = $row['status'] === 'normal' ? 1 : 0;
        $fields = [];
        $deviceImageFields = [];
        foreach ($schema['fields'] as $name => $definition) {
            $field = $definition;
            $field['name'] = $name;
            $field['input_id'] = 'c-config-' . str_replace('_', '-', $name);
            $field['required'] = !empty($definition['required']);
            $field['value'] = array_key_exists($name, $config)
                ? $config[$name]
                : (array_key_exists('default', $definition) ? $definition['default'] : '');
            if ($definition['type'] === 'enum') {
                $field['option_list'] = [];
                foreach ($definition['options'] as $option) {
                    $field['option_list'][$option] = $this->optionTitle($name, $option);
                }
            }
            if ($definition['type'] === 'device_image') {
                $field['pc_value'] = isset($config['pc_' . $name]) ? $config['pc_' . $name] : '';
                $field['mobile_value'] = isset($config['mobile_' . $name]) ? $config['mobile_' . $name] : '';
                $deviceImageFields[] = $name;
            }
            $fields[] = $field;
        }

        $references = PageBlockReference::where('page_block_id', (int)$row['id'])
            ->where('status', 'normal')
            ->order('weigh desc,id asc')
            ->column('content_id');
        $contentType = $this->schemaContentType($schema, $config);
        $this->view->assign('row', $row);
        $this->view->assign('schema', $schema);
        $this->view->assign('formFields', $fields);
        $this->view->assign('schemaFieldsJson', json_encode(array_keys($schema['fields']), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->view->assign('deviceImageFields', $deviceImageFields);
        $this->view->assign('supportsSourceMode', isset($schema['fields']['source_mode']));
        $this->view->assign('referenceIds', implode(',', array_map('intval', $references ?: [])));
        $this->view->assign('referenceSource', $this->referenceSource($contentType));
        $this->view->assign('referenceContentType', $contentType);
    }

    protected function buildReferences(array $schema, array $config, $referenceIds)
    {
        if (!isset($schema['fields']['source_mode'])) {
            return [];
        }
        $contentType = $this->schemaContentType($schema, $config);
        $ids = is_array($referenceIds) ? $referenceIds : explode(',', (string)$referenceIds);
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        $references = [];
        $weight = count($ids);
        foreach ($ids as $id) {
            $references[] = [
                'content_type' => $contentType,
                'content_id' => $id,
                'weigh' => $weight--,
            ];
        }
        return $references;
    }

    protected function schemaContentType(array $schema, array $config)
    {
        if (!empty($config['content_type'])) {
            return (string)$config['content_type'];
        }
        if (isset($schema['fields']['content_type']['options'][0])) {
            return (string)$schema['fields']['content_type']['options'][0];
        }
        return in_array($schema['source_type'], ['product', 'article', 'case', 'page'], true)
            ? $schema['source_type']
            : '';
    }

    protected function referenceSource($contentType)
    {
        $sources = [
            'product' => 'cms/page_block/referenceoptions?content_type=product',
            'article' => 'cms/article/index',
            'case' => 'cms/page_block/referenceoptions?content_type=case',
            'page' => 'cms/page/index',
        ];
        return isset($sources[$contentType]) ? $sources[$contentType] : '';
    }

    protected function optionTitle($field, $value)
    {
        $titles = [
            'manual' => '手动选择',
            'auto' => '自动筛选',
            'weigh_desc' => '权重从高到低',
            'publish_time_desc' => '发布时间从新到旧',
            'product' => '产品',
            'article' => '新闻',
            'case' => '工程案例',
            'page' => '单页',
        ];
        return isset($titles[$value]) ? $titles[$value] : $value;
    }
}

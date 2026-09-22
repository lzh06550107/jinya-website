<?php

namespace app\admin\controller\cms;

use app\common\model\cms\PageContentBlock as CmsModel;
use app\common\service\cms\StructuredConfigCodec;
use app\common\service\cms\LabelPageBlockConfigCodec;
use app\common\service\cms\BagsPageBlockConfigCodec;
use app\common\service\cms\BoxesPageBlockConfigCodec;
use app\common\service\cms\AboutPageBlockConfigCodec;
use app\common\service\cms\ContactPageBlockConfigCodec;
use app\common\service\cms\PageContentBlockEditorSchema;
use app\common\service\cms\HtmlSanitizer;

class PageContentBlock extends StructuredChild
{
    protected $modelClass = CmsModel::class;
    protected $parentField = 'page_id';
    protected $searchFields = 'id,block_key,block_type,title';

    protected function preExcludeFields($params)
    {
        $params = (array)$params;
        if (isset($params['extra_mobile_content'])) {
            $params['extra_mobile_content'] = HtmlSanitizer::clean($params['extra_mobile_content']);
        }
        $blockType = isset($params['block_type']) ? (string)$params['block_type'] : '';
        $blockKey = isset($params['block_key']) ? (string)$params['block_key'] : '';
        if ($blockType === 'label_section' || strpos($blockKey, 'label_') === 0) {
            $params = (new LabelPageBlockConfigCodec())->extract($params);
        } elseif ($blockType === 'bags_section' || strpos($blockKey, 'bags_') === 0) {
            $params = (new BagsPageBlockConfigCodec())->extract($params);
        } elseif ($blockType === 'boxes_section' || strpos($blockKey, 'boxes_') === 0) {
            $params = (new BoxesPageBlockConfigCodec())->extract($params);
        } elseif ($blockType === 'about_section' || strpos($blockKey, 'about_') === 0) {
            $params = (new AboutPageBlockConfigCodec())->extract($params);
        } elseif ($blockType === 'contact_section' || strpos($blockKey, 'contact_') === 0) {
            $params = (new ContactPageBlockConfigCodec())->extract($params);
        } else {
            $params = (new StructuredConfigCodec())->extractPageBlock($params);
        }
        return parent::preExcludeFields($params);
    }

    public function _initialize()
    {
        parent::_initialize();
        $types = [
            'text' => '文本',
            'image_text' => '图文',
            'contact_info' => '联系方式',
            'timeline' => '发展历程',
            'gallery' => '相册',
            'honor_list' => '荣誉列表',
            'patent_list' => '专利列表',
            'video_list' => '视频列表',
            'map' => '地图',
            'form_intro' => '咨询说明',
            'label_section' => '不干胶/卷标专用区块',
            'bags_section' => '包装袋·无版印刷专用区块',
            'boxes_section' => '彩盒专用区块',
            'about_section' => '走近金亚专用区块',
            'contact_section' => '联系我们专用区块',
        ];
        $this->view->assign('blockTypeList', $types);
        $this->assignconfig('blockTypeList', $types);
        $this->assignconfig('pageContentBlockEditorSchemas', PageContentBlockEditorSchema::all());

        // Use a new, immutable RequireJS entry name for the rich-text editor.
        // This avoids stale browser/CDN/RequireJS caches of page_content_block.js.
        $this->assignconfig('jsname', 'backend/cms/page_content_block_rich_v8');
        $this->assignconfig('cmsPageContentBlockEditorBuild', 'rich-v8');
    }
}

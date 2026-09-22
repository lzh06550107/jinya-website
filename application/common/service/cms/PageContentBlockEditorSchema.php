<?php

namespace app\common\service\cms;

/**
 * Backend editor contract for the five structured one-page sites.
 *
 * This schema intentionally describes only fields consumed by the active
 * PC/Mobile templates. The underlying row/extra_json format is unchanged;
 * hidden legacy inputs remain in the form so historical data is preserved.
 */
class PageContentBlockEditorSchema
{
    private static $schemas;

    public static function all()
    {
        if (self::$schemas === null) {
            self::$schemas = self::build();
        }
        return self::$schemas;
    }

    public static function forBlock($blockKey)
    {
        $all = self::all();
        $key = trim((string)$blockKey);
        return isset($all[$key]) ? $all[$key] : null;
    }

    private static function build()
    {
        $schemas = [];

        // 不干胶 / 卷标（11）
        $schemas['label_hero'] = self::make('label', '不干胶/卷标', 'Banner', 'label_section',
            ['title','subtitle','content','image','mobile_image'], [],
            ['title','text','badge','mobile_title','mobile_text','mobile_badge','pc_visible','mobile_visible'], [],
            ['title'=>'Banner 标题','subtitle'=>'标题右侧短句','content'=>'Banner 说明','image'=>'PC 背景图','mobile_image'=>'移动背景图'],
            ['title'=>'卖点标题','text'=>'卖点说明','badge'=>'图标类型','mobile_title'=>'移动卖点标题','mobile_text'=>'移动卖点说明','mobile_badge'=>'移动图标类型']
        );
        $schemas['label_quote'] = self::make('label', '不干胶/卷标', '为什么选择金亚', 'label_section',
            ['title'], ['label_consult_text','label_consult_url','label_phone','label_video_url'],
            ['title','text','badge','mobile_title','mobile_text','mobile_badge','pc_visible','mobile_visible'], [],
            [], ['title'=>'优势标题','text'=>'优势说明','badge'=>'右侧强调标签','mobile_title'=>'移动优势标题','mobile_text'=>'移动优势说明','mobile_badge'=>'移动强调标签'],
            ['label_consult_text'=>'咨询按钮文字','label_consult_url'=>'咨询链接','label_phone'=>'服务热线','label_video_url'=>'左侧视频']
        );
        $schemas['label_samples'] = self::make('label', '不干胶/卷标', '产品展示', 'label_section',
            ['title'], [], ['title','image','mobile_title','mobile_image','pc_visible','mobile_visible'], [],
            [], ['title'=>'产品名称','image'=>'产品图片','mobile_title'=>'移动产品名称','mobile_image'=>'移动产品图片']
        );
        $schemas['label_capability'] = self::make('label', '不干胶/卷标', '能力图文', 'label_section',
            [], [], ['title','text','group','image_top','image_bottom','image','mobile_title','mobile_text','mobile_image_top','mobile_image_bottom','mobile_image','pc_visible','mobile_visible'],
            ['normal'=>'图右文左','reverse'=>'图左文右'],
            [], [
                'title'=>'标题','text'=>'说明','group'=>'版式方向',
                'image_top'=>'PC 左上图片','image_bottom'=>'PC 左下图片','image'=>'PC 右侧大图',
                'mobile_title'=>'移动标题','mobile_text'=>'移动说明',
                'mobile_image_top'=>'移动左上图片','mobile_image_bottom'=>'移动左下图片','mobile_image'=>'移动右侧大图'
            ]
        );
        $schemas['label_tech'] = self::make('label', '不干胶/卷标', '耐高低温视觉图', 'label_section',
            ['title','image','mobile_image'], [], [], [],
            ['title'=>'图片替代文字','image'=>'PC 整图','mobile_image'=>'移动整图']
        );
        $schemas['label_compare'] = self::make('label', '不干胶/卷标', '胶水材质对比', 'label_section',
            ['title','subtitle','image','mobile_image'], ['label_secondary_title'],
            ['title','text','subtitle','badge','value','group','mobile_title','mobile_text','mobile_subtitle','mobile_badge','pc_visible','mobile_visible'],
            ['compare'=>'胶水对比行','parameter'=>'产品参数行'],
            ['image'=>'PC 胶水结构对比图','mobile_image'=>'移动胶水结构对比图'], ['title'=>'项目/产品名称','text'=>'第二列','subtitle'=>'第三列','badge'=>'第四列','value'=>'第五列/服务咨询','group'=>'表格分组','mobile_title'=>'移动名称','mobile_text'=>'移动第二列','mobile_subtitle'=>'移动第三列','mobile_badge'=>'移动第四列'],
            ['label_secondary_title'=>'参数说明标题']
        );
        $schemas['label_materials'] = self::make('label', '不干胶/卷标', '常用工艺与不干胶印刷', 'label_section',
            ['title'], ['label_print_title','label_print_points'],
            ['title','text','image','group','mobile_title','mobile_text','mobile_image','pc_visible','mobile_visible'],
            ['craft-image'=>'工艺图片','craft-note'=>'工艺说明','print-image'=>'不干胶印刷轮播图'],
            [], ['title'=>'工艺/图片名称','text'=>'工艺说明','image'=>'图片','group'=>'项目类型','mobile_title'=>'移动名称','mobile_text'=>'移动说明','mobile_image'=>'移动图片'],
            ['label_print_title'=>'不干胶印刷标题','label_print_points'=>'不干胶印刷要点（Markdown/换行）']
        );
        $schemas['label_materials']['section_order'] = ['items','specific'];
        $schemas['label_materials']['specific_section_label'] = '不干胶印刷配置';
        $schemas['label_elements'] = self::make('label', '不干胶/卷标', '卷标三要素', 'label_section',
            ['title'], [],
            ['title','text','image','group','mobile_title','mobile_text','mobile_image','pc_visible','mobile_visible'],
            ['intro-direction'=>'出标方向','intro-core'=>'卷芯规格','intro-diameter'=>'卷芯外直径测量'],
            [], ['title'=>'说明标题','text'=>'说明文字','image'=>'PC 示意图','group'=>'说明类型','mobile_title'=>'移动标题','mobile_text'=>'移动说明','mobile_image'=>'移动示意图']
        );
        $schemas['label_service'] = self::make('label', '不干胶/卷标', '定制流程与保障', 'label_section',
            ['title'], ['label_secondary_title'],
            ['title','text','badge','group','mobile_title','mobile_text','mobile_badge','pc_visible','mobile_visible'],
            ['process'=>'定制流程','guarantee'=>'保障项目'],
            [], ['title'=>'项目标题','text'=>'保障说明','badge'=>'图标类型','group'=>'项目类型','mobile_title'=>'移动标题','mobile_text'=>'移动说明','mobile_badge'=>'移动图标类型'],
            ['label_secondary_title'=>'第二组标题']
        );
        $schemas['label_advantages'] = self::make('label', '不干胶/卷标', '生产与服务优势', 'label_section',
            ['title'], [],
            ['title','text','image','badge','group','mobile_title','mobile_text','mobile_image','mobile_badge','pc_visible','mobile_visible'],
            ['photo'=>'生产场景图片','adv'=>'优势卡片'],
            [], ['title'=>'标题','text'=>'优势说明','image'=>'场景图片','badge'=>'图标类型','group'=>'项目类型','mobile_title'=>'移动标题','mobile_text'=>'移动说明','mobile_image'=>'移动图片','mobile_badge'=>'移动图标类型']
        );
        $schemas['label_cta'] = self::make('label', '不干胶/卷标', '一站式定制服务图片', 'label_section',
            ['image','mobile_image'], [], [], [], ['image'=>'PC 整图','mobile_image'=>'移动整图']
        );

        // 包装袋无版印刷（8）
        $schemas['bags_hero'] = self::make('bags', '包装袋无版印刷', 'Banner', 'bags_section',
            ['title','content','image','mobile_image'], [],
            ['title','badge','mobile_title','mobile_badge','pc_visible','mobile_visible'], [],
            ['title'=>'Banner 标题','content'=>'Banner 说明','image'=>'PC 背景图','mobile_image'=>'移动背景图'],
            ['title'=>'卖点文字','badge'=>'图标类型','mobile_title'=>'移动卖点文字','mobile_badge'=>'移动图标类型']
        );
        $schemas['bags_products'] = self::make('bags', '包装袋无版印刷', '产品中心', 'bags_section',
            ['title','subtitle'], [],
            ['title','image','group','mobile_title','mobile_image','pc_visible','mobile_visible'],
            ['main'=>'主展示图','small'=>'普通展示图'], [],
            ['title'=>'产品名称','image'=>'产品图片','group'=>'展示位置','mobile_title'=>'移动产品名称','mobile_image'=>'移动产品图片']
        );
        $schemas['bags_compare'] = self::make('bags', '包装袋无版印刷', '专版和无版印刷怎么选', 'bags_section',
            ['title','subtitle','content','image','mobile_image'], ['bags_compare_brand','bags_compare_footer'],
            ['title','text','badge','group','mobile_title','mobile_text','mobile_badge','pc_visible','mobile_visible'],
            ['plate'=>'专版印刷','digital'=>'无版印刷'],
            ['title'=>'主标题','subtitle'=>'副标题','content'=>'选择说明','image'=>'PC 背景图','mobile_image'=>'移动背景图'],
            ['title'=>'方案标题','text'=>'方案要点','badge'=>'图标类型','group'=>'方案类型','mobile_title'=>'移动方案标题','mobile_text'=>'移动方案要点','mobile_badge'=>'移动图标类型'],
            ['bags_compare_brand'=>'品牌小标题','bags_compare_footer'=>'底部提示']
        );
        $schemas['bags_cases'] = self::make('bags', '包装袋无版印刷', '应用案例', 'bags_section',
            ['title','subtitle','image','mobile_image','link'], ['bags_tabs'],
            ['title','text','image','url','subtitle','group','mobile_title','mobile_text','mobile_image','mobile_subtitle','mobile_url','pc_visible','mobile_visible'], [],
            ['image'=>'PC 案例区背景图','mobile_image'=>'移动案例区背景图','link'=>'查看更多产品'], ['title'=>'案例标题','text'=>'案例说明','image'=>'案例图片','url'=>'MORE+ 链接','subtitle'=>'底部文字','group'=>'所属 Tabs','mobile_title'=>'移动标题','mobile_text'=>'移动说明','mobile_image'=>'移动图片','mobile_subtitle'=>'移动底部文字','mobile_url'=>'移动链接'],
            ['bags_tabs'=>'筛选 Tabs']
        );
        $schemas['bags_cases']['dynamic_group_source'] = 'bags_tabs';
        $schemas['bags_cases']['dynamic_group_multiple'] = true;
        $schemas['bags_cases']['item_help']['group'] = '可多选当前“筛选 Tabs”；同一个案例可同时出现在多个分类中。';
        unset($schemas['bags_cases']['scalar_help']['bags_tabs']);
        $schemas['bags_samples'] = self::make('bags', '包装袋无版印刷', '样品展示', 'bags_section',
            ['title','subtitle'], ['bags_tabs'],
            ['title','image','group','mobile_title','mobile_image','pc_visible','mobile_visible'], [],
            [], ['title'=>'样品名称','image'=>'样品图片','group'=>'所属 Tabs','mobile_title'=>'移动样品名称','mobile_image'=>'移动样品图片'],
            ['bags_tabs'=>'筛选 Tabs']
        );
        $schemas['bags_samples']['dynamic_group_source'] = 'bags_tabs';
        $schemas['bags_samples']['dynamic_group_multiple'] = true;
        $schemas['bags_samples']['item_help']['group'] = '可多选当前“筛选 Tabs”；同一个样品可同时出现在多个分类中。';
        unset($schemas['bags_samples']['scalar_help']['bags_tabs']);
        $schemas['bags_crafts'] = self::make('bags', '包装袋无版印刷', '常用印刷工艺', 'bags_section',
            ['title','subtitle'], [],
            ['title','image','mobile_title','mobile_image','pc_visible','mobile_visible'], [],
            [], ['title'=>'工艺名称','image'=>'工艺图片','mobile_title'=>'移动工艺名称','mobile_image'=>'移动工艺图片']
        );
        $schemas['bags_process'] = self::make('bags', '包装袋无版印刷', '定制流程', 'bags_section',
            ['title'], [],
            ['title','text','subtitle','badge','image','group','mobile_title','mobile_text','mobile_subtitle','mobile_badge','mobile_image','pc_visible','mobile_visible'],
            ['step'=>'流程步骤','photo'=>'流程场景图'],
            [], ['title'=>'步骤/图片标题','text'=>'步骤说明','subtitle'=>'步骤编号','badge'=>'图标类型','image'=>'场景图片','group'=>'项目类型','mobile_title'=>'移动标题','mobile_text'=>'移动说明','mobile_subtitle'=>'移动编号','mobile_badge'=>'移动图标类型','mobile_image'=>'移动场景图片']
        );
        $schemas['bags_cta'] = self::make('bags', '包装袋无版印刷', '六项服务保障', 'bags_section',
            [], [],
            ['title','text','badge','mobile_title','mobile_text','mobile_badge','pc_visible','mobile_visible'], [],
            [], ['title'=>'保障标题','text'=>'英文/补充说明','badge'=>'图标类型','mobile_title'=>'移动保障标题','mobile_text'=>'移动说明','mobile_badge'=>'移动图标类型']
        );

        // 彩盒（11）
        $schemas['boxes_hero'] = self::make('boxes', '彩盒', 'Banner', 'boxes_section',
            ['title','subtitle','content','image','mobile_image','link'], [], [], [],
            ['title'=>'Banner 标题','subtitle'=>'行业标签','content'=>'Banner 说明','image'=>'PC 背景图','mobile_image'=>'移动背景图','link'=>'CTA 按钮'], []
        );
        $schemas['boxes_products'] = self::make('boxes', '彩盒', '产品展示', 'boxes_section',
            ['title','subtitle'], [],
            ['title','image','group','mobile_title','mobile_image','pc_visible','mobile_visible'],
            ['main'=>'主展示图',''=>'普通展示图'], [],
            ['title'=>'产品名称','image'=>'产品图片','group'=>'展示位置','mobile_title'=>'移动产品名称','mobile_image'=>'移动产品图片']
        );
        $schemas['boxes_value'] = self::make('boxes', '彩盒', '价值图文', 'boxes_section',
            ['title'], [],
            ['title','text','image','group','mobile_title','mobile_text','mobile_image','pc_visible','mobile_visible'],
            ['normal'=>'图左文右','reverse'=>'图右文左'], [],
            ['title'=>'标题','text'=>'说明','image'=>'图片','group'=>'版式方向','mobile_title'=>'移动标题','mobile_text'=>'移动说明','mobile_image'=>'移动图片']
        );
        $schemas['boxes_promise'] = self::make('boxes', '彩盒', '品质承诺', 'boxes_section',
            ['title','subtitle','content'], ['boxes_secondary_title','boxes_secondary_text','boxes_badge_text'], [], [],
            ['subtitle'=>'主承诺文案','content'=>'副承诺文案'], [], ['boxes_secondary_title'=>'面板标题','boxes_secondary_text'=>'面板说明（支持换行）','boxes_badge_text'=>'面板徽标文字']
        );
        $schemas['boxes_details'] = self::make('boxes', '彩盒', '细节展示', 'boxes_section',
            ['title','subtitle'], ['boxes_secondary_title','boxes_secondary_text','boxes_badge_text'],
            ['title','text','subtitle','image','group','mobile_title','mobile_text','mobile_subtitle','mobile_image','pc_visible','mobile_visible'],
            ['normal'=>'图左文右','reverse'=>'图右文左'],
            [], ['title'=>'细节主标题','text'=>'底部说明','subtitle'=>'编号/标签','image'=>'细节图片','group'=>'版式方向','mobile_title'=>'移动主标题','mobile_text'=>'移动说明','mobile_subtitle'=>'移动编号/标签','mobile_image'=>'移动图片'],
            ['boxes_secondary_title'=>'底部采购 CTA 标题','boxes_secondary_text'=>'底部采购 CTA 说明（支持换行）','boxes_badge_text'=>'CTA 徽标文字']
        );
        $schemas['boxes_purchase'] = self::make('boxes', '彩盒', '采购说明', 'boxes_section',
            ['title','content'], [], [], [], ['content'=>'说明文字']
        );
        $schemas['boxes_applications'] = self::make('boxes', '彩盒', '应用场景', 'boxes_section',
            ['title','subtitle'], [],
            ['title','image','mobile_title','mobile_image','pc_visible','mobile_visible'], [],
            [], ['title'=>'场景名称','image'=>'场景图片','mobile_title'=>'移动场景名称','mobile_image'=>'移动场景图片']
        );
        $schemas['boxes_craft_material'] = self::make('boxes', '彩盒', '工艺与材质', 'boxes_section',
            ['title'], ['boxes_secondary_title'],
            ['title','subtitle','image','group','mobile_title','mobile_subtitle','mobile_image','pc_visible','mobile_visible'],
            ['craft'=>'印刷工艺','material'=>'产品材质'], [],
            ['title'=>'名称','subtitle'=>'英文副标题','image'=>'图片','group'=>'项目类型','mobile_title'=>'移动名称','mobile_subtitle'=>'移动英文副标题','mobile_image'=>'移动图片'],
            ['boxes_secondary_title'=>'材质区标题']
        );
        $schemas['boxes_team'] = self::make('boxes', '彩盒', '专业团队', 'boxes_section',
            ['title'], [],
            ['title','image','mobile_title','mobile_image','pc_visible','mobile_visible'], [],
            [], ['title'=>'图片说明','image'=>'团队图片','mobile_title'=>'移动图片说明','mobile_image'=>'移动团队图片']
        );
        $schemas['boxes_types'] = self::make('boxes', '彩盒', '常见盒型展示', 'boxes_section',
            ['title','subtitle','image','mobile_image'], [], [], [],
            ['image'=>'PC 盒型整图','mobile_image'=>'移动盒型整图']
        );
        $schemas['boxes_services'] = self::make('boxes', '彩盒', '服务保障', 'boxes_section',
            [], [], ['title','text','badge','mobile_title','mobile_text','mobile_badge','pc_visible','mobile_visible'], [],
            [], ['title'=>'服务标题','text'=>'英文/补充说明','badge'=>'图标类型','mobile_title'=>'移动服务标题','mobile_text'=>'移动说明','mobile_badge'=>'移动图标类型']
        );

        // 走进金亚（4）
        $schemas['about_hero'] = self::make('about', '走进金亚', 'Banner', 'about_section',
            ['image','mobile_image'], [], [], [],
            ['image'=>'PC 背景图','mobile_image'=>'移动背景图'], []
        );
        $schemas['about_values'] = self::make('about', '走进金亚', '品质理念', 'about_section',
            ['title'], [],
            ['title','text','image','group','mobile_title','mobile_text','mobile_image','pc_visible','mobile_visible'],
            ['normal'=>'图左文右','reverse'=>'图右文左'], [],
            ['title'=>'理念标题','text'=>'理念说明','image'=>'配图','group'=>'版式方向','mobile_title'=>'移动标题','mobile_text'=>'移动说明','mobile_image'=>'移动配图']
        );
        $schemas['about_stats'] = self::make('about', '走进金亚', '企业指标', 'about_section',
            [], [], ['title','value','subtitle','mobile_title','mobile_subtitle','pc_visible','mobile_visible'], [],
            [], ['title'=>'指标名称','value'=>'数值','subtitle'=>'单位','mobile_title'=>'移动指标名称','mobile_subtitle'=>'移动单位']
        );
        $schemas['about_stats']['item_editable_unit_fields'] = ['subtitle','mobile_subtitle'];
        $schemas['about_culture_source'] = self::make('about', '走进金亚', '企业文化', 'about_section',
            ['title','subtitle','image','mobile_image'], [],
            ['title','text','badge','mobile_title','mobile_text','mobile_badge','pc_visible','mobile_visible'], [],
            ['image'=>'PC 背景图','mobile_image'=>'移动背景图'],
            ['title'=>'文化标题','text'=>'文化说明','badge'=>'图标类型','mobile_title'=>'移动标题','mobile_text'=>'移动说明','mobile_badge'=>'移动图标类型']
        );

        // 联系我们（3）
        $schemas['contact_hero'] = self::make('contact', '联系我们', 'Banner', 'contact_section',
            ['title','content','image','mobile_image','link'], [], [], [],
            ['title'=>'Banner 标题','content'=>'Banner 说明','image'=>'PC 背景图','mobile_image'=>'移动背景图','link'=>'CTA 按钮'], []
        );
        $schemas['contact_info'] = self::make('contact', '联系我们', '联系信息', 'contact_section',
            ['title','content','image','mobile_image'], ['contact_company_title','contact_map_image','contact_map_provider','contact_baidu_ak','contact_map_lng','contact_map_lat','contact_map_zoom','contact_map_marker_title','contact_map_marker_address','contact_map_zoom_control','contact_map_scroll_wheel'],
            ['title','text','badge','mobile_title','mobile_text','mobile_badge','pc_visible','mobile_visible'], [],
            ['title'=>'客服标题','content'=>'公司介绍','image'=>'PC 客服头像','mobile_image'=>'移动客服头像'],
            ['title'=>'联系方式名称','text'=>'联系方式内容','badge'=>'图标类型','mobile_title'=>'移动名称','mobile_text'=>'移动内容','mobile_badge'=>'移动图标类型'],
            ['contact_company_title'=>'公司名称','contact_map_image'=>'地图备用图片','contact_map_provider'=>'地图类型','contact_baidu_ak'=>'百度地图浏览器端 AK','contact_map_lng'=>'百度地图经度','contact_map_lat'=>'百度地图纬度','contact_map_zoom'=>'地图缩放级别','contact_map_marker_title'=>'地图标记名称','contact_map_marker_address'=>'地图标记地址','contact_map_zoom_control'=>'显示缩放控件','contact_map_scroll_wheel'=>'允许滚轮缩放']
        );
        $schemas['contact_thanks'] = self::make('contact', '联系我们', '感谢客户', 'contact_section',
            ['title','subtitle','content','image','mobile_image'], ['contact_phone'], [], [],
            ['content'=>'感谢文字','image'=>'PC 背景图','mobile_image'=>'移动背景图'], [], ['contact_phone'=>'咨询热线']
        );

        $schemas['label_compare']['group_item_fields'] = [
            'compare' => ['title','text','subtitle','group','mobile_title','mobile_text','mobile_subtitle','pc_visible','mobile_visible'],
            'parameter' => ['title','text','subtitle','badge','value','group','mobile_title','mobile_text','mobile_subtitle','mobile_badge','pc_visible','mobile_visible'],
        ];
        $schemas['label_materials']['group_item_fields'] = [
            'craft-image' => ['title','image','group','mobile_title','mobile_image','pc_visible','mobile_visible'],
            'craft-note' => ['title','text','group','mobile_title','mobile_text','pc_visible','mobile_visible'],
            'print-image' => ['title','image','group','mobile_title','mobile_image','pc_visible','mobile_visible'],
        ];
        $schemas['label_elements']['group_item_fields'] = [
            'intro-direction' => ['title','text','image','group','mobile_title','mobile_text','mobile_image','pc_visible','mobile_visible'],
            'intro-core' => ['title','text','image','group','mobile_title','mobile_text','mobile_image','pc_visible','mobile_visible'],
            'intro-diameter' => ['title','text','image','group','mobile_title','mobile_text','mobile_image','pc_visible','mobile_visible'],
        ];
        $schemas['label_service']['group_item_fields'] = [
            'process' => ['title','text','badge','group','mobile_title','mobile_text','mobile_badge','pc_visible','mobile_visible'],
            'guarantee' => ['title','text','badge','group','mobile_title','mobile_text','mobile_badge','pc_visible','mobile_visible'],
        ];
        $schemas['bags_process']['group_item_fields'] = [
            'step' => ['title','text','subtitle','badge','group','mobile_title','mobile_text','mobile_subtitle','mobile_badge','pc_visible','mobile_visible'],
            'photo' => ['title','image','group','mobile_title','mobile_image','pc_visible','mobile_visible'],
        ];
        $schemas['label_advantages']['group_item_fields'] = [
            'photo' => ['image','group','mobile_image','pc_visible','mobile_visible'],
            'adv' => ['title','text','badge','group','mobile_title','mobile_text','mobile_badge','pc_visible','mobile_visible'],
        ];

        // Only these blocks consume badge/mobile_badge as visual icons. Other
        // badge fields are ordinary text/markers and must stay plain inputs.
        foreach ([
            'label_hero','label_service','label_advantages',
            'bags_hero','bags_compare','bags_process','bags_cta',
            'boxes_hero','boxes_services',
            'about_hero','about_culture_source',
            'contact_hero','contact_info',
        ] as $iconBlockKey) {
            $schemas[$iconBlockKey]['item_icon_fields'] = ['badge','mobile_badge'];
        }

        // Presentation-only metadata for the backend functional-item editor.
        // These values never alter the stored extra_json structure; they only
        // make the shared editor read like the existing homepage editors.
        $itemOrder = [
            'group',
            'title','mobile_title',
            'text','mobile_text',
            'subtitle','mobile_subtitle',
            'value',
            'image','mobile_image',
            'image_top','mobile_image_top',
            'image_bottom','mobile_image_bottom',
            'badge','mobile_badge',
            'url','mobile_url',
            'pc_visible','mobile_visible',
        ];
        foreach ($schemas as &$schema) {
            $schema['item_section_label'] = '功能项目';
            $schema['item_noun'] = '项目';
            $schema['item_add_label'] = '添加项目';
            $schema['item_order'] = $itemOrder;
        }
        unset($schema);

        $itemPresentation = [
            'label_hero' => ['卖点','添加卖点'],
            'label_quote' => ['选择理由','添加选择理由'],
            'label_samples' => ['产品','添加产品'],
            'label_capability' => ['图文项目','添加图文项目'],
            'label_compare' => ['表格行','添加表格行'],
            'label_materials' => ['工艺','添加工艺'],
            'label_elements' => ['说明项目','添加说明项目'],
            'label_service' => ['服务项目','添加服务项目'],
            'label_advantages' => ['优势项目','添加优势项目'],
            'bags_hero' => ['卖点','添加卖点'],
            'bags_products' => ['产品','添加产品'],
            'bags_compare' => ['对比项','添加对比项'],
            'bags_cases' => ['案例','添加案例'],
            'bags_samples' => ['样品','添加样品'],
            'bags_crafts' => ['工艺','添加工艺'],
            'bags_process' => ['流程项目','添加流程项目'],
            'bags_cta' => ['服务保障','添加服务保障'],
            'boxes_hero' => ['卖点','添加卖点'],
            'boxes_products' => ['产品','添加产品'],
            'boxes_value' => ['价值图文','添加价值图文'],
            'boxes_details' => ['细节展示','添加细节展示'],
            'boxes_applications' => ['应用场景','添加应用场景'],
            'boxes_craft_material' => ['工艺/材质','添加工艺/材质'],
            'boxes_team' => ['团队图片','添加团队图片'],
            'boxes_services' => ['服务保障','添加服务保障'],
            'about_hero' => ['卖点','添加卖点'],
            'about_values' => ['品质理念','添加品质理念'],
            'about_stats' => ['企业指标','添加企业指标'],
            'about_culture_source' => ['企业文化','添加企业文化'],
            'contact_hero' => ['卖点','添加卖点'],
            'contact_info' => ['联系方式','添加联系方式'],
        ];
        foreach ($itemPresentation as $key => $presentation) {
            if (!isset($schemas[$key])) {
                continue;
            }
            $schemas[$key]['item_noun'] = $presentation[0];
            $schemas[$key]['item_add_label'] = $presentation[1];
        }

        $schemas['label_materials']['item_section_label'] = '常用工艺配置';
        $schemas['label_materials']['item_add_label'] = '添加常用工艺';

        // label_quote is a reasons list rather than a generic item collection.
        // Keep its stored fields unchanged, but make the backend wording match
        // the actual business meaning shown by the active PC/Mobile templates.
        $schemas['label_quote']['item_section_label'] = '选择金亚的理由';
        $schemas['label_quote']['item_help']['badge'] = '右侧强调文案，支持换行；例如“智能检测\n快速响应”。';
        $schemas['label_quote']['item_help']['mobile_badge'] = '移动端独立强调文案；留空时继承 PC 右侧强调标签。';

        // Ability-image rows intentionally keep the three PC image positions
        // together, followed by the three mobile positions.  The shared JS
        // editor consumes this metadata without introducing a dedicated form.
        $schemas['label_capability']['item_order'] = [
            'group',
            'title','mobile_title',
            'text','mobile_text',
            'image_top','image_bottom','image',
            'mobile_image_top','mobile_image_bottom','mobile_image',
            'pc_visible','mobile_visible',
        ];
        $schemas['label_capability']['item_pair_rows'] = [
            ['title','mobile_title'],
            ['image_top','image_bottom'],
            ['mobile_image_top','mobile_image_bottom'],
        ];
        // PC / 移动说明都使用整行富文本编辑器；帮助文案由编辑器自身显示。
        $schemas['label_capability']['item_help']['text'] = '';
        $schemas['label_capability']['item_help']['mobile_text'] = '';
        $schemas['label_capability']['item_help']['image_top'] = 'PC 拼图左上图片；与左下图、右侧大图共同组成三图布局。';
        $schemas['label_capability']['item_help']['image_bottom'] = 'PC 拼图左下图片；与左上图、右侧大图共同组成三图布局。';
        $schemas['label_capability']['item_help']['image'] = 'PC 拼图右侧大图，跨上下两行；历史只有这一张图时会自动按单图铺满。';
        $schemas['label_capability']['item_help']['mobile_image_top'] = '移动端左上图；留空时继承 PC 左上图片。';
        $schemas['label_capability']['item_help']['mobile_image_bottom'] = '移动端左下图；留空时继承 PC 左下图片。';
        $schemas['label_capability']['item_help']['mobile_image'] = '移动端主图；留空时继承 PC 右侧大图。';

        $schemas['label_elements']['item_section_label'] = '卷标三要素说明';
        $schemas['label_elements']['item_noun'] = '说明项目';
        $schemas['label_elements']['item_add_label'] = '添加说明项目';
        $schemas['label_elements']['item_help']['image'] = '上传当前说明项目的 PC 示意图；未配置时前台不显示图片区域。';
        $schemas['label_elements']['item_help']['mobile_image'] = '移动端独立示意图；留空时继承 PC 示意图。';
        $schemas['label_elements']['help'] = '当前编辑：卷标三要素。只保留出标方向、卷芯规格、卷芯外直径测量三个说明项目；旧箭头和卷芯圆圈项目已退役。';

        return $schemas;
    }

    private static function make($page, $pageLabel, $label, $blockType, array $baseFields, array $scalarFields, array $itemFields, array $groups = [], array $baseLabels = [], array $itemLabels = [], array $scalarLabels = [])
    {
        return [
            'page' => $page,
            'page_label' => $pageLabel,
            'label' => $label,
            'block_type' => $blockType,
            'base_fields' => array_values($baseFields),
            'scalar_fields' => array_values($scalarFields),
            'item_fields' => array_values($itemFields),
            'groups' => $groups,
            'group_item_fields' => [],
            'item_icon_fields' => [],
            'item_editable_unit_fields' => [],
            'has_items' => !empty($itemFields),
            'base_labels' => $baseLabels,
            'item_labels' => $itemLabels,
            'scalar_labels' => $scalarLabels,
            'base_help' => self::baseHelp($baseFields),
            'item_help' => self::itemHelp($itemFields),
            'scalar_help' => self::scalarHelp($scalarFields),
            'help' => self::help($page, $label),
        ];
    }

    private static function baseHelp(array $fields)
    {
        $all = [
            'content' => '支持 Markdown；仅用于当前功能块正文。',
            'image' => 'PC 端图片。建议保持与当前默认素材相同的尺寸或宽高比例，避免版式变形。',
            'mobile_image' => '移动端独立图片；留空时自动继续使用 PC 图片。',
        ];
        return self::pickHelp($fields, $all);
    }

    private static function itemHelp(array $fields)
    {
        $all = [
            'text' => '仅填写当前子项目在前台实际显示的说明内容。',
            'image' => 'PC 子项目图片。建议与同一功能块现有图片保持一致尺寸或比例。',
            'mobile_image' => '移动端独立子项目图片；留空时使用 PC 图片。',
            'group' => '决定该子项目在当前功能块中的版式/分组；请从当前功能块允许的选项中选择。',
            'badge' => '前台模板使用的图标或标记类型。',
            'mobile_badge' => '移动端独立图标/标记；留空时沿用 PC 设置。',
            'mobile_title' => '移动端独立标题；留空时沿用 PC 标题。',
            'mobile_text' => '移动端独立说明；留空时沿用 PC 说明。',
            'mobile_subtitle' => '移动端独立副标题/单位；留空时沿用 PC 设置。',
            'mobile_url' => '移动端独立链接；留空时沿用 PC 链接。',
        ];
        return self::pickHelp($fields, $all);
    }

    private static function scalarHelp(array $fields)
    {
        $all = [
            'label_consult_url' => '支持站内相对地址或完整 URL。',
            'label_button_url' => '支持站内相对地址或完整 URL。',
            'label_video_url' => '左侧展示视频；支持上传 MP4/WebM/Ogg，或填写浏览器可直接播放的视频地址。',
            'bags_tabs' => '每行一个选项，格式：显示文字|分组值。',
            'label_print_title' => '对应 html/labels.html 的“不干胶印刷”独立区块标题。',
            'label_print_points' => '对应“不干胶印刷”左侧要点，支持 Markdown 和换行。',
            'bags_compare_brand' => '对应专版/无版对比区左侧 JINYA PACKAGE 小标题。',
            'bags_compare_footer' => '对应对比区底部“按需要定制 灵活可控”等提示。',
            'contact_map_image' => '静态地图/位置示意图。未启用百度地图、AK/坐标缺失或 API 加载失败时自动显示该图片。',
            'contact_map_provider' => '选择“百度地图”后才会尝试加载 JSAPI；默认“静态图片”保持现有行为。',
            'contact_baidu_ak' => '填写百度地图开放平台申请的“浏览器端 AK”。建议在百度控制台配置 Referer 白名单。',
            'contact_map_lng' => '百度地图使用的 BD09 经度，例如 113.600000。',
            'contact_map_lat' => '百度地图使用的 BD09 纬度，例如 34.760000。',
            'contact_map_zoom' => '建议 14–18；企业位置展示通常使用 16。',
            'contact_map_marker_title' => '地图标记点和信息窗口显示的公司/地点名称。',
            'contact_map_marker_address' => '点击地图标记后显示的详细地址。',
            'contact_map_zoom_control' => '是否在地图右下角显示 + / - 缩放控件。',
            'contact_map_scroll_wheel' => '是否允许鼠标滚轮缩放地图；移动端仍可使用触摸手势。',
        ];
        return self::pickHelp($fields, $all);
    }

    private static function pickHelp(array $fields, array $all)
    {
        $result = [];
        foreach ($fields as $field) {
            if (isset($all[$field])) {
                $result[$field] = $all[$field];
            }
        }
        return $result;
    }

    private static function help($page, $label)
    {
        return '当前编辑：' . $label . '。仅显示前台模板实际使用的配置；被隐藏的历史字段仍保留，不会因本次保存被清空。';
    }
}

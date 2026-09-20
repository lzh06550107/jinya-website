<?php
namespace app\common\service\cms;

class AboutPageDefaults
{
    public static function page()
    {
        return [
            'title'=>'走近金亚','summary'=>'了解金亚包装的品质理念、生产实力与企业文化。',
            'seo_title'=>'走近金亚 - 金亚包装','seo_keywords'=>'金亚包装,包装印刷,生产实力,企业文化',
            'seo_description'=>'走近金亚包装，了解公司品质理念、生产能力、快速响应和企业文化。',
            'weigh'=>870,'slug'=>'about','template'=>'about','page_type'=>'about','canonical_url'=>'/page/about','status'=>'published',
        ];
    }

    public static function blocks()
    {
        return [
            self::block('about_hero', "走近金亚包装\n专业制造 值得信赖", '', '围绕客户对稳定包装供应的长期需求，金亚持续完善设计、印刷、制作与品质管控能力；以专业团队、成熟设备和高效协作，为客户提供可靠、省心的一站式包装服务。', '/uploads/cms-jinya/about/home-hero.jpg', 1080, [
                self::item('个性化定制','','','','badge','custom'),
                self::item('专业团队','','','','badge','team'),
                self::item('大小批量','','','','badge','layers'),
                self::item('接加急单','','','','badge','truck'),
            ]),
            self::block('about_values', '金亚包装，专注做好一件事：打造高品质包装', '', '', '', 1070, [
                self::item('质量保证 · 用心印好包装','我们脚踏实地深耕包装印刷，专注把控全流程，从设计到印刷，深入遵循各项行业规范。我们坚信，品质是企业的生命线，更是商家立足市场的根本。缺少稳定的质量保障，就留不住回头客，更没有长远的发展空间。坚持严守工艺标准、打磨每一处细节，以扎实可靠的成品赢得客户信赖，与合作伙伴长久携手、稳步共赢。','/uploads/cms-jinya/about/about-block-1.jpg','','normal',''),
                self::item('增效工艺，保障细节清晰呈现。','从前期设计落地到印刷成品，我们遵循行业规范，持续优化整套生产流程。依托成熟增效工艺，在提升交付效率的同时，不放过任何一处细微瑕疵。我们始终笃信品质为立足根本，脚踏实地专注包装印刷。严苛管控色彩、材质、模切、粘合等每一道工序，用精细化生产守住产品质感。效率与品质双向兼顾，既缩短客户等待周期，又稳定把控成品标准。依靠扎实工艺呈现精致细节，持续收获客户认可与复购，携手合作伙伴稳步拓远市场。','/uploads/cms-jinya/about/about-block-2.jpg','','reverse',''),
                self::item('快捷高效，直击客户核心需求','快捷高效，是众多客户最为看重的合作要点。从方案对接、样品打样到成品出货，我们理顺全流程工序，减少不必要等待。依托成熟印刷工艺与灵活生产模式，兼顾效率与品质。我们深知，客户的市场节奏不容耽搁，稳定的交付时效助力大家抢抓商机。在提速的同时严守质量标准，拒绝只求速度忽略细节。用省心顺畅的合作体验，赢得长期信赖。','/uploads/cms-jinya/about/about-block-3.jpg','','normal',''),
                self::item('品质稳定｜灵活定制，赋能伙伴发展','稳定品质是长久合作的根基，从设计到印刷严守行业规范，层层品控，保障成品效果统一。同时支持多样化灵活定制，兼容大小批量订单，按需调整材质、工艺与方案。兼顾标准化品控与个性化需求，不忽视细节瑕疵，落地客户各类包装构想。以持续可靠的产品，助力合作伙伴塑造品牌形象，抓住市场机遇。','/uploads/cms-jinya/about/about-block-4.jpg','','reverse',''),
            ]),
            self::block('about_stats', '', '', '', '', 1060, [
                self::item('印刷生产基地','','','10000','stat','', 'm²'),
                self::item('产能标签/袋/盒','','','100','stat','', '万+'),
                self::item('自动化生产线','','','20','stat','', '条'),
                self::item('快速响应24小时内出货','','','24','stat','', '小时'),
                self::item('快速出稿','','','6','stat','', '小时'),
            ]),
            self::block('about_culture_source', '企业文化', 'CORPORATE CULTURE', '', '/uploads/cms-jinya/about/about-2.jpg', 1050, [
                self::item('企业愿景',"深耕包装，创造持续价值\n设计创造精品\n包装传递价值",'','','culture','compass'),
                self::item('企业使命',"赋能客户，造福社会\n成为员工幸福、客户满意\n社会尊敬的幸福企业",'','','culture','team'),
                self::item('核心价值观',"诚信 品质 高效\n创新 共创 共赢",'','','culture','target'),
                self::item('企业精神',"以客户为中心\n脚踏实地 协作致远\n专注 务实 热情 严谨 感恩",'','','culture','layers'),
            ]),
        ];
    }

    private static function block($key,$title,$subtitle,$content,$image,$weigh,array $items)
    {
        return [
            'block_key'=>$key,'title'=>$title,'subtitle'=>$subtitle,'content'=>$content,
            'image'=>$image,'mobile_image'=>'','link_text'=>'','link_url'=>'',
            'pc_visible'=>1,'mobile_visible'=>1,'weigh'=>$weigh,'status'=>'normal',
            'extra'=>['items'=>$items],
        ];
    }

    private static function item($title,$text='',$image='',$value='',$group='',$badge='',$subtitle='')
    {
        return [
            'title'=>$title,'text'=>$text,'image'=>$image,'value'=>$value,'subtitle'=>$subtitle,
            'group'=>$group,'badge'=>$badge,'url'=>'','pc_visible'=>1,'mobile_visible'=>1,
        ];
    }
}

<?php
namespace app\common\service\cms;

class ContactPageDefaults
{
    public static function page()
    {
        return [
            'title'=>'联系我们','summary'=>'联系金亚包装，获取包装定制、打样、生产与交付建议。',
            'seo_title'=>'联系我们 - 金亚包装','seo_keywords'=>'金亚包装,联系我们,包装定制,郑州包装厂家',
            'seo_description'=>'联系郑州金亚包装有限公司，获取不干胶标签、包装袋、彩盒等包装定制服务。',
            'weigh'=>860,'slug'=>'contact','template'=>'contact','page_type'=>'contact','canonical_url'=>'/page/contact','status'=>'published',
        ];
    }

    public static function blocks()
    {
        return [
            self::block('contact_hero', "联系金亚包装\n高效沟通 快速响应", '', '围绕您的产品类型、包装形式、数量与交期需求，提供快速沟通、专业建议与定制方案；从需求确认到打样生产，我们将及时响应并持续跟进，让合作推进更高效、更省心。', '/uploads/cms-jinya/contact/contact-office.jpg', 1040, [
                self::item('个性化定制','','','','','custom'),
                self::item('专业团队','','','','','team'),
                self::item('大小批量','','','','','layers'),
                self::item('接加急单','','','','','truck'),
            ]),
            self::block('contact_info', '欢迎您进入金亚包装网站', '', "郑州金亚包装有限公司是一家集设计、印刷、制作于一体的一站式包装生产厂家，公司深耕细作包装领域，专注于农化、食品、中药类、保健类包装定制。\n公司汇聚了知名美院设计师及经验丰富的印刷团队，配备5台高精度印刷机、8台高速复合机、10台模切机及多台分条制袋机等自动化生产设备，日产标签100万张，包装袋80万+，纸盒20万。以丰富的工作经验、严谨的工作态度和全面的服务体系，为每一款包装的品质护航。\n主营：不干胶标签/包装袋/无版印刷/彩盒；支持大小批量，快速出货。\n企业坚守诚信、共赢、创新经营理念，并致力于实践\"诚信立基、创新引领、严谨求实、守时高效\"的精神风貌与工作作风，为客户提供卓越的产品与服务，共邀各界新老客户咨询洽谈，携手共创行业发展新机遇。", '/uploads/cms-jinya/contact/contact-avatar.jpg', 1030, [
                self::item('咨询热线：',"18903716652\n15824809297",'','','','phone'),
                self::item('公司邮箱：','973123908@qq.com','','','','email'),
                self::item('公司地址：','河南省郑州市中原区电厂路70号华强广场','','','','map'),
            ], [
                'company_title'=>'郑州金亚包装有限公司',
                'map_image'=>'/uploads/cms-jinya/contact/contact-map.jpg',
                'map_provider'=>'static',
                'baidu_ak'=>'',
                'map_lng'=>'',
                'map_lat'=>'',
                'map_zoom'=>'16',
                'map_marker_title'=>'郑州金亚包装有限公司',
                'map_marker_address'=>'河南省郑州市中原区电厂路70号华强广场',
                'map_zoom_control'=>'1',
                'map_scroll_wheel'=>'1',
            ]),
            self::block('contact_thanks', '感恩1000+客户的支持', 'THANKS TO', '感恩一路携手相伴，并肩奋进的岁月！每一份订单，承载着您对终端客户的责任与信赖。经由您推向市场的，不只是包装产品，更是精工造物的初心。往后我们持续精进印刷工艺，完善定制化配套服务，携手各位客户、伙伴，同心开拓崭新商机。', '/uploads/cms-jinya/contact/workshop-1.jpg', 1020, [], [
                'phone'=>'18903716652',
            ]),
        ];
    }

    private static function block($key,$title,$subtitle,$content,$image,$weigh,array $items,array $extra=[])
    {
        $extra['items']=$items;
        return [
            'block_key'=>$key,'title'=>$title,'subtitle'=>$subtitle,'content'=>$content,
            'image'=>$image,'mobile_image'=>'','link_text'=>'','link_url'=>'',
            'pc_visible'=>1,'mobile_visible'=>1,'weigh'=>$weigh,'status'=>'normal','extra'=>$extra,
        ];
    }

    private static function item($title,$text='',$image='',$value='',$group='',$badge='')
    {
        return [
            'title'=>$title,'text'=>$text,'image'=>$image,'value'=>$value,'subtitle'=>'',
            'group'=>$group,'badge'=>$badge,'url'=>'','pc_visible'=>1,'mobile_visible'=>1,
        ];
    }
}

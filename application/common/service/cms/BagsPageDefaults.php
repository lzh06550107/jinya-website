<?php
namespace app\common\service\cms;

class BagsPageDefaults
{
    public static function page()
    {
        return [
            'title'=>'包装袋·无版印刷','summary'=>'包装袋与卷膜无版印刷定制，支持多袋型、多材质、灵活起订与快速交付。',
            'seo_title'=>'包装袋无版印刷 - 金亚包装','seo_keywords'=>'包装袋,无版印刷,卷膜,拉链袋,包装袋定制',
            'seo_description'=>'金亚包装提供包装袋与卷膜无版印刷定制，覆盖产品、应用案例、样品、工艺与定制流程。',
            'weigh'=>890,'slug'=>'bags','template'=>'bags','page_type'=>'bags','canonical_url'=>'/page/bags','status'=>'published',
        ];
    }
    public static function blocks()
    {
        return [
            self::block('bags_hero',"包装袋无版印刷\n灵活起订 快速交付",'', '围绕包装袋与卷膜定制需求，提供袋型、材质、规格与数量的灵活选择；从设计校对到无版印刷交付，全流程高效衔接，帮助客户降低库存压力并加快产品上市。','/uploads/cms-jinya/bags/bags-hero-products.jpg',1080,[
                'items'=>[
                    self::item('个性化定制','','','','badge','custom'),self::item('专业团队','','','','badge','team'),
                    self::item('大小批量','','','','badge','batch'),self::item('接加急单','','','','badge','urgent'),
                ],
            ]),
            self::block('bags_products','产品中心','高品质 · 高标准 · 高信誉','','',1070,[
                'items'=>[
                    self::item('包装袋产品陈列','', '/uploads/cms-jinya/bags/bags-prod-1.jpg','','main'),
                    self::item('肥料包装袋','', '/uploads/cms-jinya/bags/bags-prod-2.jpg','','small'),
                    self::item('站立袋产品','', '/uploads/cms-jinya/bags/bags-prod-3.jpg','','small'),
                    self::item('八边封袋产品','', '/uploads/cms-jinya/bags/bags-prod-4.jpg','','small'),
                    self::item('标签卷膜产品','', '/uploads/cms-jinya/bags/bags-prod-5.jpg','','small'),
                ],
            ]),
            self::block('bags_compare','专版和无版印刷怎么选','订单量、版费、价格与交期一目了然','','',1060,[
                'note'=>'批量越大，专版越有优势  |  交期越紧，无版更灵活  —  按需要定制，灵活可控',
                'items'=>[
                    self::item('专版印刷',"起订量高\n价格低\n有版费\n工期长",'','','compare','print'),
                    self::item('无版印刷',"起订量低\n价格偏高\n无版费\n工期短",'','','compare','clock'),
                ],
            ]),
            self::block('bags_cases','应用案例','个性定制  专业高效服务  省时省心  价格合理','','',1050,[
                'tabs'=>"全部|all\n肥料/农药类|t1\n食品类|t2\n中药类|t3\n宠物类|t4",
                'items'=>[
                    self::item('无版卷膜','无版卷膜实现专版印刷质感，采用膜内印刷工艺，成品观感出众。门槛低至30公斤即可起订，适配立式、卧式各类包装设备，灵活满足中小批量订单，快速投产省心高效。','/uploads/cms-jinya/bags/bags-case-1.jpg','/product/no-plate-roll-film','case','', '适用于：食品、中药类、农资、宠物类'),
                    self::item('无版拉链袋','无版拉链袋支持按需定制，拉链袋、站立袋款式齐全，没有版费尺寸不受限制。数码高清印刷，大小订单均可承接，1000个即可起订，灵活适配各类产品包装需求。','/uploads/cms-jinya/bags/bags-case-2.jpg','/product/no-plate-zipper-bag','case','', '适用于：食品、中药类、农资、宠物类'),
                    self::item('无版包装袋','无版包装袋涵盖三边封、站立袋等多种袋型，免收版费，1000个即可起订。不限图案色彩，支持随心定制，出货速度快，高效助力客户新品快速投产。','/uploads/cms-jinya/bags/bags-case-3.jpg','/product/no-plate-packaging-bag','case','', '适用于：食品、农资、农牧、宠物等'),
                ],
            ], '查看更多产品', '/products?category=no-plate-packaging'),
            self::block('bags_samples','包装样品展示','精工细作  脚踏实地','','',1040,[
                'tabs'=>"无版卷膜/无版拉链袋|g1\n无版印刷包装袋|g2\n凹版定制|g3",
                'items'=>[
                    self::item('农肥中转袋','', '/uploads/cms-jinya/bags/sample-nongfei.jpg'),self::item('大米包装袋','', '/uploads/cms-jinya/bags/sample-dami.jpg'),
                    self::item('宠物猫袋袋','', '/uploads/cms-jinya/bags/sample-cat.jpg'),self::item('硝膜八边封袋','', '/uploads/cms-jinya/bags/sample-baofeng.jpg'),
                    self::item('肥类包装','', '/uploads/cms-jinya/bags/sample-feiliao.jpg'),self::item('八边封包装袋','', '/uploads/cms-jinya/bags/sample-babianfeng.jpg'),
                    self::item('自动卷膜','', '/uploads/cms-jinya/bags/sample-juanmo1.jpg'),self::item('卷膜','', '/uploads/cms-jinya/bags/sample-juanmo2.jpg'),
                    self::item('斜嘴手提袋','', '/uploads/cms-jinya/bags/sample-xiezui.jpg'),self::item('铝箔站立袋','', '/uploads/cms-jinya/bags/sample-lvbo.jpg'),
                    self::item('中转袋','', '/uploads/cms-jinya/bags/sample-zhongzhuan.jpg'),self::item('水果拉链中转袋','', '/uploads/cms-jinya/bags/sample-shuiguo.jpg'),
                ],
            ]),
            self::block('bags_crafts','常用印刷工艺','采购展示按需定制','','',1030,[
                'items'=>[
                    self::item('哑膜工艺','', '/uploads/cms-jinya/bags/craft-matte.jpg'),self::item('光膜工艺','', '/uploads/cms-jinya/bags/craft-gloss.jpg'),
                    self::item('UV工艺','', '/uploads/cms-jinya/bags/craft-uv.jpg'),self::item('镭射膜工艺','', '/uploads/cms-jinya/bags/craft-laser.jpg'),
                    self::item('烫金工艺','', '/uploads/cms-jinya/bags/craft-gold.jpg'),self::item('开窗工艺','', '/uploads/cms-jinya/bags/craft-window.jpg'),
                ],
            ]),
            self::block('bags_process','定制流程','','','',1020,[
                'items'=>[
                    self::item('我要定制','通过在线咨询或者电话咨询询问','','','process','headset','01'),
                    self::item('合算报价','客户提供需求初步报价','','','process','quote','02'),
                    self::item('沟通细节','客户提供需求，本公司提供方案、双方意见达成一致','','','process','chat','03'),
                    self::item('最终确认','全心全意按照客户要求进行生产','','','process','confirm','04'),
                    self::item('支付/生产','客户可直接到本公司或者线上确认，满意后签订订单合同','','','process','produce','05'),
                    self::item('交易完成','生产完成后，产品交予客户，满意后交易完成','','','process','deal','06'),
                ],
            ]),
            self::block('bags_cta','','','','/uploads/cms-jinya/bags/cta-designer.jpg',1010,['items'=>[]]),
        ];
    }
    private static function block($key,$title,$subtitle,$content,$image,$weigh,array $extra,$linkText='',$linkUrl='')
    {
        return ['block_key'=>$key,'title'=>$title,'subtitle'=>$subtitle,'content'=>$content,'image'=>$image,'mobile_image'=>'','link_text'=>$linkText,'link_url'=>$linkUrl,'pc_visible'=>1,'mobile_visible'=>1,'weigh'=>$weigh,'status'=>'normal','extra'=>$extra];
    }
    private static function item($title,$text='',$image='',$url='',$group='',$badge='',$subtitle='')
    {
        return ['title'=>$title,'text'=>$text,'image'=>$image,'url'=>$url,'group'=>$group,'badge'=>$badge,'subtitle'=>$subtitle,'pc_visible'=>1,'mobile_visible'=>1];
    }
}

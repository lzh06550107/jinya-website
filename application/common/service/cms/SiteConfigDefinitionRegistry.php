<?php

namespace app\common\service\cms;

/**
 * CMS 站点共享配置字段定义。
 *
 * 公共布局聚合编辑器与兼容的“网站配置”入口共同使用此定义，
 * 以保证 fa_config 始终是唯一数据源。
 */
class SiteConfigDefinitionRegistry
{
    public static function all()
    {
        return [
            'name' => ['title' => '网站名称', 'type' => 'string', 'default' => '我的网站', 'rule' => 'required'],
            'cms_company' => ['title' => '公司全称', 'type' => 'string', 'default' => '安徽科创美涂料科技股份有限公司', 'rule' => 'required'],
            'cms_slogan' => ['title' => '网站口号', 'type' => 'string', 'default' => '专业涂料产品与工程解决方案', 'rule' => ''],
            'cms_logo' => ['title' => '网站 Logo', 'type' => 'image', 'default' => '', 'rule' => ''],
            'cms_pc_product_banner' => ['title' => 'PC 产品栏目 Banner', 'type' => 'image', 'default' => '', 'rule' => ''],
            'cms_pc_news_banner' => ['title' => 'PC 新闻栏目 Banner', 'type' => 'image', 'default' => '', 'rule' => ''],
            'cms_pc_case_banner' => ['title' => 'PC 案例栏目 Banner', 'type' => 'image', 'default' => '', 'rule' => ''],
            'cms_pc_about_banner' => ['title' => 'PC 企业栏目 Banner', 'type' => 'image', 'default' => '', 'rule' => ''],
            'cms_mobile_product_banner' => ['title' => '移动端产品栏目 Banner', 'type' => 'image', 'default' => '', 'rule' => ''],
            'cms_mobile_news_banner' => ['title' => '移动端新闻栏目 Banner', 'type' => 'image', 'default' => '', 'rule' => ''],
            'cms_mobile_case_banner' => ['title' => '移动端案例栏目 Banner', 'type' => 'image', 'default' => '', 'rule' => ''],
            'cms_mobile_about_banner' => ['title' => '移动端企业栏目 Banner', 'type' => 'image', 'default' => '', 'rule' => ''],
            'cms_wechat_qr' => ['title' => '微信公众号二维码', 'type' => 'image', 'default' => '', 'rule' => ''],
            'cms_service_wecom_url' => ['title' => '企业微信在线客服链接', 'type' => 'string', 'default' => '', 'rule' => ''],
            'cms_service_wechat_qr' => ['title' => '客服企业微信二维码', 'type' => 'image', 'default' => '', 'rule' => ''],
            'cms_service_wechat_name' => ['title' => '客服名称', 'type' => 'string', 'default' => '金亚包装业务客服', 'rule' => ''],
            'cms_service_wechat_tip' => ['title' => '微信咨询提示语', 'type' => 'string', 'default' => '扫码添加客服，获取包装解决方案', 'rule' => ''],
            'cms_service_hours' => ['title' => '客服工作时间', 'type' => 'string', 'default' => '8:30-18:00', 'rule' => ''],
            'cms_douyin_qr' => ['title' => '抖音二维码', 'type' => 'image', 'default' => '', 'rule' => ''],
            'cms_kuaishou_qr' => ['title' => '快手二维码', 'type' => 'image', 'default' => '', 'rule' => ''],
            'cms_xiaohongshu_qr' => ['title' => '小红书二维码', 'type' => 'image', 'default' => '', 'rule' => ''],
            'cms_video_qr' => ['title' => '视频号二维码', 'type' => 'image', 'default' => '', 'rule' => ''],
            'cms_bilibili_qr' => ['title' => 'B站二维码', 'type' => 'image', 'default' => '', 'rule' => ''],
            'cms_phone' => ['title' => '服务热线', 'type' => 'string', 'default' => '400-006-8683', 'rule' => ''],
            'cms_email' => ['title' => '联系邮箱', 'type' => 'string', 'default' => '', 'rule' => 'email'],
            'cms_address' => ['title' => '企业地址', 'type' => 'string', 'default' => '请填写企业地址', 'rule' => ''],
            'cms_factory_address' => ['title' => '工厂地址', 'type' => 'string', 'default' => '', 'rule' => ''],
            'beian' => ['title' => '备案号', 'type' => 'string', 'default' => '', 'rule' => ''],
            'cms_copyright_year' => ['title' => '版权年份', 'type' => 'string', 'default' => date('Y'), 'rule' => ''],
            'cms_tech_support' => ['title' => '技术支持', 'type' => 'string', 'default' => '', 'rule' => ''],
        ];
    }

    public static function pick(array $names)
    {
        $all = self::all();
        $result = [];
        foreach ($names as $name) {
            if (!isset($all[$name])) {
                throw new \InvalidArgumentException('未注册的网站配置字段：' . $name);
            }
            $result[$name] = $all[$name];
        }
        return $result;
    }
}

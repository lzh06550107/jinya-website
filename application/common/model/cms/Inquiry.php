<?php

namespace app\common\model\cms;

class Inquiry extends BaseModel
{
    protected $name = 'cms_inquiry';

    protected $append = ['status_text', 'mobile_masked'];

    public function getStatusList()
    {
        return [
            'new' => '新线索',
            'pending' => '待联系',
            'contacted' => '已联系',
            'qualified' => '需求确认',
            'quoted' => '已报价',
            'following' => '持续跟进',
            'won' => '已成交',
            'closed' => '已关闭',
            'invalid' => '无效线索',
        ];
    }

    public function getStatusTextAttr($value, $data)
    {
        $list = $this->getStatusList();
        return isset($list[$data['status']]) ? $list[$data['status']] : $data['status'];
    }

    public function getMobileMaskedAttr($value, $data)
    {
        $mobile = isset($data['mobile']) ? (string)$data['mobile'] : '';
        return strlen($mobile) >= 7 ? substr($mobile, 0, 3) . '****' . substr($mobile, -4) : $mobile;
    }

    public function assignedAdmin()
    {
        return $this->belongsTo(\app\admin\model\Admin::class, 'assigned_admin_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function followups()
    {
        return $this->hasMany(InquiryFollowup::class, 'inquiry_id', 'id')->order('id desc');
    }

}

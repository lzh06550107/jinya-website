<?php

namespace app\common\model\cms;

class InquiryFollowup extends BaseModel
{
    protected $name = 'cms_inquiry_followup';

    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class, 'inquiry_id', 'id');
    }

    public function admin()
    {
        return $this->belongsTo(\app\admin\model\Admin::class, 'admin_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

}

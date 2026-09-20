<?php

namespace app\common\model\cms;

use app\common\service\cms\PublishStateMachine;
use app\common\service\cms\HtmlSanitizer;

abstract class ContentModel extends BaseModel
{
    protected $append = ['status_text'];

    public function getStatusList()
    {
        return PublishStateMachine::labels();
    }

    public function getStatusTextAttr($value, $data)
    {
        $status = isset($data['status']) ? $data['status'] : '';
        $list = $this->getStatusList();
        return isset($list[$status]) ? $list[$status] : $status;
    }

    public function setContentAttr($value)
    {
        return HtmlSanitizer::clean($value);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->where('publish_time', '<=', time());
    }
}

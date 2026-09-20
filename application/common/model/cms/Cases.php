<?php

namespace app\common\model\cms;

class Cases extends ContentModel
{
    protected $name = 'cms_case';

    public function setStartedAtAttr($value)
    {
        return $value === '' || $value === null ? null : (is_numeric($value) ? (int)$value : strtotime($value));
    }

    public function setCompletedAtAttr($value)
    {
        return $value === '' || $value === null ? null : (is_numeric($value) ? (int)$value : strtotime($value));
    }
}

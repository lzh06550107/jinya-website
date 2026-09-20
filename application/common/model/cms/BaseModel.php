<?php

namespace app\common\model\cms;

use think\Model;
use traits\model\SoftDelete;

abstract class BaseModel extends Model
{
    use SoftDelete;

    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    public function getNormalStatusList()
    {
        return ['normal' => '正常', 'hidden' => '隐藏'];
    }
}

<?php
namespace app\common\repository\cms;
interface CaseRepositoryInterface
{
    /** 首页案例展示按后台引用顺序读取。 */
    public function publishedByIds(array $ids);
}

<?php
namespace app\common\repository\cms;
use app\common\model\cms\Cases;
class ThinkCaseRepository extends RepositorySupport implements CaseRepositoryInterface
{
    public function publishedByIds(array $ids)
    {
        if (!$ids) return [];
        $rows = $this->rows(Cases::where('status', 'published')->where('publish_time', '<=', time())->where('id', 'in', $ids)->select());
        return $this->orderedByIds($rows, $ids);
    }
}

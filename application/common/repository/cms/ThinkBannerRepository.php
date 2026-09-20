<?php
namespace app\common\repository\cms;
use app\common\model\cms\Banner;
class ThinkBannerRepository implements BannerRepositoryInterface
{
    public function published($pageKey, $position, $terminal)
    {
        $field = $terminal === 'mobile' ? 'mobile_visible' : 'pc_visible';
        $now = time();
        $rows = Banner::where('page_key', $pageKey)->where('position', $position)->where('status', 'normal')->where($field, 1)
            ->where(function ($q) use ($now) { $q->whereNull('start_time')->whereOr('start_time', '<=', $now); })
            ->where(function ($q) use ($now) { $q->whereNull('end_time')->whereOr('end_time', '>=', $now); })
            ->order('weigh desc,id asc')->select();
        return $rows ? collection($rows)->toArray() : [];
    }
}

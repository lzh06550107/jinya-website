<?php

namespace app\common\service\cms;

use think\Db;

/**
 * 阻止删除仍被页面功能块引用的业务内容。
 */
class PageReferenceGuard
{
    protected $counter;

    public function __construct(callable $counter = null)
    {
        $this->counter = $counter ?: function ($contentType, array $ids) {
            return Db::name('cms_page_block_reference')
                ->where('content_type', $contentType)
                ->where('content_id', 'in', $ids)
                ->where('status', 'normal')
                ->whereNull('deletetime')
                ->count();
        };
    }

    public function assertDeletable($contentType, array $ids)
    {
        if (!in_array($contentType, ['product', 'article', 'case', 'page'], true)) {
            throw new \InvalidArgumentException('不支持的业务内容类型：' . $contentType);
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (!$ids) {
            throw new \InvalidArgumentException('请选择需要删除的内容');
        }
        $count = (int)call_user_func($this->counter, $contentType, $ids);
        if ($count > 0) {
            throw new \RuntimeException('所选内容仍被页面功能块引用，请先解除页面功能块引用；如只需停止展示，请使用下架操作');
        }
    }
}

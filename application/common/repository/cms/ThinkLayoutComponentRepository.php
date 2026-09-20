<?php
namespace app\common\repository\cms;
use app\common\model\cms\LayoutComponent;
class ThinkLayoutComponentRepository implements LayoutComponentRepositoryInterface
{
    public function publishedMap($terminal)
    {
        $field = $terminal === 'mobile' ? 'mobile_visible' : 'pc_visible';
        $rows = LayoutComponent::where('status', 'normal')->where($field, 1)
            ->where('device', 'in', ['all', $terminal])->order('id asc')->select();
        $map = [];
        foreach ($rows ?: [] as $row) {
            $item = $row->toArray();
            $config = json_decode(isset($item['config_json']) ? $item['config_json'] : '', true);
            $item['config'] = is_array($config) ? $config : [];
            $map[$item['component_key']] = $item;
        }
        return $map;
    }
}

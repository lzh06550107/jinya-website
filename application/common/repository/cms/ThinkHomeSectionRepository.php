<?php
namespace app\common\repository\cms;
use app\common\model\cms\HomeSection;
class ThinkHomeSectionRepository extends RepositorySupport implements HomeSectionRepositoryInterface
{
    public function publishedMap($terminal)
    {
        $field = $terminal === 'mobile' ? 'mobile_visible' : 'pc_visible';
        $rows = HomeSection::where('status', 'normal')->where($field, 1)->order('weigh desc,id asc')->select();
        $map = [];
        foreach ($this->rows($rows) as $row) {
            $config = json_decode(isset($row['config_json']) ? $row['config_json'] : '', true);
            $config = is_array($config) ? $config : [];
            $row['config'] = $this->normalizeConfig($config);
            $row['resolved_title'] = $terminal === 'mobile' && !empty($row['mobile_title']) ? $row['mobile_title'] : $row['title'];
            $row['resolved_subtitle'] = $terminal === 'mobile' && !empty($row['mobile_subtitle']) ? $row['mobile_subtitle'] : $row['subtitle'];
            $row['resolved_description'] = $terminal === 'mobile' && !empty($row['mobile_description']) ? $row['mobile_description'] : (isset($row['description']) ? $row['description'] : '');
            $row['resolved_content'] = $terminal === 'mobile' && !empty($row['mobile_content']) ? $row['mobile_content'] : (isset($row['content']) ? $row['content'] : '');
            $row['resolved_background'] = $terminal === 'mobile' && !empty($row['mobile_background_image']) ? $row['mobile_background_image'] : (isset($row['background_image']) ? $row['background_image'] : '');
            $row['display_count'] = (int)($terminal === 'mobile' ? $row['mobile_display_count'] : $row['pc_display_count']);
            $map[$row['section_key']] = $row;
        }
        return $map;
    }

    private function normalizeConfig(array $config)
    {
        if (!array_key_exists('metrics', $config) && isset($config['manager_value'])) {
            $config['metrics'] = [
                ['value' => $config['manager_value'], 'unit' => '', 'text' => isset($config['manager_text']) ? $config['manager_text'] : '', 'icon' => isset($config['manager_icon']) ? $config['manager_icon'] : ''],
                ['value' => isset($config['team_value']) ? $config['team_value'] : '', 'unit' => isset($config['team_unit']) ? $config['team_unit'] : '', 'text' => isset($config['team_text']) ? $config['team_text'] : '', 'icon' => isset($config['team_icon']) ? $config['team_icon'] : ''],
                ['value' => isset($config['warranty_value']) ? $config['warranty_value'] : '', 'unit' => isset($config['warranty_unit']) ? $config['warranty_unit'] : '', 'text' => isset($config['warranty_text']) ? $config['warranty_text'] : '', 'icon' => isset($config['warranty_icon']) ? $config['warranty_icon'] : ''],
                ['value' => isset($config['response_value']) ? $config['response_value'] : '', 'unit' => isset($config['response_unit']) ? $config['response_unit'] : '', 'text' => isset($config['response_text']) ? $config['response_text'] : '', 'icon' => isset($config['response_icon']) ? $config['response_icon'] : ''],
            ];
        }
        if (!array_key_exists('items', $config) && isset($config['title1'])) {
            $config['items'] = [];
            for ($index = 1; $index <= 12; $index++) {
                $titleKey = 'title' . $index;
                if (empty($config[$titleKey])) {
                    continue;
                }
                $config['items'][] = [
                    'title' => $config[$titleKey],
                    'text' => isset($config['text' . $index]) ? $config['text' . $index] : '',
                    'image' => isset($config['image' . $index]) ? $config['image' . $index] : '',
                    'icon' => isset($config['icon' . $index]) ? $config['icon' . $index] : '',
                    'url' => isset($config['url' . $index]) ? $config['url' . $index] : '',
                ];
            }
        }
        return $config;
    }
}

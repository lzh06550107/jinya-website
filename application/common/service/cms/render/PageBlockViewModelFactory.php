<?php
namespace app\common\service\cms\render;
if (!class_exists('app\\common\\service\\cms\\MarkdownRenderer', false)) { require_once dirname(__DIR__) . '/MarkdownRenderer.php'; }
use app\common\service\cms\MarkdownRenderer;
class PageBlockViewModelFactory
{
    private $allowed = ['text','rich_text','image_text','contact_info','timeline','gallery','honor_list','patent_list','video_list','map','form_intro','label_section','bags_section','boxes_section','about_section','contact_section'];
    public function map(array $rows, $terminal = 'pc')
    {
        $out = [];
        foreach ($rows as $row) {
            $type = isset($row['block_type']) ? $row['block_type'] : 'text';
            if (!in_array($type, $this->allowed, true)) continue;
            $extra = isset($row['extra']) && is_array($row['extra']) ? $row['extra'] : [];
            $blockKey = isset($row['block_key']) ? (string)$row['block_key'] : '';
            if ($type === 'label_section' || $type === 'bags_section' || $type === 'boxes_section' || $type === 'about_section' || $type === 'contact_section') {
                $extra = $this->normalizeLabelExtra($extra, $terminal, $blockKey);
            }
            $extra = $this->renderExtraMarkdown($extra, $terminal);
            $content = isset($row['content']) ? $row['content'] : '';
            if ($terminal === 'mobile' && isset($extra['mobile_content']) && trim((string)$extra['mobile_content']) !== '') $content = $extra['mobile_content'];
            $linkText = isset($row['link_text']) ? trim((string)$row['link_text']) : '';
            $linkUrl = isset($row['link_url']) ? trim((string)$row['link_url']) : '';
            if ($blockKey === 'bags_cases') {
                if ($linkText === '') $linkText = '查看更多产品';
                if ($linkUrl === '') $linkUrl = $terminal === 'mobile' ? '/mobile/products?category=no-plate-packaging' : '/products?category=no-plate-packaging';
                if ($terminal === 'mobile' && $linkUrl === '/products?category=no-plate-packaging') {
                    $linkUrl = '/mobile/products?category=no-plate-packaging';
                }
            }
            $item = [
                'key' => isset($row['block_key']) ? $row['block_key'] : '',
                'type' => $type,
                'title' => isset($row['title']) ? $row['title'] : '',
                'subtitle' => isset($row['subtitle']) ? $row['subtitle'] : '',
                'content_html' => MarkdownRenderer::render($content),
                'content_inline_html' => MarkdownRenderer::renderInline($content),
                'image' => isset($row['resolved_image']) ? $row['resolved_image'] : '',
                'link_text' => $linkText,
                'link_url' => $linkUrl,
                'extra' => $extra,
            ];
            $out[$item['key']] = $item;
        }
        return $out;
    }
    private function normalizeLabelExtra(array $extra, $terminal, $blockKey = '')
    {
        $items = isset($extra['items']) && is_array($extra['items']) ? $extra['items'] : [];
        $visibleField = $terminal === 'mobile' ? 'mobile_visible' : 'pc_visible';
        $normalized = [];
        foreach ($items as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            if (array_key_exists($visibleField, $entry) && (int)$entry[$visibleField] === 0) {
                continue;
            }
            if ($terminal === 'mobile') {
                foreach (['title','text','image','image_top','image_bottom','subtitle','badge','url'] as $field) {
                    $mobileField = 'mobile_' . $field;
                    if (isset($entry[$mobileField]) && trim((string)$entry[$mobileField]) !== '') {
                        $entry[$field] = $entry[$mobileField];
                    }
                }
            }
            foreach (['group','badge'] as $field) {
                if (!array_key_exists($field, $entry) || is_array($entry[$field]) || is_object($entry[$field])) {
                    $entry[$field] = '';
                } else {
                    $entry[$field] = trim((string)$entry[$field]);
                }
            }
            $entry['icon_kind'] = 'legacy';
            $entry['icon_value'] = '';
            $badge = $entry['badge'];
            if ($badge !== '' && preg_match('/^fa\s+fa-[a-z0-9-]+$/i', $badge)) {
                $entry['icon_kind'] = 'font';
                $entry['icon_value'] = strtolower(preg_replace('/\s+/', ' ', $badge));
            } elseif ($badge !== '' && preg_match('#^(?:https?://|//|/uploads/|uploads/)#i', $badge)) {
                $entry['icon_kind'] = 'image';
                $entry['icon_value'] = $badge;
            }
            foreach (['text','subtitle','badge'] as $field) {
                $value = isset($entry[$field]) ? trim((string)$entry[$field]) : '';
                $entry[$field . '_lines'] = $value === '' ? [] : preg_split('/\r\n|\r|\n/', $value);
            }
            $entry['lines'] = isset($entry['text_lines']) ? $entry['text_lines'] : [];
            $normalized[] = $entry;
        }
        $extra['items'] = $normalized;
        if (isset($extra['tabs']) && trim((string)$extra['tabs']) !== '') {
            $extra['tabs_items'] = [];
            $allTab = null;
            foreach (preg_split('/\r\n|\r|\n/', (string)$extra['tabs']) as $line) {
                $parts = array_map('trim', explode('|', $line, 2));
                if ($parts[0] === '') continue;
                $label = $parts[0];
                $value = isset($parts[1]) && $parts[1] !== '' ? $parts[1] : $label;
                if ($blockKey === 'bags_cases') {
                    $isAll = $value === 'all' || $label === '全部' || strpos($label, '查看更多') !== false || strpos($label, '查看全部') !== false;
                    if ($isAll) {
                        $allTab = ['label' => '全部', 'value' => 'all'];
                        continue;
                    }
                }
                $extra['tabs_items'][] = ['label' => $label, 'value' => $value];
            }
            if ($blockKey === 'bags_cases' && $allTab !== null) {
                array_unshift($extra['tabs_items'], $allTab);
            }
        }
        if ($blockKey === 'bags_cases') {
            $legacyGroups = [
                '无版卷膜' => 't1,t2,t3,t4',
                '无版拉链袋' => 't1,t2,t3,t4',
                '无版包装袋' => 't1,t2,t4',
            ];
            $productUrls = [
                '无版卷膜' => 'no-plate-roll-film',
                '无版拉链袋' => 'no-plate-zipper-bag',
                '无版包装袋' => 'no-plate-packaging-bag',
            ];
            foreach ($extra['items'] as &$caseItem) {
                $title = isset($caseItem['title']) ? trim((string)$caseItem['title']) : '';
                $group = isset($caseItem['group']) ? trim((string)$caseItem['group']) : '';
                if ($group === 'case' && isset($legacyGroups[$title])) {
                    $caseItem['group'] = $legacyGroups[$title];
                }
                $url = isset($caseItem['url']) ? trim((string)$caseItem['url']) : '';
                if (($url === '' || $url === '#') && isset($productUrls[$title])) {
                    $prefix = $terminal === 'mobile' ? '/mobile/product/' : '/product/';
                    $caseItem['url'] = $prefix . $productUrls[$title];
                }
            }
            unset($caseItem);
        }
        return $extra;
    }

    private function renderExtraMarkdown(array $extra, $terminal)
    {
        if (isset($extra['items']) && is_array($extra['items'])) {
            foreach ($extra['items'] as $index => $entry) {
                if (!is_array($entry)) continue;
                if ($terminal === 'mobile') {
                    foreach (['label','value','image','url','media_url'] as $field) {
                        $mobileField = 'mobile_' . $field;
                        if (isset($entry[$mobileField]) && trim((string)$entry[$mobileField]) !== '') $entry[$field] = $entry[$mobileField];
                    }
                    if (isset($entry['value']) && trim((string)$entry['value']) !== '') $entry['text'] = $entry['value'];
                }
                $entry = $this->normalizeMetricEntry($entry);
                foreach (['value','text','description','content'] as $field) {
                    if (!isset($entry[$field]) || trim((string)$entry[$field]) === '') continue;
                    $entry[$field . '_html'] = MarkdownRenderer::render($entry[$field]);
                    $entry[$field . '_inline_html'] = MarkdownRenderer::renderInline($entry[$field]);
                }
                $extra['items'][$index] = $entry;
            }
        }
        return $extra;
    }

    private function normalizeMetricEntry(array $entry)
    {
        $value = isset($entry['value']) ? trim((string)$entry['value']) : '';
        $unit = isset($entry['year']) ? trim((string)$entry['year']) : '';
        $metricValue = $value;
        $metricUnit = '';
        if ($unit !== '' && strpos($unit, '+') === 0) {
            $metricUnit = preg_replace('/\s+/u', '', $unit);
        } elseif ($value !== '' && preg_match('/^(.+?)\s*(\+\s*(?:项|㎡|m²|m2|年))$/u', $value, $matches)) {
            $metricValue = trim($matches[1]);
            $metricUnit = preg_replace('/\s+/u', '', $matches[2]);
        }
        $entry['metric_value'] = $metricValue;
        $entry['metric_unit'] = $metricUnit;
        return $entry;
    }

}

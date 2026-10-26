<?php

namespace app\common\service\cms;

use app\common\service\cms\render\CmsCacheInvalidator;
use think\Db;

class PageBlockRealSourceService
{
    private $cache;
    private $codec;
    private $homeConfigService;
    private $bannerHighlightCodec;

    public function __construct(CmsCacheInvalidator $cache = null, StructuredConfigCodec $codec = null, HomeSectionConfigService $homeConfigService = null, BannerHighlightCodec $bannerHighlightCodec = null)
    {
        $this->cache = $cache ?: new CmsCacheInvalidator();
        $this->codec = $codec ?: new StructuredConfigCodec();
        $this->homeConfigService = $homeConfigService ?: new HomeSectionConfigService();
        $this->bannerHighlightCodec = $bannerHighlightCodec ?: new BannerHighlightCodec();
    }

    public function loadBannerCollection($pageKey, $position)
    {
        return $this->rows(Db::name('cms_banner')
            ->where('page_key', (string)$pageKey)
            ->where('position', (string)$position)
            ->whereNull('deletetime')
            ->order('weigh desc,id asc')
            ->select());
    }

    public function saveBannerCollection($pageKey, $position, array $rows, $adminId = 0)
    {
        $pageKey = trim((string)$pageKey);
        $position = trim((string)$position);
        if ($pageKey === '' || $position === '') {
            throw new \InvalidArgumentException('Banner 数据范围无效');
        }

        Db::startTrans();
        try {
            $existingRows = $this->loadBannerCollection($pageKey, $position);
            $existing = [];
            foreach ($existingRows as $row) {
                $existing[(int)$row['id']] = $row;
            }

            $submittedIds = [];
            $now = time();
            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $id = isset($row['id']) ? (int)$row['id'] : 0;
                if ($id > 0 && !isset($existing[$id])) {
                    throw new \InvalidArgumentException('Banner 不存在或不属于当前页面：' . $id);
                }
                $fields = $this->normalizeBanner($row, $id > 0 && isset($existing[$id]) ? $existing[$id] : []);
                $fields['edited_by_admin'] = 1;
                $fields['updatetime'] = $now;
                if ($id > 0) {
                    Db::name('cms_banner')
                        ->where('id', $id)
                        ->where('page_key', $pageKey)
                        ->where('position', $position)
                        ->whereNull('deletetime')
                        ->update($fields);
                    $submittedIds[$id] = true;
                    continue;
                }
                if ($this->bannerRowEmpty($fields)) {
                    continue;
                }
                $fields['page_key'] = $pageKey;
                $fields['position'] = $position;
                $fields['source_key'] = '';
                $fields['createtime'] = $now;
                $newId = Db::name('cms_banner')->insertGetId($fields);
                if ($newId) {
                    $submittedIds[(int)$newId] = true;
                }
            }

            foreach ($existing as $id => $row) {
                if (isset($submittedIds[$id])) {
                    continue;
                }
                Db::name('cms_banner')
                    ->where('id', $id)
                    ->where('page_key', $pageKey)
                    ->where('position', $position)
                    ->whereNull('deletetime')
                    ->update(['deletetime' => $now, 'updatetime' => $now, 'edited_by_admin' => 1]);
            }

            Db::commit();
            $this->cache->invalidateLayout();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }
    }

    public function loadHomeSection($sectionKey, $contentType = '')
    {
        $sectionKey = trim((string)$sectionKey);
        $row = Db::name('cms_home_section')
            ->where('section_key', $sectionKey)
            ->whereNull('deletetime')
            ->find();
        if (!$row) {
            throw new \RuntimeException('首页模块不存在，请先执行 cms:install：' . $sectionKey);
        }
        $row = $this->row($row);
        $configJson = isset($row['config_json']) ? $row['config_json'] : '';
        $row['config'] = $this->codec->normalizeHome($this->codec->decode($configJson));
        $row['config_social_icon_1'] = $this->codec->scalar($configJson, 'social_icon_1', true);
        $row['config_social_text_1'] = $this->codec->scalar($configJson, 'social_text_1', true);
        $row['config_social_icon_2'] = $this->codec->scalar($configJson, 'social_icon_2', true);
        $row['config_social_text_2'] = $this->codec->scalar($configJson, 'social_text_2', true);
        $row['config_left_title'] = $this->codec->scalar($configJson, 'left_title', true);
        $row['config_right_title'] = $this->codec->scalar($configJson, 'right_title', true);
        $row['config_left_url'] = $this->codec->scalar($configJson, 'left_url', true);
        $row['config_right_url'] = $this->codec->scalar($configJson, 'right_url', true);
        $row['config_other_title'] = $this->codec->scalar($configJson, 'other_title', true);
        $row['config_other_summary'] = $this->codec->scalar($configJson, 'other_summary', true);
        $row['config_other_url'] = $this->codec->scalar($configJson, 'other_url', true);
        $row['config_video_url'] = $this->codec->scalar($configJson, 'video_url', true);
        $row['config_video_poster'] = $this->codec->scalar($configJson, 'video_poster', true);
        $row['config_metrics'] = isset($row['config']['metrics']) && is_array($row['config']['metrics']) ? $row['config']['metrics'] : [];
        $row['config_metrics_text'] = $this->codec->formatHomeRows($configJson, 'metrics', ['value', 'unit', 'text', 'icon', 'prefix']);
        $row['config_items'] = isset($row['config']['items']) && is_array($row['config']['items']) ? $row['config']['items'] : [];
        $row['config_items_text'] = $this->codec->formatHomeRows($configJson, 'items', ['title', 'text', 'image', 'icon', 'url', 'subtitle']);
        $row['config_media_items'] = isset($row['config']['media_items']) && is_array($row['config']['media_items']) ? $row['config']['media_items'] : [];
        $row['config_media_items_text'] = $this->codec->formatHomeRows($configJson, 'media_items', ['title', 'image', 'video_url', 'url']);
        $row['config_social_links_text'] = $this->codec->formatHomeRows($configJson, 'social_links', ['title', 'url', 'image']);
        if ($contentType !== '') {
            $row['pc_reference_ids'] = $this->loadHomeReferenceIds($sectionKey, $contentType, 'pc');
            $row['mobile_reference_ids'] = $this->loadHomeReferenceIds($sectionKey, $contentType, 'mobile');
        } else {
            $row['pc_reference_ids'] = [];
            $row['mobile_reference_ids'] = [];
        }
        return $row;
    }

    public function saveHomeSection($sectionKey, array $section, array $references, $adminId = 0, $contentType = '')
    {
        $sectionKey = trim((string)$sectionKey);
        $existing = Db::name('cms_home_section')
            ->where('section_key', $sectionKey)
            ->whereNull('deletetime')
            ->find();
        if (!$existing) {
            throw new \RuntimeException('首页模块不存在，请先执行 cms:install：' . $sectionKey);
        }

        $fields = $this->homeConfigService->preparePatch(
            $sectionKey,
            $section,
            isset($existing['config_json']) ? $existing['config_json'] : ''
        );
        $fields['edited_by_admin'] = 1;
        $fields['updatetime'] = time();

        Db::startTrans();
        try {
            Db::name('cms_home_section')
                ->where('section_key', $sectionKey)
                ->whereNull('deletetime')
                ->update($fields);

            if ($contentType !== '') {
                foreach (['pc', 'mobile'] as $terminal) {
                    $ids = isset($references[$terminal]) ? $references[$terminal] : [];
                    $this->replaceHomeReferences($sectionKey, $contentType, $terminal, $ids);
                }
            }
            Db::commit();
            $this->cache->invalidateLayout();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }
    }

    private function loadHomeReferenceIds($sectionKey, $contentType, $terminal)
    {
        $rows = Db::name('cms_home_section_reference')
            ->where('section_key', $sectionKey)
            ->where('content_type', $contentType)
            ->where('terminal', $terminal)
            ->where('status', 'normal')
            ->whereNull('deletetime')
            ->order('weigh desc,id asc')
            ->column('content_id');
        $ids = array_values(array_map('intval', is_array($rows) ? $rows : []));
        if ($ids || $terminal === 'all') {
            return $ids;
        }
        $fallback = Db::name('cms_home_section_reference')
            ->where('section_key', $sectionKey)
            ->where('content_type', $contentType)
            ->where('terminal', 'all')
            ->where('status', 'normal')
            ->whereNull('deletetime')
            ->order('weigh desc,id asc')
            ->column('content_id');
        return array_values(array_map('intval', is_array($fallback) ? $fallback : []));
    }

    private function replaceHomeReferences($sectionKey, $contentType, $terminal, $ids)
    {
        $ids = is_array($ids) ? $ids : explode(',', (string)$ids);
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        $now = time();
        $existingRows = $this->rows(Db::name('cms_home_section_reference')
            ->where('section_key', $sectionKey)
            ->where('content_type', $contentType)
            ->where('terminal', $terminal)
            ->select());
        $existing = [];
        foreach ($existingRows as $row) {
            $existing[(int)$row['content_id']] = $row;
        }
        Db::name('cms_home_section_reference')
            ->where('section_key', $sectionKey)
            ->where('content_type', $contentType)
            ->where('terminal', $terminal)
            ->whereNull('deletetime')
            ->update(['deletetime' => $now, 'updatetime' => $now]);

        $weight = count($ids) * 10;
        foreach ($ids as $id) {
            if (isset($existing[$id])) {
                Db::name('cms_home_section_reference')->where('id', (int)$existing[$id]['id'])->update([
                    'weigh' => $weight,
                    'status' => 'normal',
                    'updatetime' => $now,
                    'deletetime' => null,
                ]);
            } else {
                Db::name('cms_home_section_reference')->insert([
                    'section_key' => $sectionKey,
                    'content_type' => $contentType,
                    'content_id' => $id,
                    'terminal' => $terminal,
                    'weigh' => $weight,
                    'status' => 'normal',
                    'createtime' => $now,
                    'updatetime' => $now,
                    'deletetime' => null,
                ]);
            }
            $weight -= 10;
        }
    }

    private function normalizeBanner(array $row, array $existing = [])
    {
        $stringFields = [
            'title', 'mobile_title', 'subtitle', 'mobile_subtitle', 'description', 'mobile_description',
            'image', 'mobile_image', 'media_type', 'mobile_media_type', 'video_url', 'mobile_video_url',
            'overlay_image', 'link_url', 'mobile_link_url', 'button_text', 'status',
        ];
        $fields = [];
        foreach ($stringFields as $field) {
            if (array_key_exists($field, $row) && !is_array($row[$field])) {
                $fields[$field] = trim((string)$row[$field]);
            } else {
                $fields[$field] = isset($existing[$field]) && !is_array($existing[$field])
                    ? trim((string)$existing[$field])
                    : '';
            }
        }
        if (!in_array($fields['media_type'], ['image', 'video'], true)) {
            $fields['media_type'] = 'image';
        }
        if (!in_array($fields['mobile_media_type'], ['', 'image', 'video'], true)) {
            $fields['mobile_media_type'] = '';
        }
        $fields['highlights_json'] = $this->bannerHighlightCodec->normalizeJson(
            array_key_exists('highlights_json', $row)
                ? $row['highlights_json']
                : (isset($existing['highlights_json']) ? $existing['highlights_json'] : '')
        );
        $fields['status'] = $fields['status'] === 'hidden' ? 'hidden' : 'normal';
        $fields['pc_visible'] = array_key_exists('pc_visible', $row)
            ? (!empty($row['pc_visible']) ? 1 : 0)
            : (isset($existing['pc_visible']) ? (int)$existing['pc_visible'] : 1);
        $fields['mobile_visible'] = array_key_exists('mobile_visible', $row)
            ? (!empty($row['mobile_visible']) ? 1 : 0)
            : (isset($existing['mobile_visible']) ? (int)$existing['mobile_visible'] : 1);
        $fields['weigh'] = array_key_exists('weigh', $row)
            ? (int)$row['weigh']
            : (isset($existing['weigh']) ? (int)$existing['weigh'] : 0);
        $fields['start_time'] = $this->timestamp(
            array_key_exists('start_time', $row) ? $row['start_time'] : (isset($existing['start_time']) ? $existing['start_time'] : null)
        );
        $fields['end_time'] = $this->timestamp(
            array_key_exists('end_time', $row) ? $row['end_time'] : (isset($existing['end_time']) ? $existing['end_time'] : null)
        );
        return $fields;
    }

    private function timestamp($value)
    {
        if ($value === '' || $value === null) {
            return null;
        }
        if (is_numeric($value)) {
            return (int)$value;
        }
        $time = strtotime((string)$value);
        return $time === false ? null : $time;
    }

    private function bannerRowEmpty(array $fields)
    {
        return $fields['title'] === '' && $fields['image'] === '' && $fields['mobile_image'] === ''
            && $fields['video_url'] === '' && $fields['mobile_video_url'] === '';
    }

    private function rows($rows)
    {
        if (is_array($rows)) {
            return $rows;
        }
        if ($rows instanceof \think\Collection) {
            return $rows->toArray();
        }
        return [];
    }

    private function row($row)
    {
        if (is_array($row)) {
            return $row;
        }
        if (is_object($row) && method_exists($row, 'toArray')) {
            return $row->toArray();
        }
        return [];
    }
}

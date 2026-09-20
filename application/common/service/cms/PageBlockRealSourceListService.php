<?php

namespace app\common\service\cms;

use think\Db;

/**
 * Resolve the operational state shown by the fixed page-block list from the
 * real data source used by each editor. The cms_page_block row remains schema
 * metadata for real-source blocks and must not override HomeSection/Banner state.
 */
class PageBlockRealSourceListService
{
    public function decorate($pageKey, array $rows)
    {
        if (!$rows) {
            return $rows;
        }

        $pageKey = trim((string)$pageKey);
        foreach ($rows as $index => $row) {
            $rows[$index] = $this->row($row);
        }
        $registry = new PageBlockRealSourceEditorRegistry();
        $homeKeys = [];
        $bannerPositions = [];
        $modes = [];

        foreach ($rows as $index => $row) {
            $blockKey = isset($row['block_key']) ? (string)$row['block_key'] : '';
            $blockType = isset($row['block_type']) ? (string)$row['block_type'] : '';
            $realSource = $registry->resolve($pageKey, $blockKey, $blockType);
            $modes[$index] = $realSource;
            if (isset($realSource['mode']) && $realSource['mode'] === 'home_section') {
                $homeKeys[] = (string)$realSource['section_key'];
            } elseif (isset($realSource['mode']) && $realSource['mode'] === 'banner_collection') {
                $bannerPositions[] = (string)$realSource['position'];
            }
        }

        $homeMap = [];
        $homeKeys = array_values(array_unique(array_filter($homeKeys, 'strlen')));
        if ($homeKeys) {
            $homeRows = Db::name('cms_home_section')
                ->where('section_key', 'in', $homeKeys)
                ->whereNull('deletetime')
                ->field('section_key,pc_visible,mobile_visible,status')
                ->select();
            foreach ($this->rows($homeRows) as $homeRow) {
                $homeMap[(string)$homeRow['section_key']] = $homeRow;
            }
        }

        $bannerMap = [];
        $bannerPositions = array_values(array_unique(array_filter($bannerPositions, 'strlen')));
        if ($bannerPositions) {
            $bannerRows = Db::name('cms_banner')
                ->where('page_key', $pageKey)
                ->where('position', 'in', $bannerPositions)
                ->whereNull('deletetime')
                ->field('position,pc_visible,mobile_visible,status')
                ->select();
            foreach ($this->rows($bannerRows) as $bannerRow) {
                $position = (string)$bannerRow['position'];
                if (!isset($bannerMap[$position])) {
                    $bannerMap[$position] = [];
                }
                $bannerMap[$position][] = $bannerRow;
            }
        }

        foreach ($rows as $index => $row) {
            $realSource = isset($modes[$index]) ? $modes[$index] : ['mode' => 'generic'];
            if ($realSource['mode'] === 'home_section') {
                $key = (string)$realSource['section_key'];
                if (isset($homeMap[$key])) {
                    $rows[$index] = $this->applyHomeSectionState($row, $homeMap[$key]);
                }
            } elseif ($realSource['mode'] === 'banner_collection') {
                $position = (string)$realSource['position'];
                $rows[$index] = $this->applyBannerCollectionState($row, isset($bannerMap[$position]) ? $bannerMap[$position] : []);
            }
        }

        return $rows;
    }

    public function applyHomeSectionState($row, array $section)
    {
        $row = $this->row($row);
        foreach (['pc_visible', 'mobile_visible', 'status'] as $field) {
            if (array_key_exists($field, $section)) {
                $row[$field] = $section[$field];
            }
        }
        return $row;
    }

    public function applyBannerCollectionState($row, array $banners)
    {
        $row = $this->row($row);
        $enabled = false;
        $pcVisible = false;
        $mobileVisible = false;
        foreach ($banners as $banner) {
            $banner = $this->row($banner);
            if (!$banner || (isset($banner['status']) ? (string)$banner['status'] : 'normal') !== 'normal') {
                continue;
            }
            $enabled = true;
            $pcVisible = $pcVisible || !empty($banner['pc_visible']);
            $mobileVisible = $mobileVisible || !empty($banner['mobile_visible']);
        }
        $row['pc_visible'] = $pcVisible ? 1 : 0;
        $row['mobile_visible'] = $mobileVisible ? 1 : 0;
        $row['status'] = $enabled ? 'normal' : 'hidden';
        return $row;
    }

    private function row($row)
    {
        if (is_object($row) && method_exists($row, 'toArray')) {
            $row = $row->toArray();
        }
        return is_array($row) ? $row : [];
    }

    private function rows($rows)
    {
        if ($rows instanceof \think\Collection) {
            $rows = $rows->toArray();
        } elseif (is_object($rows) && method_exists($rows, 'toArray')) {
            $rows = $rows->toArray();
        }
        return is_array($rows) ? $rows : [];
    }
}

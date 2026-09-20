<?php

namespace app\common\service\cms;

class PageBlockRealSourceEditorRegistry
{
    private $homeEditorSchema;

    public function __construct(HomeSectionEditorSchema $homeEditorSchema = null)
    {
        $this->homeEditorSchema = $homeEditorSchema ?: new HomeSectionEditorSchema();
    }

    public function resolve($pageKey, $blockKey, $blockType)
    {
        $pageKey = trim((string)$pageKey);
        $blockKey = trim((string)$blockKey);
        $blockType = trim((string)$blockType);

        if ($pageKey === 'home' && $blockKey === 'hero') {
            return [
                'mode' => 'banner_collection',
                'page_key' => 'home',
                'position' => 'hero',
            ];
        }

        if ($pageKey === 'home' && $this->homeEditorSchema->has($blockKey)) {
            return [
                'mode' => 'home_section',
                'section_key' => $blockKey,
                'content_type' => $this->homeEditorSchema->referenceType($blockKey),
            ];
        }

        if ($blockType === 'banner') {
            return [
                'mode' => 'banner_collection',
                'page_key' => $pageKey,
                'position' => 'channel',
            ];
        }

        return ['mode' => 'generic'];
    }
}

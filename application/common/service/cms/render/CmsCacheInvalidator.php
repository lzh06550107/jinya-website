<?php

namespace app\common\service\cms\render;

use think\Cache;

class CmsCacheInvalidator
{
    public function invalidate(array $tags)
    {
        foreach (array_unique($tags) as $tag) {
            $tag = trim((string)$tag);
            if ($tag === '') {
                continue;
            }
            Cache::rm('cms:' . $tag);
            Cache::rm('cms:' . $tag . ':pc');
            Cache::rm('cms:' . $tag . ':mobile');
        }
    }

    public function invalidateContent($type, $id = 0, $slug = '')
    {
        $mutation = new \app\common\service\cms\AdminContentMutationService();
        $this->invalidate($mutation->cacheTags($type, $id, $slug));
    }

    public function invalidateLayout()
    {
        $this->invalidate(['site', 'navigation', 'layout', 'home', 'sitemap', 'search']);
    }
}

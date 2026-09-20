<?php

namespace app\common\service\cms;

class AdminContentMutationService
{
    public function markEdited(array $params)
    {
        $params['edited_by_admin'] = 1;
        return $params;
    }

    public function cacheTags($type, $id = 0, $slug = '')
    {
        $tags = ['home', 'navigation', 'sitemap', 'search'];
        if ($type !== '') {
            $tags[] = $type . ':list';
            if ((int)$id > 0) {
                $tags[] = $type . ':id:' . (int)$id;
            }
            if (trim((string)$slug) !== '') {
                $tags[] = $type . ':slug:' . trim((string)$slug);
            }
        }
        return array_values(array_unique($tags));
    }
}

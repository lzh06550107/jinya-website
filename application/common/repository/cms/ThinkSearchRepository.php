<?php
namespace app\common\repository\cms;

use app\common\model\cms\Product;
use app\common\model\cms\Article;
use app\common\model\cms\Page;

class ThinkSearchRepository extends RepositorySupport implements SearchRepositoryInterface
{
    private $pageSlugs = ['label', 'bags', 'boxes', 'about', 'contact'];

    public function searchPublished($keyword, $page, $pageSize, array $types)
    {
        $keyword = trim((string)$keyword);
        $all = [];
        $models = ['product' => Product::class, 'article' => Article::class, 'page' => Page::class];
        foreach ($types as $type) {
            if (!isset($models[$type])) {
                continue;
            }
            $class = $models[$type];
            $query = $class::where('status', 'published')
                ->where('publish_time', '<=', time())
                ->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', '%' . $keyword . '%')->whereOr('summary', 'like', '%' . $keyword . '%');
                });
            if ($type === 'page') {
                $query->where('slug', 'in', $this->pageSlugs);
            }
            $rows = $query->order('publish_time desc,id desc')->select();
            foreach ($this->rows($rows) as $row) {
                $row['_type'] = $type;
                $all[] = $row;
            }
        }
        usort($all, function ($a, $b) {
            return ((int)$b['publish_time'] <=> (int)$a['publish_time']) ?: ((int)$b['id'] <=> (int)$a['id']);
        });
        $total = count($all);
        $page = max(1, (int)$page);
        $pageSize = max(1, (int)$pageSize);
        $offset = ($page - 1) * $pageSize;
        return ['items' => array_slice($all, $offset, $pageSize), 'total' => $total, 'page' => $page, 'page_size' => $pageSize];
    }
}

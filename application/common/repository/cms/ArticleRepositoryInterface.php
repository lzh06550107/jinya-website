<?php
namespace app\common\repository\cms;
interface ArticleRepositoryInterface { public function findById($id);
 public function publishedByIds(array $ids); public function paginatePublished($categoryId,$page,$pageSize,$order); public function findPublishedBySlug($slug); public function previousNext($articleId,$publishTime); public function related($articleId,$categoryId,$limit); public function latest($excludeId,$limit);
}

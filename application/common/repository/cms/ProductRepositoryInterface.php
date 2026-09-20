<?php
namespace app\common\repository\cms;
interface ProductRepositoryInterface { public function findById($id);
 public function publishedByIds(array $ids); public function paginatePublished($categoryId,$page,$pageSize,$order); public function findPublishedBySlug($slug); public function previousNext($id,$publishTime); public function related($id,$categoryId,$limit);
}

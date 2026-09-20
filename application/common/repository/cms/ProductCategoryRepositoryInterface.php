<?php
namespace app\common\repository\cms;
interface ProductCategoryRepositoryInterface { public function publishedTree(); public function findPublishedBySlug($slug); }

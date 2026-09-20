<?php
namespace app\common\repository\cms;
interface ArticleCategoryRepositoryInterface { public function publishedTree(); public function findPublishedBySlug($slug); }

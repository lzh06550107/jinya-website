<?php
namespace app\common\repository\cms;
interface SearchRepositoryInterface { public function searchPublished($keyword,$page,$pageSize,array $types); }

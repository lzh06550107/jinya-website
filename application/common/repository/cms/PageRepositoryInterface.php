<?php
namespace app\common\repository\cms;
interface PageRepositoryInterface { public function findById($id); public function findPublishedBySlug($slug); public function publishedAll(); }

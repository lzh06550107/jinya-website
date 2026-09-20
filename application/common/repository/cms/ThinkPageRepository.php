<?php
namespace app\common\repository\cms;
use app\common\model\cms\Page;
class ThinkPageRepository extends RepositorySupport implements PageRepositoryInterface
{ private function base(){return Page::where('status','published')->where('publish_time','<=',time());} public function findById($id){return$this->row(Page::get((int)$id));} public function findPublishedBySlug($slug){return$this->row($this->base()->where('slug',$slug)->find());} public function publishedAll(){return$this->rows($this->base()->order('weigh desc,id asc')->select());} }

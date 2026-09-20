<?php
namespace app\common\service\cms\render;
class ContentNotFoundException extends \RuntimeException
{
    private $contentType;
    private $slug;
    public function __construct($contentType, $slug) { parent::__construct($contentType . ' not found: ' . $slug); $this->contentType=$contentType; $this->slug=$slug; }
    public function contentType() { return $this->contentType; }
    public function slug() { return $this->slug; }
}

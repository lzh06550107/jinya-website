<?php
namespace app\common\service\cms\render;

final class RenderContext
{
    private $terminal;
    private $page;

    private function __construct($terminal, $page)
    {
        if (!in_array($terminal, ['pc', 'mobile'], true)) {
            throw new \InvalidArgumentException('terminal must be pc or mobile');
        }
        $this->terminal = $terminal;
        $this->page = max(1, (int)$page);
    }

    public static function pc($page = 1) { return new self('pc', $page); }
    public static function mobile($page = 1) { return new self('mobile', $page); }
    public function terminal() { return $this->terminal; }
    public function page() { return $this->page; }
    public function isPc() { return $this->terminal === 'pc'; }
    public function isMobile() { return $this->terminal === 'mobile'; }
}

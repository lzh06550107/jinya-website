<?php

namespace app\index\controller;

class Error extends CmsBase
{
    public function index()
    {
        $this->assignPageConfig('error.404');
        return $this->redirectOr404();
    }

    public function _empty($name)
    {
        return $this->redirectOr404();
    }
}

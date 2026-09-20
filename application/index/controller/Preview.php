<?php

namespace app\index\controller;

use app\common\service\cms\render\ContentNotFoundException;
use think\Cache;

class Preview extends CmsBase
{
    public function show($token = '')
    {
        $data=Cache::get('cms:preview:'.trim((string)$token));
        if(!$data||empty($data['type'])||empty($data['id']))$this->error('预览链接已失效，请返回后台重新打开');
        try{$result=$this->renderServices()->preview()->render($data['type'],(int)$data['id'],$this->renderContext());}
        catch(ContentNotFoundException $e){$this->error('预览内容不存在或类型不受支持');}
        $this->setPcTheme($result['pc_theme'],$result['section'],$result['section']==='news'||$result['section']==='cases'?'body-color-p102':'body-color');
        $this->assignPageViewModel($result['view_model']);
        $this->applyChannelFromViewModel($result['view_model'],'内容预览','');
        $this->view->assign('isPreview',true);
        return $this->view->fetch($result['template']);
    }
}

<?php

namespace app\mobile\controller;

use app\common\service\cms\InquiryService;

class Inquiry extends CmsBase
{
    public function submit()
    {
        if (!$this->request->isPost()) {
            $this->error('请求方式错误');
        }
        $this->token();
        $data = $this->request->post();
        $data['source_url'] = $this->request->server('HTTP_REFERER', '');
        $data['source_title'] = isset($data['source_title']) ? $data['source_title'] : '';
        $data['utm_source'] = isset($data['utm_source']) ? $data['utm_source'] : $this->request->get('utm_source', '');
        $data['utm_medium'] = isset($data['utm_medium']) ? $data['utm_medium'] : $this->request->get('utm_medium', '');
        $data['utm_campaign'] = isset($data['utm_campaign']) ? $data['utm_campaign'] : $this->request->get('utm_campaign', '');
        $data['ip'] = $this->request->ip();
        $data['user_agent'] = substr($this->request->server('HTTP_USER_AGENT', ''), 0, 500);
        try {
            (new InquiryService())->create($data);
            $this->success('提交成功，我们会尽快联系您', $this->mobilePath('/'), ['__token__' => $this->request->token()]);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), '', ['__token__' => $this->request->token()]);
        }
    }
}

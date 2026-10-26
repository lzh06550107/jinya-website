<?php

namespace app\mobile\controller;

use app\common\service\cms\InquiryService;

class Inquiry extends CmsBase
{
    public function submit()
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误', 'data' => []]);
        }

        // Keep FastAdmin's CSRF validation, but return a stable JSON envelope for
        // every business/database outcome below.
        $this->token();

        $data = $this->request->post();
        $data['source_url'] = $this->request->server('HTTP_REFERER', '');
        $data['source_title'] = isset($data['source_title']) ? $data['source_title'] : '';
        $data['utm_source'] = isset($data['utm_source']) ? $data['utm_source'] : $this->request->get('utm_source', '');
        $data['utm_medium'] = isset($data['utm_medium']) ? $data['utm_medium'] : $this->request->get('utm_medium', '');
        $data['utm_campaign'] = isset($data['utm_campaign']) ? $data['utm_campaign'] : $this->request->get('utm_campaign', '');
        $data['ip'] = $this->request->ip();
        $data['user_agent'] = $this->request->server('HTTP_USER_AGENT', '');

        try {
            $inquiry = (new InquiryService())->create($data);
            return json([
                'code' => 1,
                'msg' => '提交成功，我们会尽快联系您',
                'data' => [
                    'id' => (int)$inquiry['id'],
                    '__token__' => $this->request->token(),
                ],
            ]);
        } catch (\Throwable $e) {
            $message = trim((string)$e->getMessage());
            return json([
                'code' => 0,
                'msg' => $message !== '' ? $message : '线索保存失败，请稍后重试',
                'data' => [
                    '__token__' => $this->request->token(),
                ],
            ]);
        }
    }
}

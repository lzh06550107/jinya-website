<?php

namespace app\common\service\cms;

use app\common\model\cms\Inquiry;
use app\common\model\cms\InquiryFollowup;
use think\Cache;
use think\Db;

class InquiryService
{
    public function create(array $data)
    {
        $name = trim(isset($data['name']) ? $data['name'] : '');
        $mobile = trim(isset($data['mobile']) ? $data['mobile'] : '');
        $company = trim(isset($data['company']) ? $data['company'] : '');
        $content = trim(isset($data['content']) ? $data['content'] : '');
        if ($name === '' || !preg_match('/^1\d{10}$/', $mobile)) {
            throw new \InvalidArgumentException('请填写姓名和正确的手机号');
        }
        if ($content === '') {
            throw new \InvalidArgumentException('请填写咨询需求');
        }
        if ($this->fieldLength($name) > 100) {
            throw new \InvalidArgumentException('姓名不能超过100个字符');
        }
        if ($this->fieldLength($company) > 200) {
            throw new \InvalidArgumentException('公司名称不能超过200个字符');
        }
        if ($this->fieldLength($content) > 2000) {
            throw new \InvalidArgumentException('咨询需求不能超过2000个字符');
        }
        $ip = isset($data['ip']) ? $data['ip'] : '';
        $rateKey = 'cms:inquiry:' . md5($ip . '|' . $mobile);
        if (Cache::get($rateKey)) {
            throw new \RuntimeException('提交过于频繁，请稍后再试');
        }
        $inquiry = new Inquiry();
        $data['name'] = $name;
        $data['mobile'] = $mobile;
        $data['company'] = $company;
        $data['content'] = $content;
        $data['status'] = 'new';
        $inquiry->allowField(true)->save($data);
        Cache::set($rateKey, 1, 60);
        return $inquiry;
    }

    protected function fieldLength($value)
    {
        return function_exists('mb_strlen') ? mb_strlen((string)$value, 'UTF-8') : strlen((string)$value);
    }

    public function assign($id, $adminId, $operatorId)
    {
        $inquiry = Inquiry::get($id);
        if (!$inquiry) {
            throw new \InvalidArgumentException('咨询线索不存在');
        }
        $adminId = (int)$adminId;
        $admin = \app\admin\model\Admin::where('id', $adminId)->where('status', 'normal')->find();
        if (!$admin) {
            throw new \InvalidArgumentException('请选择有效的负责人');
        }
        $inquiry->save(['assigned_admin_id' => $adminId]);
        $this->addSystemFollowup($id, $operatorId, '分配负责人为管理员 #' . (int)$adminId);
        return $inquiry;
    }

    public function follow($id, $adminId, array $data)
    {
        $inquiry = Inquiry::get($id);
        if (!$inquiry) {
            throw new \InvalidArgumentException('咨询线索不存在');
        }
        $content = trim(isset($data['content']) ? $data['content'] : '');
        if ($content === '') {
            throw new \InvalidArgumentException('请填写跟进内容');
        }
        Db::startTrans();
        try {
            $followup = new InquiryFollowup();
            $followup->save([
                'inquiry_id' => $id,
                'admin_id' => $adminId,
                'follow_type' => isset($data['follow_type']) ? $data['follow_type'] : 'phone',
                'content' => $content,
                'next_follow_time' => !empty($data['next_follow_time']) ? strtotime($data['next_follow_time']) : null,
                'attachment' => isset($data['attachment']) ? $data['attachment'] : '',
            ]);
            $update = [
                'last_follow_time' => time(),
                'next_follow_time' => !empty($data['next_follow_time']) ? strtotime($data['next_follow_time']) : null,
            ];
            if (!empty($data['status'])) {
                $update['status'] = $data['status'];
            }
            $inquiry->save($update);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
        return $followup;
    }

    public function changeStatus($id, $status, $adminId)
    {
        $inquiry = Inquiry::get($id);
        if (!$inquiry) {
            throw new \InvalidArgumentException('咨询线索不存在');
        }
        $list = $inquiry->getStatusList();
        if (!isset($list[$status])) {
            throw new \InvalidArgumentException('无效的客户状态');
        }
        $inquiry->save(['status' => $status]);
        $this->addSystemFollowup($id, $adminId, '客户状态变更为：' . $list[$status]);
        return $inquiry;
    }

    protected function addSystemFollowup($id, $adminId, $content)
    {
        $followup = new InquiryFollowup();
        $followup->save([
            'inquiry_id' => $id,
            'admin_id' => $adminId,
            'follow_type' => 'system',
            'content' => $content,
        ]);
    }
}

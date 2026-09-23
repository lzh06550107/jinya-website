<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;
use app\common\model\cms\Inquiry as InquiryModel;
use app\common\service\cms\InquiryService;
use think\Response;

class Inquiry extends Backend
{
    // allocate 使用旧的 cms/inquiry/assign 权限节点，避免升级后普通角色丢失权限。
    protected $noNeedRight = ['allocate'];
    protected $model = null;
    protected $searchFields = 'id,name,mobile,company,content';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new InquiryModel();
        $this->view->assign('statusList', $this->model->getStatusList());
        $this->assignconfig('statusList', $this->model->getStatusList());
        $this->assignconfig('canViewMobile', $this->auth->check('cms/inquiry/view_mobile'));
    }

    public function index()
    {
        $this->request->filter(['strip_tags', 'trim']);
        if (!$this->request->isAjax()) {
            return $this->view->fetch();
        }
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $query = InquiryModel::with('assignedAdmin')->where($where);
        $this->applyDataScope($query);
        $list = $query->order($sort, $order)->paginate($limit);
        $rows = $list->items();
        if (!$this->auth->check('cms/inquiry/view_mobile')) {
            foreach ($rows as &$row) {
                $row['mobile'] = $row['mobile_masked'];
            }
            unset($row);
        }
        return json(['total' => $list->total(), 'rows' => $rows]);
    }

    public function detail($ids = null)
    {
        $row = $this->findAccessible($ids, ['assignedAdmin']);
        if (!$this->auth->check('cms/inquiry/view_mobile')) {
            $row['mobile'] = $row['mobile_masked'];
        }
        $this->view->assign('row', $row);
        return $this->view->fetch();
    }

    public function allocate($ids = null)
    {
        if (!$this->auth->check('cms/inquiry/assign')) {
            $this->error('你没有分配客户负责人的权限');
        }
        $row = $this->findAccessible($ids, ['assignedAdmin']);
        if (!$this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch('cms/inquiry/assign');
        }
        $adminId = (int)$this->request->post('admin_id');
        try {
            (new InquiryService())->assign((int)$row['id'], $adminId, (int)$this->auth->id);
            $this->success('分配成功');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function follow($ids = null)
    {
        $row = $this->findAccessible($ids);
        if (!$this->request->isPost()) {
            $this->view->assign('row', $row);
            $this->view->assign('followTypeList', [
                'phone' => '电话',
                'wechat' => '微信',
                'email' => '邮件',
                'meeting' => '面谈',
                'other' => '其他',
            ]);
            return $this->view->fetch();
        }
        try {
            (new InquiryService())->follow((int)$row['id'], (int)$this->auth->id, $this->request->post());
            $this->success('跟进记录已保存');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function changeStatus($ids = null)
    {
        $row = $this->findAccessible($ids);
        if (!$this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        try {
            (new InquiryService())->changeStatus((int)$row['id'], $this->request->post('status'), (int)$this->auth->id);
            $this->success('状态已更新');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function viewMobile($ids = null)
    {
        $row = $this->findAccessible($ids);
        $this->success('', '', ['mobile' => $row['mobile']]);
    }

    public function export()
    {
        $query = InquiryModel::with('assignedAdmin')->order('id desc');
        $this->applyDataScope($query);
        $rows = $query->limit(10000)->select();
        $showMobile = $this->auth->check('cms/inquiry/view_mobile');
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, ['ID', '姓名', '手机号', '公司', '地区', '咨询内容', '状态', '负责人', '下次跟进', '提交时间']);
        foreach ($rows as $row) {
            fputcsv($stream, [
                $row['id'],
                $row['name'],
                $showMobile ? $row['mobile'] : $row['mobile_masked'],
                $row['company'],
                trim($row['province'] . ' ' . $row['city']),
                $row['content'],
                $row['status_text'],
                isset($row['assigned_admin']['nickname']) ? $row['assigned_admin']['nickname'] : '',
                $row['next_follow_time'] ? date('Y-m-d H:i:s', $row['next_follow_time']) : '',
                $row['createtime'] ? date('Y-m-d H:i:s', $row['createtime']) : '',
            ]);
        }
        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);
        return Response::create($content, 'html', 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="cms-inquiries-' . date('Ymd-His') . '.csv"',
        ]);
    }

    public function del($ids = '')
    {
        if (!$this->request->isPost()) {
            $this->error('请求方式错误');
        }
        $ids = $ids ?: $this->request->request('ids');
        $idList = array_values(array_filter(array_map('intval', explode(',', (string)$ids))));
        if (!$idList) {
            $this->error('请选择需要删除的记录');
        }
        $query = InquiryModel::where('id', 'in', $idList);
        $this->applyDataScope($query);
        $rows = $query->select();
        if (count($rows) !== count($idList)) {
            $this->error('包含无权操作的客户记录');
        }
        foreach ($rows as $row) {
            $row->delete();
        }
        $this->success('删除成功');
    }

    protected function applyDataScope($query)
    {
        if (!$this->auth->isSuperAdmin() && !$this->auth->check('cms/inquiry/view_all')) {
            $query->where('assigned_admin_id', (int)$this->auth->id);
        }
        return $query;
    }

    protected function findAccessible($id, array $with = [])
    {
        // assignedAdmin is eager-loaded with a LEFT JOIN. Once that join exists,
        // an unqualified "id" is ambiguous because both inquiry and admin own it.
        // Always target the inquiry table explicitly for the requested lead.
        $query = InquiryModel::where('inquiry.id', (int)$id);
        if ($with) {
            $query->with($with);
        }
        $this->applyDataScope($query);
        $row = $query->find();
        if (!$row) {
            $this->error('咨询线索不存在或无权访问');
        }
        return $row;
    }
}

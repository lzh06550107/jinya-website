<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;
use app\common\model\Config as ConfigModel;
use app\common\service\cms\InstallerService;
use app\common\service\cms\render\CmsCacheInvalidator;
use think\Cache;
use think\Exception;

/**
 * 企业网站配置
 */
class SiteConfig extends Backend
{
    protected $noNeedRight = [];

    protected function definitions()
    {
        return \app\common\service\cms\SiteConfigDefinitionRegistry::all();
    }

    public function index()
    {
        $values = [];
        foreach ($this->definitions() as $name => $definition) {
            $row = ConfigModel::getByName($name);
            $values[$name] = $row ? $row['value'] : $definition['default'];
        }
        $this->view->assign('values', $values);
        return $this->view->fetch();
    }

    public function edit($ids = null)
    {
        if (!$this->request->isPost()) {
            $this->error('请求方式错误');
        }
        $this->token();
        $params = $this->request->post('row/a', [], 'trim');
        if (!$params) {
            $this->error('配置内容不能为空');
        }

        try {
            foreach ($this->definitions() as $name => $definition) {
                if (!array_key_exists($name, $params)) {
                    continue;
                }
                $value = trim((string)$params[$name]);
                if ($definition['rule'] === 'required' && $value === '') {
                    throw new Exception($definition['title'] . '不能为空');
                }
                if ($definition['rule'] === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception($definition['title'] . '格式不正确');
                }

                $row = ConfigModel::getByName($name);
                if ($row) {
                    $row->save(['value' => $value]);
                } else {
                    ConfigModel::create([
                        'name' => $name,
                        'group' => 'basic',
                        'title' => $definition['title'],
                        'tip' => '',
                        'type' => $definition['type'],
                        'visible' => '',
                        'value' => $value,
                        'content' => '',
                        'rule' => $definition['rule'],
                        'extend' => '',
                        'setting' => '',
                    ]);
                }
            }

            (new InstallerService())->refreshSiteConfig();
            (new CmsCacheInvalidator())->invalidateLayout();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success('网站配置保存成功');
    }
}

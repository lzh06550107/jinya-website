<?php

namespace app\admin\controller\cms;

use app\common\model\cms\Page as PageModel;

/**
 * Content editor for the five retained structured pages.
 * Page creation/deletion/duplication is owned by the fixed PageSchema registry.
 */
class Page extends Content
{
    protected $modelClass = PageModel::class;
    protected $contentType = 'page';
    protected $currentSlugs = ['label', 'bags', 'boxes', 'about', 'contact'];

    protected function afterCmsInitialize()
    {
        $templates = [
            'label' => '不干胶/卷标',
            'bags' => '包装袋·无版印刷',
            'boxes' => '彩盒',
            'about' => '走进金亚',
            'contact' => '联系我们',
        ];
        $this->view->assign('templateList', $templates);
        $this->assignconfig('templateList', $templates);
    }

    public function add()
    {
        $this->error('固定页面不能新增，请通过页面管理维护现有页面');
    }

    public function del($ids = '')
    {
        $this->error('固定页面不能删除');
    }

    public function duplicate($ids = null)
    {
        $this->error('固定页面不能复制');
    }

    public function edit($ids = null)
    {
        $this->assertCurrentPage($ids);
        return parent::edit($ids);
    }

    public function preview($ids = null)
    {
        $this->assertCurrentPage($ids);
        return parent::preview($ids);
    }

    protected function resolveIds($ids)
    {
        $idList = parent::resolveIds($ids);
        $rows = PageModel::where('id', 'in', $idList)->where('slug', 'in', $this->currentSlugs)->column('id');
        $valid = array_values(array_map('intval', is_array($rows) ? $rows : []));
        sort($valid);
        $expected = $idList;
        sort($expected);
        if ($valid !== $expected) {
            $this->error('所选页面已退役或不属于当前站点');
        }
        return $idList;
    }

    protected function previewPath($row)
    {
        return '/page/' . $row['slug'] . '?preview=1';
    }

    private function assertCurrentPage($id)
    {
        $row = PageModel::get((int)$id);
        if (!$row || !in_array((string)$row['slug'], $this->currentSlugs, true)) {
            $this->error('页面不存在或已退役');
        }
    }
}

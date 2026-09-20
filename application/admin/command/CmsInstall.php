<?php

namespace app\admin\command;

use app\common\service\cms\InstallerService;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class CmsInstall extends Command
{
    protected function configure()
    {
        $this->setName('cms:install')
            ->setDescription('安装或修复企业 CMS 数据表、默认数据和后台菜单');
    }

    protected function execute(Input $input, Output $output)
    {
        try {
            $result = (new InstallerService())->install();
            $output->info(sprintf(
                'CMS 安装完成：%d 张数据表，表前缀 %s',
                $result['table_count'],
                $result['prefix']
            ));
            $output->info('请退出后台重新登录，或刷新后台菜单缓存。');
            return 0;
        } catch (\Throwable $e) {
            $output->error('CMS 安装失败：' . $e->getMessage());
            return 1;
        }
    }
}

<?php

namespace app\admin\command;

use app\common\service\cms\PublishService;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class CmsPublishScheduled extends Command
{
    protected function configure()
    {
        $this->setName('cms:publish-scheduled')
            ->setDescription('发布已经到期的 CMS 定时内容');
    }

    protected function execute(Input $input, Output $output)
    {
        $result = (new PublishService())->publishDueScheduled();
        $output->info('已发布：' . $result['published'] . ' 条');
        foreach ($result['failed'] as $failure) {
            $output->error(sprintf(
                '%s #%d 发布失败：%s',
                $failure['type'],
                $failure['id'],
                $failure['message']
            ));
        }
        return empty($result['failed']) ? 0 : 1;
    }
}

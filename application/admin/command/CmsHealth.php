<?php
namespace app\admin\command;
use app\common\service\cms\health\CmsHealthService;
use think\console\Command;use think\console\Input;use think\console\input\Option;use think\console\Output;
class CmsHealth extends Command
{
 protected function configure(){$this->setName('cms:health')->setDescription('检查 CMS 动态渲染架构与内容完整性')->addOption('json',null,Option::VALUE_OPTIONAL,'JSON 报告路径','');}
 protected function execute(Input $input,Output $output){try{$service=new CmsHealthService();$report=$service->run();foreach($report['findings'] as $finding){$row=$finding->toArray();$line='['.$row['severity'].'] '.$row['code'].' '.$row['message'];if($row['severity']==='ERROR')$output->error($line);elseif($row['severity']==='WARNING')$output->warning($line);else$output->info($line);} $path=trim((string)$input->getOption('json'));if($path!==''){$path=$this->path($path);if(!is_dir(dirname($path)))mkdir(dirname($path),0755,true);file_put_contents($path,json_encode(['counts'=>$report['counts'],'findings'=>array_map(function($f){return$f->toArray();},$report['findings']),'generated_at'=>$report['generated_at']],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT).PHP_EOL);}return$service->hasErrors($report)?1:0;}catch(\Throwable $e){$output->error('CMS 健康检查失败：'.$e->getMessage());return 1;}}
 private function path($path){if(strpos($path,'/')===0||preg_match('/^[a-zA-Z]:[\\\\\/]/',$path))return$path;return ROOT_PATH.ltrim(str_replace(['/','\\'],DS,$path),DS);}
}

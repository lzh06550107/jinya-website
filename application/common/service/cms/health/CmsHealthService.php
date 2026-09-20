<?php

namespace app\common\service\cms\health;

use think\Config;
use think\Db;

if (!class_exists(__NAMESPACE__ . '\\HealthFinding', false)) require_once __DIR__ . '/HealthFinding.php';

class CmsHealthService
{
    private $checks;
    private $root;
    private $connection;
    private $prefix;

    public function __construct(array $checks = null, $root = null, $connection = null, $prefix = null)
    {
        $this->checks = $checks;
        $this->root = $root ?: (defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 5) . DIRECTORY_SEPARATOR);
        $this->connection = $connection;
        $this->prefix = $prefix;
    }

    public function run()
    {
        $checks = $this->checks !== null ? $this->checks : $this->defaultChecks();
        $findings = [];
        foreach ($checks as $name => $check) {
            try {
                $result = call_user_func($check);
                foreach ($this->normalizeFindings($result, $name) as $finding) $findings[] = $finding;
            } catch (\Throwable $exception) {
                $findings[] = new HealthFinding('ERROR', 'health.check_failed', $name . ': ' . $exception->getMessage());
            }
        }
        $counts = ['PASS'=>0,'WARNING'=>0,'ERROR'=>0];
        foreach ($findings as $finding) $counts[$finding->severity()]++;
        return ['counts'=>$counts,'findings'=>$findings,'generated_at'=>date('c')];
    }

    public function hasErrors(array $report){return isset($report['counts']['ERROR']) && (int)$report['counts']['ERROR'] > 0;}

    private function defaultChecks()
    {
        return [
            'schema'=>[$this,'checkSchema'],
            'navigation'=>[$this,'checkNavigation'],
            'home'=>[$this,'checkHomeReferences'],
            'slug'=>[$this,'checkDuplicateSlugs'],
            'pages'=>[$this,'checkRequiredPages'],
            'media'=>[$this,'checkMediaFiles'],
            'frozen'=>[$this,'checkFrozenRuntime'],
            'controller'=>[$this,'checkControllerBoundaries'],
            'template'=>[$this,'checkTemplateBoundaries'],
        ];
    }

    public function checkSchema()
    {
        $required = [
            'cms_banner'=>['mobile_title','mobile_image','page_key','position'],
            'cms_home_section'=>['mobile_title','pc_display_count','mobile_display_count'],
            'cms_page_content_block'=>['source_key','extra_json'],
        ];
        $pdo=$this->pdo();$prefix=$this->prefix();$missing=[];
        foreach($required as $table=>$columns){foreach($columns as $column){$st=$pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?');$st->execute([$prefix.$table,$column]);if((int)$st->fetchColumn()===0)$missing[]=$table.'.'.$column;}}
        return $missing?new HealthFinding('ERROR','schema.column_missing','缺少动态渲染字段：'.implode('、',$missing)):new HealthFinding('PASS','schema.ok','动态渲染表结构完整');
    }

    public function checkNavigation()
    {
        $st=$this->pdo()->prepare("SELECT COUNT(*) FROM `{$this->prefix()}cms_navigation` WHERE `status`='normal' AND `position`='header' AND `deletetime` IS NULL");$st->execute();$count=(int)$st->fetchColumn();
        return $count>0?new HealthFinding('PASS','navigation.ok','有效页头导航 '.$count.' 项'):new HealthFinding('ERROR','navigation.missing','没有有效页头导航');
    }

    public function checkHomeReferences()
    {
        $pdo=$this->pdo();$p=$this->prefix();$invalid=0;
        $tables=['product'=>'cms_product','article'=>'cms_article','case'=>'cms_case','page'=>'cms_page'];
        foreach($tables as $type=>$table){$st=$pdo->prepare("SELECT COUNT(*) FROM `{$p}cms_home_section_reference` r LEFT JOIN `{$p}{$table}` t ON t.id=r.content_id AND t.deletetime IS NULL WHERE r.content_type=? AND r.deletetime IS NULL AND t.id IS NULL");$st->execute([$type]);$invalid+=(int)$st->fetchColumn();}
        return $invalid?new HealthFinding('ERROR','home.invalid_reference','首页存在 '.$invalid.' 个失效引用'):new HealthFinding('PASS','home.references_ok','首页引用有效');
    }

    public function checkDuplicateSlugs()
    {
        $duplicates=[];$pdo=$this->pdo();$p=$this->prefix();
        foreach(['cms_product','cms_article','cms_case','cms_page','cms_article_category'] as $table){$st=$pdo->query("SELECT `slug` FROM `{$p}{$table}` WHERE `slug`<>'' AND `deletetime` IS NULL GROUP BY `slug` HAVING COUNT(*)>1");if($st)foreach($st->fetchAll(\PDO::FETCH_COLUMN) as $slug)$duplicates[]=$table.':'.$slug;}
        return $duplicates?new HealthFinding('ERROR','slug.duplicate','重复 slug：'.implode('、',$duplicates)):new HealthFinding('PASS','slug.unique','所有 slug 唯一');
    }

    public function checkRequiredPages()
    {
        $required=['label','bags','boxes','about','contact'];$st=$this->pdo()->query("SELECT `slug` FROM `{$this->prefix()}cms_page` WHERE `deletetime` IS NULL");$existing=$st?$st->fetchAll(\PDO::FETCH_COLUMN):[];$missing=array_values(array_diff($required,$existing));
        return $missing?new HealthFinding('ERROR','page.required_missing','缺少固定单页：'.implode('、',$missing)):new HealthFinding('PASS','page.required_ok','固定单页完整');
    }

    public function checkMediaFiles()
    {
        $pdo=$this->pdo();$p=$this->prefix();$paths=[];
        foreach([['cms_product','cover_image'],['cms_article','cover_image'],['cms_case','cover_image'],['cms_page','cover_image'],['cms_banner','image']] as $item){$st=$pdo->query("SELECT `{$item[1]}` FROM `{$p}{$item[0]}` WHERE `{$item[1]}`<>'' AND `deletetime` IS NULL");if($st)$paths=array_merge($paths,$st->fetchAll(\PDO::FETCH_COLUMN));}
        $missing=[];foreach(array_unique($paths) as $path){if(strpos($path,'/uploads/')!==0&&strpos($path,'/assets/')!==0)continue;$file=rtrim($this->root,'/\\').DIRECTORY_SEPARATOR.'public'.str_replace('/',DIRECTORY_SEPARATOR,$path);if(!is_file($file))$missing[]=$path;}
        return $missing?new HealthFinding('WARNING','media.file_missing','缺失本地媒体 '.count($missing).' 个',['examples'=>array_slice($missing,0,20)]):new HealthFinding('PASS','media.files_ok','本地媒体引用有效');
    }

    public function checkFrozenRuntime(){return $this->scanFiles(['application/index','application/mobile','application/common/service/cms/render','application/common/repository/cms'],['FrozenHomeSnapshot','StrictPageCloneService','StrictProductCloneService','StrictMobilePageCloneService','StrictMobileProductCloneService','sites/www.ahkcm.com','sites/m.ahkcm.com'],'architecture.frozen_runtime','运行时冻结依赖');}
    public function checkControllerBoundaries(){return $this->scanFiles(['application/index/controller','application/mobile/controller'],['Db::','::where(','app\\common\\model\\cms','file_get_contents(','DOMDocument'],'architecture.controller_model','控制器越层访问');}
    public function checkTemplateBoundaries(){return $this->scanFiles(['application/index/view/cms','application/mobile/view/cms'],['{php}','Db::','Model::','file_get_contents','sites/www.ahkcm.com','sites/m.ahkcm.com'],'architecture.template_db','模板越层访问');}

    private function scanFiles(array $dirs,array $needles,$code,$label)
    {
        $hits=[];$root=rtrim($this->root,'/\\').DIRECTORY_SEPARATOR;
        foreach($dirs as $dir){$path=$root.str_replace('/',DIRECTORY_SEPARATOR,$dir);if(!is_dir($path))continue;$it=new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));foreach($it as $file){if($file->isDir()||!in_array(strtolower($file->getExtension()),['php','html'],true))continue;$source=file_get_contents($file->getPathname());foreach($needles as $needle){if(strpos($source,$needle)!==false)$hits[]=str_replace($root,'',$file->getPathname()).':'.$needle;}}}
        return $hits?new HealthFinding('ERROR',$code,$label.'：'.implode('、',array_slice($hits,0,20))):new HealthFinding('PASS',$code.'.ok',$label.'检查通过');
    }

    private function normalizeFindings($result,$name)
    {
        if($result instanceof HealthFinding)return[$result];if(is_array($result)){if(isset($result['severity']))return[new HealthFinding($result['severity'],isset($result['code'])?$result['code']:$name,isset($result['message'])?$result['message']:$name,isset($result['context'])?(array)$result['context']:[])];$out=[];foreach($result as $item)$out=array_merge($out,$this->normalizeFindings($item,$name));return$out;}return[new HealthFinding('PASS',$name.'.ok',$name.' 检查通过')];
    }
    private function pdo(){if($this->connection instanceof \PDO)return$this->connection;$c=$this->connection?:Db::connect();$pdo=$c->getPdo();if(!$pdo&&method_exists($c,'execute')){$c->execute('SELECT 1');$pdo=$c->getPdo();}if(!is_object($pdo))throw new \RuntimeException('数据库连接初始化失败');return$pdo;}
    private function prefix(){$p=$this->prefix!==null?$this->prefix:Config::get('database.prefix');if(!preg_match('/^[a-zA-Z0-9_]+$/',(string)$p))throw new \InvalidArgumentException('数据库表前缀不合法');return(string)$p;}
}

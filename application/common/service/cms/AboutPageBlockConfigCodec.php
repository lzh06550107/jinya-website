<?php
namespace app\common\service\cms;

class AboutPageBlockConfigCodec
{
    private $itemFields = [
        'title','text','image','url','value','subtitle','badge','group',
        'mobile_title','mobile_text','mobile_image','mobile_subtitle','mobile_badge','mobile_url',
        'pc_visible','mobile_visible',
    ];

    public function extract(array $params)
    {
        $rows=isset($params['about_items'])&&is_array($params['about_items'])?$params['about_items']:[];
        $extra=['items'=>[]];
        foreach($rows as $row){
            if(!is_array($row)) continue;
            $item=[];$has=false;
            foreach($this->itemFields as $field){
                if($field==='pc_visible'||$field==='mobile_visible'){
                    $item[$field]=isset($row[$field])&&(string)$row[$field]==='0'?0:1;
                    continue;
                }
                $value=array_key_exists($field,$row)?$this->scalar($row[$field]):'';
                $item[$field]=$value;if($value!=='')$has=true;
            }
            if($has)$extra['items'][]=$item;
        }
        foreach(array_keys($params) as $key){if(strpos($key,'about_')===0||strpos($key,'extra_')===0)unset($params[$key]);}
        $params['extra_json']=json_encode($extra,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        return $params;
    }

    public function itemFields(){return $this->itemFields;}
    private function scalar($value){return is_array($value)||is_object($value)?'':trim((string)$value);}
}

<?php
namespace app\common\service\cms;

class BagsPageBlockConfigCodec
{
    private $scalarMap = [
        'bags_note' => 'note',
        'bags_tabs' => 'tabs',
        'bags_secondary_title' => 'secondary_title',
        'bags_compare_brand' => 'compare_brand',
        'bags_compare_footer' => 'compare_footer',
    ];
    private $itemFields = [
        'title','text','image','url','value','subtitle','badge','group',
        'mobile_title','mobile_text','mobile_image','mobile_subtitle','mobile_badge','mobile_url',
        'pc_visible','mobile_visible',
    ];
    public function extract(array $params)
    {
        $extra=[];
        foreach($this->scalarMap as $input=>$key){ if(array_key_exists($input,$params)) $extra[$key]=$this->scalar($params[$input]); }
        $rows=isset($params['bags_items'])&&is_array($params['bags_items'])?$params['bags_items']:[];
        $extra['items']=[];
        foreach($rows as $row){
            if(!is_array($row)) continue;
            $item=[];$has=false;
            foreach($this->itemFields as $field){
                if($field==='pc_visible'||$field==='mobile_visible'){$item[$field]=isset($row[$field])&&(string)$row[$field]==='0'?0:1;continue;}
                $value=array_key_exists($field,$row)?$this->scalar($row[$field]):'';$item[$field]=$value;if($value!=='')$has=true;
            }
            if($has)$extra['items'][]=$item;
        }
        foreach(array_keys($params) as $key){ if(strpos($key,'bags_')===0||strpos($key,'extra_')===0) unset($params[$key]); }
        $params['extra_json']=json_encode($extra,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        return $params;
    }
    public function editorData($json)
    {
        $decoded=json_decode((string)$json,true);$decoded=is_array($decoded)?$decoded:[];
        $result=['items'=>isset($decoded['items'])&&is_array($decoded['items'])?$decoded['items']:[]];
        foreach($this->scalarMap as $input=>$key)$result[$key]=isset($decoded[$key])&&!is_array($decoded[$key])?(string)$decoded[$key]:'';
        return $result;
    }
    public function scalarInputs(){return $this->scalarMap;}
    public function itemFields(){return $this->itemFields;}
    private function scalar($value){return is_array($value)||is_object($value)?'':trim((string)$value);}
}

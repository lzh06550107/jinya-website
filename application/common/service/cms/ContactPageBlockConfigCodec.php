<?php
namespace app\common\service\cms;

class ContactPageBlockConfigCodec
{
    private $scalarMap = [
        'contact_company_title' => 'company_title',
        'contact_map_image' => 'map_image',
        'contact_map_provider' => 'map_provider',
        'contact_baidu_ak' => 'baidu_ak',
        'contact_map_lng' => 'map_lng',
        'contact_map_lat' => 'map_lat',
        'contact_map_zoom' => 'map_zoom',
        'contact_map_marker_title' => 'map_marker_title',
        'contact_map_marker_address' => 'map_marker_address',
        'contact_map_zoom_control' => 'map_zoom_control',
        'contact_map_scroll_wheel' => 'map_scroll_wheel',
        'contact_phone' => 'phone',
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
        $rows=isset($params['contact_items'])&&is_array($params['contact_items'])?$params['contact_items']:[];
        $extra['items']=[];
        foreach($rows as $row){
            if(!is_array($row)) continue;
            $item=[];$has=false;
            foreach($this->itemFields as $field){
                if($field==='pc_visible'||$field==='mobile_visible'){$item[$field]=isset($row[$field])&&(string)$row[$field]==='0'?0:1;continue;}
                $value=array_key_exists($field,$row)?$this->scalar($row[$field]):'';
                $item[$field]=$value;if($value!=='')$has=true;
            }
            if($has)$extra['items'][]=$item;
        }
        foreach(array_keys($params) as $key){ if(strpos($key,'contact_')===0||strpos($key,'extra_')===0) unset($params[$key]); }
        $params['extra_json']=json_encode($extra,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        return $params;
    }

    public function scalarInputs(){return $this->scalarMap;}
    public function itemFields(){return $this->itemFields;}
    private function scalar($value){return is_array($value)||is_object($value)?'':trim((string)$value);}
}

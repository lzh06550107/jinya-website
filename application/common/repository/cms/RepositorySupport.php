<?php
namespace app\common\repository\cms;
abstract class RepositorySupport
{
    protected function rows($value)
    {
        if (!$value) return [];
        if (is_object($value) && method_exists($value, 'toArray')) {
            $value = $value->toArray();
        }
        $out = [];
        foreach ($value as $key => $row) {
            $out[$key] = $this->row($row);
        }
        return $out;
    }
    protected function row($value) { if(!$value)return null; return is_object($value)&&method_exists($value,'toArray')?$value->toArray():(array)$value; }
    protected function orderedByIds(array $rows,array $ids)
    {
        $map=[]; foreach($rows as $row)$map[(int)$row['id']]=$row; $out=[]; foreach($ids as $id)if(isset($map[(int)$id]))$out[]=$map[(int)$id]; return $out;
    }
    protected function pagination($paginator)
    {
        return ['items'=>$this->rows($paginator->items()),'total'=>(int)$paginator->total(),'page'=>(int)$paginator->currentPage(),'last_page'=>(int)$paginator->lastPage(),'page_size'=>(int)$paginator->listRows()];
    }
}

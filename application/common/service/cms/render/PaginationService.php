<?php
namespace app\common\service\cms\render;
class PaginationService
{
    public function build($page, $pageSize, $total, $path, array $query = [])
    {
        $page=max(1,(int)$page); $pageSize=max(1,(int)$pageSize); $total=max(0,(int)$total); $last=max(1,(int)ceil($total/$pageSize));
        $items=[]; $start=max(1,$page-2); $end=min($last,$start+4); $start=max(1,$end-4);
        for($i=$start;$i<=$end;$i++) $items[]=['page'=>$i,'url'=>$this->url($path,$i,$query),'current'=>$i===$page];
        return ['page'=>$page,'page_size'=>$pageSize,'total'=>$total,'last_page'=>$last,'items'=>$items,'first_url'=>$this->url($path,1,$query),'previous_url'=>$page>1?$this->url($path,$page-1,$query):'','next_url'=>$page<$last?$this->url($path,$page+1,$query):'','last_url'=>$this->url($path,$last,$query)];
    }
    private function url($path,$page,array $query){ unset($query['page']); if($page>1)$query['page']=$page; $qs=http_build_query($query); return $path.($qs!==''?'?'.$qs:''); }
}

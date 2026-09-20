<?php
namespace app\common\repository\cms;
use app\common\model\cms\HomeSectionReference;
class ThinkHomeSectionReferenceRepository extends RepositorySupport implements HomeSectionReferenceRepositoryInterface
{
    public function ordered($sectionKey,$contentType,$terminal)
    {
        if ($sectionKey === 'products' && $contentType === 'product') {
            return $this->unifiedProducts($sectionKey, $contentType);
        }
        $exact=$this->rows(HomeSectionReference::where('section_key',$sectionKey)->where('content_type',$contentType)->where('terminal',$terminal)->where('status','normal')->order('weigh desc,id asc')->select());
        if($exact)return $exact;
        return $this->rows(HomeSectionReference::where('section_key',$sectionKey)->where('content_type',$contentType)->where('terminal','all')->where('status','normal')->order('weigh desc,id asc')->select());
    }

    private function unifiedProducts($sectionKey, $contentType)
    {
        $rows = $this->rows(HomeSectionReference::where('section_key', $sectionKey)
            ->where('content_type', $contentType)
            ->where('status', 'normal')
            ->order('weigh desc,id asc')
            ->select());
        $result = [];
        $seen = [];
        foreach ($rows as $row) {
            $contentId = isset($row['content_id']) ? (int)$row['content_id'] : 0;
            if ($contentId <= 0 || isset($seen[$contentId])) {
                continue;
            }
            $seen[$contentId] = true;
            $result[] = $row;
        }
        return $result;
    }
}

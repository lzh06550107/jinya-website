<?php
namespace app\common\repository\cms;
use app\common\service\cms\PageConfigService;
class ThinkPageConfigRepository implements PageConfigRepositoryInterface
{
    private $service;
    public function __construct(PageConfigService $service = null) { $this->service = $service ?: new PageConfigService(); }
    public function resolved($pageKey, $terminal) { return $this->service->resolvePage($pageKey, $terminal); }
}

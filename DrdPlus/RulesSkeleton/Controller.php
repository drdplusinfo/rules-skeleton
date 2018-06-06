<?php
namespace DrdPlus\RulesSkeleton;

use DeviceDetector\Parser\Bot;

class Controller extends \DrdPlus\FrontendSkeleton\Controller
{

    /** @var UsagePolicy */
    private $usagePolicy;
    /** @var Request */
    private $rulesSkeletonRequest;

    public function getUsagePolicy(): UsagePolicy
    {
        if ($this->usagePolicy === null) {
            $this->usagePolicy = new UsagePolicy(\basename($this->getDocumentRoot()), $this->getRequest());
        }

        return $this->usagePolicy;
    }

    /**
     * @return \DrdPlus\FrontendSkeleton\Request|Request
     */
    public function getRequest(): \DrdPlus\FrontendSkeleton\Request
    {
        if ($this->rulesSkeletonRequest === null) {
            $this->rulesSkeletonRequest = new Request(new Bot());
        }

        return $this->rulesSkeletonRequest;
    }
}
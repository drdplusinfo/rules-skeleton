<?php
namespace DrdPlus\RulesSkeleton;

use DeviceDetector\Parser\Bot;

class Controller extends \DrdPlus\FrontendSkeleton\Controller
{

    /** @var UsagePolicy */
    private $usagePolicy;
    /** @var Request */
    private $rulesSkeletonRequest;
    /** @var string */
    private $eshopUrl;
    /** @var bool */
    private $freeAccess = false;

    public function setFreeAccess(): Controller
    {
        $this->freeAccess = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFreeAccess(): bool
    {
        return $this->freeAccess;
    }

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

    /**
     * @return string
     */
    public function getEshopUrl(): string
    {
        if ($this->eshopUrl === null) {
            $eshopUrl = \trim(\file_get_contents($this->getDocumentRoot() . '/eshop_url.txt'));
            if (!\filter_var($eshopUrl, FILTER_VALIDATE_URL)) {
                throw new Exceptions\InvalidEshopUrl("Given e-shop URL from 'eshop_url.txt' is not valid: '$eshopUrl'");
            }
            $this->eshopUrl = $eshopUrl;
        }

        return $this->eshopUrl;
    }
}
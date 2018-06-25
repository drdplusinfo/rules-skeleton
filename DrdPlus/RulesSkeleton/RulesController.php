<?php
namespace DrdPlus\RulesSkeleton;

use DeviceDetector\Parser\Bot;

class RulesController extends \DrdPlus\FrontendSkeleton\FrontendController
{

    /** @var UsagePolicy */
    private $usagePolicy;
    /** @var Request */
    private $rulesSkeletonRequest;
    /** @var string */
    private $eshopUrl;
    /** @var bool */
    private $freeAccess = false;

    public function __construct(
        string $googleAnalyticsId,
        HtmlHelper $htmlHelper,
        string $documentRoot = null,
        string $webRoot = null,
        string $vendorRoot = null,
        string $partsRoot = null,
        string $genericPartsRoot = null,
        array $bodyClasses = []
    )
    {
        $documentRoot = $documentRoot ?? (PHP_SAPI !== 'cli' ? \rtrim(\dirname($_SERVER['SCRIPT_FILENAME']), '\/') : \getcwd());
        parent::__construct(
            $googleAnalyticsId,
            $htmlHelper,
            $documentRoot,
            $webRoot ?? $documentRoot . '/web/passed', // pass.php will change it to /web/pass if access is not allowed yet
            $vendorRoot,
            $partsRoot,
            $genericPartsRoot ?? __DIR__ . '/../../parts/rules-skeleton'
        );
    }

    public function setFreeAccess(): RulesController
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

    public function activateTrial(\DateTime $trialExpiration): bool
    {
        $visitorCanAccessContent = $this->getUsagePolicy()->activateTrial($trialExpiration);
        if ($visitorCanAccessContent) {
            $this->setRedirect(
                new \DrdPlus\FrontendSkeleton\Redirect(
                    "/?{$this->getUsagePolicy()->getTrialExpiredAtName()}={$trialExpiration->getTimestamp()}",
                    $trialExpiration->getTimestamp() - \time()
                )
            );
        }

        return $visitorCanAccessContent;
    }
}
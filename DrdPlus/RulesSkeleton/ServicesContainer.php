<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

use DeviceDetector\Parser\Bot;
use DrdPlus\FrontendSkeleton\PageCache;
use DrdPlus\FrontendSkeleton\Web\Body;
use DrdPlus\RulesSkeleton\Web\Pass;
use DrdPlus\RulesSkeleton\Web\Pdf;
use Granam\String\StringTools;
use \DrdPlus\RulesSkeleton\Web\Body as RulesBody;

/**
 * @method Configuration getConfiguration()
 * @method Dirs getDirs()
 */
class ServicesContainer extends \DrdPlus\FrontendSkeleton\ServicesContainer
{
    /** @var UsagePolicy */
    private $usagePolicy;
    /** @var Pass */
    private $pass;
    /** @var Pdf */
    private $pdf;

    public function __construct(Configuration $configuration, HtmlHelper $htmlHelper)
    {
        parent::__construct($configuration, $htmlHelper);
    }

    public function getPageCache(): PageCache
    {
        if ($this->pageCache === null) {
            $this->pageCache = new PageCache(
                $this->getWebVersions(),
                $this->getConfiguration()->getDirs(),
                $this->getHtmlHelper()->isInProduction(),
                $this->getConfiguration()->getDirs()->isAllowedAccessToWebFiles()
                    ? 'passed'
                    : 'pass'
            );
        }

        return $this->pageCache;
    }

    /**
     * @return Body|RulesBody
     */
    public function getBody(): Body
    {
        if ($this->body === null) {
            $this->body = new RulesBody(
                $this->getWebFiles(),
                $this->getUsagePolicy(),
                $this->getPass()
            );
        }

        return $this->body;
    }

    public function getPass(): Pass
    {
        if ($this->pass === null) {
            $this->pass = new Pass($this->getConfiguration(), $this->getUsagePolicy());
        }

        return $this->pass;
    }

    public function getUsagePolicy(): UsagePolicy
    {
        if ($this->usagePolicy === null) {
            $this->usagePolicy = new UsagePolicy(
                StringTools::toVariableName($this->getConfiguration()->getWebName()),
                $this->getRequest(),
                $this->getCookiesService(),
                $this->getConfiguration()
            );
        }

        return $this->usagePolicy;
    }

    /**
     * @return \DrdPlus\FrontendSkeleton\Request|Request
     */
    public function getRequest(): \DrdPlus\FrontendSkeleton\Request
    {
        if ($this->request === null) {
            $this->request = new Request(new Bot());
        }

        return $this->request;
    }

    public function getPdf(): Pdf
    {
        if ($this->pdf === null) {
            $this->pdf = new Pdf($this->getDirs());
        }

        return $this->pdf;
    }
}
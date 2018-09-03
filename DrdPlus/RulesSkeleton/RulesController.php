<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

use DrdPlus\RulesSkeleton\Web\Content;

/**
 * @method ServicesContainer getServicesContainer
 */
class RulesController extends \DrdPlus\FrontendSkeleton\FrontendController
{
    public function __construct(ServicesContainer $servicesContainer)
    {
        parent::__construct($servicesContainer);
    }

    public function activateTrial(\DateTime $now): bool
    {
        $trialExpiration = (clone $now)->modify('+4 minutes');
        $visitorCanAccessContent = $this->getServicesContainer()->getUsagePolicy()->activateTrial($trialExpiration);
        if ($visitorCanAccessContent) {
            $at = $trialExpiration->getTimestamp() + 1; // one second "insurance" overlap
            $afterSeconds = $at - $now->getTimestamp();
            $this->setRedirect(
                new \DrdPlus\FrontendSkeleton\Redirect(
                    "/?{$this->getServicesContainer()->getUsagePolicy()->getTrialExpiredAtName()}={$at}",
                    $afterSeconds
                )
            );
        }

        return $visitorCanAccessContent;
    }

    public function allowAccess(): RulesController
    {
        $this->getServicesContainer()->getUsagePolicy()->allowAccess();

        return $this;
    }

    /**
     * @return Content|\DrdPlus\FrontendSkeleton\Web\Content
     */
    public function getContent(): \DrdPlus\FrontendSkeleton\Web\Content
    {
        if ($this->content === null) {
            $this->content = new Content(
                $this->getServicesContainer(),
                $this->getRedirect()
            );
        }

        return $this->content;
    }

}
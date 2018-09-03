<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use DrdPlus\FrontendSkeleton\Redirect;
use DrdPlus\RulesSkeleton\ServicesContainer;

/**
 * @method ServicesContainer getServicesContainer
 */
class Content extends \DrdPlus\FrontendSkeleton\Web\Content
{
    public function __construct(ServicesContainer $servicesContainer, ?Redirect $redirect)
    {
        parent::__construct($servicesContainer, $redirect);
    }

    public function getStringContent(): string
    {
        if ($this->getServicesContainer()->getPdf()->sendPdf()) {
            return '';
        }

        return parent::getStringContent();
    }

}
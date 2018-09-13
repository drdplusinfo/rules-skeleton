<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use DrdPlus\RulesSkeleton\ServicesContainer;

/**
 * @method ServicesContainer getServicesContainer
 */
class Content extends \DrdPlus\FrontendSkeleton\Web\Content
{
    public const PDF = 'pdf';
    public const PASS = 'pass';

    public function getStringContent(): string
    {
        if ($this->containsPdf()) {
            return $this->getBody()->getBodyString();
        }

        return parent::getStringContent();
    }

    public function containsPdf(): bool
    {
        return $this->getContentType() === self::PDF;
    }

    public function containsPass(): bool
    {
        return $this->getContentType() === self::PASS;
    }

}
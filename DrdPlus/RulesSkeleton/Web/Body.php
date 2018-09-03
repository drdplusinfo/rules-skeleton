<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use DrdPlus\FrontendSkeleton\Web\WebFiles;
use DrdPlus\RulesSkeleton\UsagePolicy;

class Body extends \DrdPlus\FrontendSkeleton\Web\Body
{
    /** @var UsagePolicy */
    private $usagePolicy;
    /** @var Pass */
    private $pass;

    public function __construct(WebFiles $webFiles, UsagePolicy $usagePolicy, Pass $pass)
    {
        parent::__construct($webFiles);
        $this->usagePolicy = $usagePolicy;
        $this->pass = $pass;
    }

    public function getBodyString(): string
    {
        if (!$this->usagePolicy->isAccessAllowed()) {
            return <<<HTML
<div class="main pass">
  <div class="background-image"></div>
  {$this->pass->getPassString()}
</div>
HTML;
        }

        return parent::getBodyString();
    }
}
<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use DrdPlus\RulesSkeleton\Environment;
use DrdPlus\RulesSkeleton\HtmlHelper;
use Granam\WebContentBuilder\Web\HeadInterface;

class MainContent extends Content
{
    public function __construct(HtmlHelper $htmlHelper, Environment $environment, HeadInterface $head, RulesMainBody $body)
    {
        parent::__construct($htmlHelper, $environment, $head, $body);
    }
}
<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use DrdPlus\RulesSkeleton\Environment;
use DrdPlus\RulesSkeleton\HtmlHelper;
use Granam\WebContentBuilder\Web\HeadInterface;

class TablesContent extends MainContent
{
    public function __construct(HtmlHelper $htmlHelper, Environment $environment, HeadInterface $head, TablesBody $tablesBody)
    {
        parent::__construct($htmlHelper, $environment, $head, $tablesBody);
    }

}
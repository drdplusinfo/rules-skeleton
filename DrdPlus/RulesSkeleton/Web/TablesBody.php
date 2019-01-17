<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use DrdPlus\RulesSkeleton\HtmlHelper;
use DrdPlus\RulesSkeleton\Request;
use Granam\WebContentBuilder\HtmlDocument;
use Granam\WebContentBuilder\Web\Body;

class TablesBody extends Body
{
    /** @var HtmlHelper */
    private $htmlHelper;
    /** @var Request */
    private $request;

    public function __construct(WebFiles $webFiles, HtmlHelper $htmlHelper, Request $request)
    {
        parent::__construct($webFiles);
        $this->htmlHelper = $htmlHelper;
        $this->request = $request;
    }

    public function getValue(): string
    {
        $rawContent = parent::getValue();
        $rawContentDocument = new HtmlDocument($rawContent);
        $tables = $this->htmlHelper->findTablesWithIds($rawContentDocument, $this->request->getRequestedTablesIds());
        $tablesContent = '';
        foreach ($tables as $table) {
            $tablesContent .= $table->outerHTML . "\n";
        }

        return $tablesContent;
    }
}
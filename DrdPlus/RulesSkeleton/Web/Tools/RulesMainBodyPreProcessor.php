<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web\Tools;

use DrdPlus\RulesSkeleton\HtmlHelper;
use Granam\Strict\Object\StrictObject;
use Granam\WebContentBuilder\HtmlDocument;

class RulesMainBodyPreProcessor extends StrictObject implements HtmlDocumentProcessorInterface
{

    public function processDocument(HtmlDocument $htmlDocument): HtmlDocument
    {
        $this->solveLocalLinksInTableOfContents($htmlDocument);
        return $htmlDocument;
    }

    protected function solveLocalLinksInTableOfContents(HtmlDocument $htmlDocument)
    {
        $tableOfContents = $htmlDocument->getElementById(HtmlHelper::ID_TABLE_OF_CONTENTS);
        if (!$tableOfContents) {
            return;
        }
        foreach ($tableOfContents->getElementsByTagName('a') as $anchor) {
            $href = trim((string)$anchor->getAttribute('href'));
            if ($href !== '') {
                continue; // already defined anchors are not changed
            }
            $text = trim((string)$anchor->prop_get_innerHTML());
            if ($text === '') {
                continue; // no value to create anchor from
            }
            $anchor->setAttribute('href', "#$text");
        }
    }

}

<?php
namespace Tests\DrdPlus\RulesSkeleton;

use Gt\Dom\Element;
use Gt\Dom\HTMLDocument;

class CoveredPartsCanBeHiddenTest extends AbstractContentTest
{
    /**
     * @test
     */
    public function I_can_hide_covered_parts()
    {
        $html = $this->getRulesContentForDevWithHiddenCovered();
        $document = new HTMLDocument($html);
        $coveredElements = $document->getElementsByClassName('this_contains_covered_only');
        self::assertNotEmpty($coveredElements);
        foreach ($coveredElements as $covered) {
            self::assertTrue($covered->hasChildNodes());
            /** @var Element $childNode */
            foreach ($covered->children as $childNode) {
                self::assertTrue(
                    $childNode->classList->contains('hidden'),
                    'Every covered element should has "hidden" class, this one does not: ' . $childNode->outerHTML
                );
            }
        }
    }
}
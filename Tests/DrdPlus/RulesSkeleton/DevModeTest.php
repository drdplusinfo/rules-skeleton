<?php
namespace Tests\DrdPlus\RulesSkeleton;

use Gt\Dom\Element;
use Gt\Dom\HTMLDocument;

class DevModeTest extends AbstractContentTest
{

    /**
     * @test
     */
    public function I_see_content_marked_by_development_classes()
    {
        $content = $this->getRulesContentForDev();
        $html = new HTMLDocument($content);
        if (!$this->checkingSkeleton($html)) {
            self::assertFalse(false, 'Intended for skeleton only');

            return;
        }
        self::assertGreaterThan(0, $html->getElementsByClassName('covered-by-code')->count());
        self::assertGreaterThan(0, $html->getElementsByClassName('generic')->count());
        self::assertGreaterThan(0, $html->getElementsByClassName('excluded')->count());
    }
}
<?php
namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\Tests\FrontendSkeleton\AbstractContentTest;

class DevModeTest extends AbstractContentTest
{

    use AbstractContentTestTrait;

    /**
     * @test
     */
    public function I_see_content_marked_by_development_classes(): void
    {
        $html = $this->getRulesForDevHtmlDocument();
        if (!$this->isSkeletonChecked($html)) {
            self::assertFalse(false, 'Intended for skeleton only');

            return;
        }
        self::assertGreaterThan(
            0,
            $html->getElementsByClassName('covered-by-code')->count(),
            'No "covered-by-code" class has been found'
        );
        self::assertGreaterThan(
            0,
            $html->getElementsByClassName('generic')->count(),
            'No "generic" class has been found'
        );
        self::assertGreaterThan(
            0,
            $html->getElementsByClassName('excluded')->count(),
            'No "excluded" class has been found"'
        );
    }
}
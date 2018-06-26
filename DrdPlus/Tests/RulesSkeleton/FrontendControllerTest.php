<?php
namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\FrontendSkeleton\HtmlHelper;
use Mockery\MockInterface;

class FrontendControllerTest extends \DrdPlus\Tests\FrontendSkeleton\FrontendControllerTest
{
    protected static function getSutClass(string $sutTestClass = null, string $regexp = '~\\\Tests(.+)Test$~'): string
    {
        return parent::getSutClass($sutTestClass ?? RulesControllerTest::class, $regexp);
    }

    /**
     * @param bool|null $inProductionMode
     * @return HtmlHelper|MockInterface
     */
    protected function createHtmlHelper(bool $inProductionMode = null): HtmlHelper
    {
        $htmlHelper = $this->mockery(\DrdPlus\RulesSkeleton\HtmlHelper::class);
        if ($inProductionMode !== null) {
            $htmlHelper->shouldReceive('isInProduction')
                ->andReturn($inProductionMode);
        }

        return $htmlHelper;
    }

}
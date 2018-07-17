<?php
namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\FrontendSkeleton\HtmlHelper;
use DrdPlus\RulesSkeleton\Dirs;
use DrdPlus\Tests\RulesSkeleton\Partials\DirsForTestsTrait;
use Mockery\MockInterface;

class FrontendControllerTest extends \DrdPlus\Tests\FrontendSkeleton\FrontendControllerTest
{
    use DirsForTestsTrait;

    protected static function getSutClass(string $sutTestClass = null, string $regexp = '~\\\Tests(.+)Test$~'): string
    {
        return parent::getSutClass($sutTestClass ?? RulesControllerTest::class, $regexp);
    }

    /**
     * @param string|null $documentRoot
     * @return Dirs|\DrdPlus\FrontendSkeleton\Dirs
     */
    protected function createDirs(string $documentRoot = null): \DrdPlus\FrontendSkeleton\Dirs
    {
        return new Dirs($this->getMasterDocumentRoot(), $documentRoot ?? $this->getDocumentRoot());
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
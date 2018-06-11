<?php
namespace DrdPlus\Tests\RulesSkeleton;

use DeviceDetector\Parser\Bot;
use DrdPlus\RulesSkeleton\Controller;
use DrdPlus\RulesSkeleton\Request;
use DrdPlus\RulesSkeleton\UsagePolicy;

class ControllerTest extends \DrdPlus\Tests\FrontendSkeleton\ControllerTest
{

    /**
     * @test
     */
    public function I_can_set_access_as_free_for_everyone(): void
    {
        $controller = new Controller($this->getDocumentRoot());
        self::assertFalse($controller->isFreeAccess(), 'Access should be protected by default');
        self::assertSame($controller, $controller->setFreeAccess());
        self::assertTrue($controller->isFreeAccess(), 'Access should be switched to free');
    }

    /**
     * @test
     */
    public function I_can_get_request(): void
    {
        $controller = new Controller($this->getDocumentRoot());
        self::assertEquals(new Request(new Bot()), $controller->getRequest());
    }

    /**
     * @test
     */
    public function I_can_get_usage_policy(): void
    {
        $controller = new Controller($this->getDocumentRoot());
        self::assertEquals(new UsagePolicy(\basename($this->getDocumentRoot()), new Request(new Bot())), $controller->getUsagePolicy());
    }
}
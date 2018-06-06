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
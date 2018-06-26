<?php
namespace DrdPlus\Tests\RulesSkeleton;

use DeviceDetector\Parser\Bot;
use DrdPlus\FrontendSkeleton\HtmlDocument;
use DrdPlus\RulesSkeleton\RulesController;
use DrdPlus\RulesSkeleton\Request;
use DrdPlus\RulesSkeleton\UsagePolicy;

class RulesControllerTest extends \DrdPlus\Tests\FrontendSkeleton\FrontendControllerTest
{
    use AbstractContentTestTrait;

    /**
     * @test
     */
    public function I_will_get_current_skeleton_root_as_default_document_root(): void
    {
        $controller = new RulesController('Google Analytics ID foo', $this->createHtmlHelper());
        $expectedDocumentRoot = \realpath($this->getDocumentRoot());
        self::assertFileExists($expectedDocumentRoot, 'No real path found from document root ' . $this->getDocumentRoot());
        self::assertSame($expectedDocumentRoot, \realpath($controller->getDocumentRoot()));
    }

    /**
     * @test
     */
    public function I_will_get_current_document_root_related_passed_web_root_as_default(): void
    {
        $controller = new RulesController('Google Analytics ID foo', $this->createHtmlHelper());
        $expectedPassedWebRoot = \realpath($this->getDocumentRoot() . '/web/passed');
        self::assertFileExists($expectedPassedWebRoot, 'No real path found from passed web root ' . $this->getDocumentRoot());
        self::assertSame($expectedPassedWebRoot, \realpath($controller->getWebRoot()), "Unexpected {$controller->getWebRoot()}");
    }

    /**
     * @test
     */
    public function I_will_get_current_skeleton_generic_parts_root_as_default(): void
    {
        $controller = new RulesController('Google Analytics ID foo', $this->createHtmlHelper());
        $expectedGenericPartsRoot = \realpath($this->getDocumentRoot() . '/parts/rules-skeleton');
        self::assertFileExists($expectedGenericPartsRoot, 'No real path found from rules skeleton parts dir ' . $this->getDocumentRoot());
        self::assertSame($expectedGenericPartsRoot, \realpath($controller->getGenericPartsRoot()));
    }

    /**
     * @test
     */
    public function I_can_set_access_as_free_for_everyone(): void
    {
        $controller = new RulesController('Google Analytics ID foo', $this->createHtmlHelper());
        self::assertFalse($controller->isFreeAccess(), 'Access should be protected by default');
        self::assertSame($controller, $controller->setFreeAccess());
        self::assertTrue($controller->isFreeAccess(), 'Access should be switched to free');
    }

    /**
     * @test
     */
    public function I_can_get_request(): void
    {
        $controller = new RulesController('Google Analytics ID foo', $this->createHtmlHelper());
        self::assertEquals(new Request(new Bot()), $controller->getRequest());
    }

    /**
     * @test
     */
    public function I_can_get_usage_policy(): void
    {
        $controller = new RulesController('Google Analytics ID foo', $this->createHtmlHelper());
        self::assertEquals(
            new UsagePolicy(\basename(\realpath($this->getDocumentRoot())), new Request(new Bot())),
            $controller->getUsagePolicy()
        );
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function I_can_activate_trial(): void
    {
        $controller = new RulesController('Google Analytics ID foo', $this->createHtmlHelper());
        $trialUntil = new \DateTime();
        $usagePolicy = $this->mockery(UsagePolicy::class);
        $usagePolicy->expects('activateTrial')
            ->with($trialUntil)
            ->andReturn(true);
        $usagePolicy->makePartial();
        $controllerReflection = new \ReflectionClass($controller);
        $usagePolicyProperty = $controllerReflection->getProperty('usagePolicy');
        $usagePolicyProperty->setAccessible(true);
        $usagePolicyProperty->setValue($controller, $usagePolicy);
        self::assertTrue($controller->activateTrial($trialUntil));
    }

    /**
     * @test
     * @backupGlobals enabled
     */
    public function I_will_be_redirected_via_html_meta_on_trial(): void
    {
        self::assertCount(0, $this->getMetaRefreshes($this->getHtmlDocument()), 'No meta tag with refresh meaning expected so far');
        $this->passOut();
        $_POST['trial'] = 1;
        $now = \time();
        $trialContent = $this->fetchNonCachedContent();
        $document = new HtmlDocument($trialContent);
        $metaRefreshes = $this->getMetaRefreshes($document);
        self::assertCount(1, $metaRefreshes, 'One meta tag with refresh meaning expected');
        $metaRefresh = \current($metaRefreshes);
        self::assertSame('240; url=/?trialExpiredAt=' . ($now + 240), $metaRefresh->getAttribute('content'));
    }
}
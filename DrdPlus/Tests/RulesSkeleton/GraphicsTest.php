<?php
namespace DrdPlus\Tests\RulesSkeleton;

class GraphicsTest extends \DrdPlus\Tests\FrontendSkeleton\GraphicsTest
{
    use AbstractContentTestTrait;

    /**
     * @test
     */
    public function Licence_page_has_colored_background_image(): void
    {
        self::assertFileExists($this->getDocumentRoot() . '/images/licence-background.png');
    }
}
<?php
namespace Tests\DrdPlus\RulesSkeleton;

class GraphicsTest extends \Tests\DrdPlus\FrontendSkeleton\GraphicsTest
{
    /**
     * @test
     */
    public function Licence_page_has_colored_background_image(): void
    {
        self::assertFileExists($this->getDocumentRoot() . '/images/licence-background.png');
    }
}
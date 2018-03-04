<?php
namespace Tests\DrdPlus\RulesSkeleton;

class GraphicsTest extends AbstractContentTest
{
    /**
     * @test
     */
    public function Licence_page_has_colored_background_image(): void
    {
        if (\defined('FREE_ACCESS') && FREE_ACCESS) {
            self::assertFileNotExists(
                $this->getDocumentRoot() . '/images/rules-full.png',
                'Content with free access does not need colored background image for licence page'
            );
        } else {
            self::assertFileExists(
                $this->getDocumentRoot() . '/images/rules-full.png',
                'Licenced content need colored background image for licence page'
            );
        }
    }

    /**
     * @test
     */
    public function Rules_page_has_monochrome_background_image(): void
    {
        self::assertFileExists($this->getDocumentRoot() . '/images/rules-monochromatic.png');
    }
}
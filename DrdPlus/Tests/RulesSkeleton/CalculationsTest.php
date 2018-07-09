<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\RulesSkeleton\HtmlHelper;
use DrdPlus\Tests\FrontendSkeleton\Partials\AbstractContentTest;
use DrdPlus\Tests\RulesSkeleton\Partials\AbstractContentTestTrait;
use Gt\Dom\Element;
use Gt\Dom\HTMLCollection;

class CalculationsTest extends AbstractContentTest
{
    use AbstractContentTestTrait;

    /**
     * @test
     */
    public function Calculation_has_descriptive_name(): void
    {
        foreach ($this->getCalculations() as $calculation) {
            $parts = \explode('=', $calculation->textContent ?? '');
            $resultName = \trim($parts[0] ?? '');
            self::assertNotSame('Bonus', $resultName, "Expected more specific name of bonus for calculation\n$calculation->outerHTML");
            self::assertNotSame('Postih', $resultName, "Expected more specific name of malus for calculation\n$calculation->outerHTML");
        }
    }

    /**
     * @return HTMLCollection|Element[]
     */
    private function getCalculations(): HTMLCollection
    {
        static $calculations;
        if ($calculations === null) {
            $document = $this->getHtmlDocument();
            $calculations = $document->getElementsByClassName(HtmlHelper::CALCULATION_CLASS);
            if (\count($calculations) === 0 && !$this->isSkeletonChecked()) {
                self::assertFalse(false, 'No calculations in current document');
            } else {
                self::assertNotEmpty($calculations, 'Some calculations expected for skeleton testing');
            }
        }

        return $calculations;
    }

    /**
     * @test
     */
    public function Result_content_trap_has_descriptive_name(): void
    {
        $contents = [];
        foreach ($this->getCalculations() as $calculation) {
            foreach ($calculation->getElementsByClassName(HtmlHelper::CONTENT_CLASS) as $contentsFromCalculation) {
                $contents[] = $contentsFromCalculation;
            }
            if (\count($contents) === 0 && !$this->isSkeletonChecked()) {
                self::assertFalse(false, 'No content classes in current document');

                return;
            }
        }
        self::assertNotEmpty($contents, 'Some content class inside calculation class expected for skeleton testing');
        foreach ($contents as $content) {
            $parts = \explode('~', $content->textContent ?? '');
            if (\count($parts) < 3) {
                $textContent = \str_replace('&lt;', '<', $content->textContent); // the HTML library may already convert &lt; to <, but we are not sure
                if (\strpos($textContent, '<')) {
                    $parts = [];
                    [$parts[0], $trapAndSameOrGreater] = \explode('<', $textContent ?? '');
                    [$parts[1], $parts[2]] = \explode('≥', $trapAndSameOrGreater);
                }
            }
            $failName = \strtolower(\trim($parts[0] ?? ''));
            self::assertNotSame('nevšiml si', $failName, "Expected more specific name of failure for content\n$content->outerHTML");
            $successName = \strtolower(\trim($parts[2] ?? ''));
            self::assertNotSame('všiml si', $successName, "Expected more specific name of success for content\n$content->outerHTML");
        }
    }
}
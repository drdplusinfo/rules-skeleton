<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\RulesSkeleton\HtmlHelper;

class HtmlHelperTest extends \DrdPlus\Tests\FrontendSkeleton\HtmlHelperTest
{
    use Partials\AbstractContentTestTrait;

    /**
     * @test
     */
    public function I_can_get_html_document_with_block(): void
    {
        $blockNamesToExpectedContent = $this->getTestsConfiguration()->getBlockNamesToExpectedContent();
        if (!$blockNamesToExpectedContent) {
            self::assertEmpty($blockNamesToExpectedContent, 'No blocks to test');
        }
        $document = $this->getHtmlDocument();
        $htmlHelper = HtmlHelper::createFromGlobals($this->getDocumentRoot());
        foreach ($blockNamesToExpectedContent as $blockName => $expectedContent) {
            $documentWithBlock = $htmlHelper->getDocumentWithBlock($blockName, $document);
            self::assertSame($expectedContent, $documentWithBlock->body->innerHTML);
        }
    }
}
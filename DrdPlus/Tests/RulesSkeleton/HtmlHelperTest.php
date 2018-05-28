<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\RulesSkeleton\HtmlHelper;

class HtmlHelperTest extends \DrdPlus\Tests\FrontendSkeleton\HtmlHelperTest
{
    use AbstractContentTestTrait;

    /**
     * @test
     */
    public function I_can_get_html_document_with_block(): void
    {
        $document = $this->getHtmlDocument();
        $htmlHelper = HtmlHelper::createFromGlobals($this->getDocumentRoot());
        $documentWithBlock = $htmlHelper->getDocumentWithBlock('just-some-block', $document);
        self::assertSame(<<<HTML
<div class="block-just-some-block">
    First part of some block
</div>

<div class="block-just-some-block">
    Second part of some block
</div>

<div class="block-just-some-block">
    Last part of some block
</div>
HTML
            , $documentWithBlock->body->innerHTML);
    }
}
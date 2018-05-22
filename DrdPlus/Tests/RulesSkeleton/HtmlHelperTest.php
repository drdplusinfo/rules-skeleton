<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\FrontendSkeleton\HtmlHelper;
use Gt\Dom\Element;

class HtmlHelperTest extends \DrdPlus\Tests\FrontendSkeleton\HtmlHelperTest
{
    /**
     * @test
     */
    public function I_can_get_filtered_tables_from_content(): void
    {
        $htmlHelper = HtmlHelper::createFromGlobals($this->getDocumentRoot());

        $allTables = $htmlHelper->findTablesWithIds($this->getHtmlDocument());
        self::assertCount(2, $allTables);
        foreach (['iamsoalone', 'justsometable'] as $expectedTableId) {
            self::assertArrayHasKey($expectedTableId, $allTables);
            $table = $allTables[$expectedTableId];
            self::assertInstanceOf(Element::class, $table);
            self::assertNotEmpty($table->innerHTML);
            self::assertSame($expectedTableId, $table->getAttribute('id'));
        }

        self::assertEmpty($htmlHelper->findTablesWithIds($this->getHtmlDocument(), ['nonExistingTableId']));

        $singleTable = $htmlHelper->findTablesWithIds($this->getHtmlDocument(), ['JustSomeTable']);
        self::assertCount(1, $singleTable);
        self::assertArrayHasKey('justsometable', $singleTable, 'ID is expected to be lower-cased');
    }
}
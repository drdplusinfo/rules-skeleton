<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\RulesSkeleton\HtmlHelper;
use DrdPlus\Tests\RulesSkeleton\Partials\AbstractContentTest;
use Gt\Dom\Element;

class TableOfContentTest extends AbstractContentTest
{

    /**
     * @test
     */
    public function I_can_navigate_to_chapter_with_same_name_as_table_of_contents_mentions(): void
    {
        /** @var Element $tableOfContent */
        $tableOfContent = $this->getHtmlDocument()->getElementById(HtmlHelper::toId('table_of_content'));
        if (!$this->getTestsConfiguration()->hasTableOfContents()) {
            self::assertEmpty(
                $tableOfContent,
                'No items of table of contents expected due to tests configuration'
            );

            return;
        }
        self::assertNotEmpty(
            $tableOfContent,
            sprintf('Some table of content under ID %s expected due to tests configuration', HtmlHelper::toId('table_of_content'))
        );
        $contents = $tableOfContent->getElementsByClassName('content');
        self::assertNotEmpty(
            $contents,
            'Expected some ".content" elements as items of a table of contents #tableOfContents' . $tableOfContent->outerHTML
        );
        foreach ($contents as $content) {
            $anchors = $content->getElementsByTagName('a');
            self::assertNotEmpty($anchors->count(), 'Expected some anchors in table of content ' . $content->outerHTML);
            foreach ($anchors as $anchor) {
                $link = $anchor->getAttribute('href');
                if (\strpos($link, '#') !== 0) {
                    continue;
                }
                $name = $anchor->textContent;
                self::assertSame($link, '#' . HtmlHelper::toId($name));
            }
        }
    }
}
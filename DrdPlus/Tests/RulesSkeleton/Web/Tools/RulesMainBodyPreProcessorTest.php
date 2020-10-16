<?php declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton\Web\Tools;

use DrdPlus\RulesSkeleton\Web\Tools\RulesMainBodyPreProcessor;
use DrdPlus\Tests\RulesSkeleton\Partials\AbstractContentTest;
use Granam\WebContentBuilder\HtmlDocument;

class RulesMainBodyPreProcessorTest extends AbstractContentTest
{
    /**
     * @test
     */
    public function I_can_be_lazy_and_let_fill_table_of_content_anchors_automatically()
    {
        $htmlDocument = new HtmlDocument($this->getContentWithoutAnchorsInTableOfContents());
        $rulesMainBodyPreProcessor = new RulesMainBodyPreProcessor($this->getHtmlHelper());
        $rulesMainBodyPreProcessor->processDocument($htmlDocument);
        self::assertSame(
            $this->getExpectedContentWithAnchorsInTableOfContents(),
            html_entity_decode($htmlDocument->saveHTML())
        );
    }

    protected function getContentWithoutAnchorsInTableOfContents(): string
    {
        return $this->fetchFile(__DIR__ . '/html/contentWithoutAnchorsInTableOfContents.php');
    }

    protected function fetchFile(string $phpFile): string
    {
        ob_start();
        include $phpFile;
        return ob_get_clean();
    }

    protected function getExpectedContentWithAnchorsInTableOfContents(): string
    {
        return $this->fetchFile(__DIR__ . '/html/expectedContentWithAutomaticAnchorsInTableOfContents.php');
    }
}

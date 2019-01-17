<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton\Web;

use DrdPlus\RulesSkeleton\Configuration;
use DrdPlus\RulesSkeleton\HtmlHelper;
use DrdPlus\RulesSkeleton\Web\DebugContactsBody;
use DrdPlus\RulesSkeleton\Web\Head;
use DrdPlus\RulesSkeleton\Web\RulesMainContent;
use Granam\WebContentBuilder\HtmlDocument;
use Granam\WebContentBuilder\Web\Body;
use Gt\Dom\Element;
use Gt\Dom\Node;

class RulesMainContentTest extends MainContentTest
{
    /**
     * @test
     */
    public function I_can_hide_debug_contacts(): void
    {
        $configurationWithShownDebugContacts = $this->createCustomConfiguration(
            [Configuration::WEB => [Configuration::SHOW_DEBUG_CONTACTS => true]]
        );
        self::assertTrue($configurationWithShownDebugContacts->isShowDebugContacts(), 'Expected configuration with shown debug contacts');
        $rulesMainContentWithDebugContacts = $this->createRulesMainContent($configurationWithShownDebugContacts);
        if ($this->isSkeletonChecked()) {
            $htmlDocument = new HtmlDocument(<<<HTML
<html lang="cs">
<body>
{$rulesMainContentWithDebugContacts->getValue()}
</body>
</html>
HTML
            );
            /** @var Element $debugContacts */
            $debugContacts = $htmlDocument->getElementById('debug_contacts');
            self::assertNotEmpty($debugContacts, 'Debug contacts are missing');
            self::assertContains('mailto:info@drdplus.info', $debugContacts->outerHTML);
            self::assertContains('https://www.facebook.com/drdplus.info', $debugContacts->outerHTML);
            self::assertContains(
                'https://rpgforum.cz/forum/viewtopic.php?f=238&amp;t=14870"',
                $debugContacts->outerHTML
            );
        }
        $configurationWithHiddenDebugContacts = $this->createCustomConfiguration(
            [Configuration::WEB => [Configuration::SHOW_DEBUG_CONTACTS => false]]
        );
        self::assertFalse($configurationWithHiddenDebugContacts->isShowDebugContacts(), 'Expected configuration with hidden debug contacts');
        $rulesMainContentWithoutDebugContacts = $this->createRulesMainContent($configurationWithHiddenDebugContacts);
        if ($this->isSkeletonChecked()) {
            $htmlDocument = new HtmlDocument(<<<HTML
<html lang="cs">
<body>
{$rulesMainContentWithoutDebugContacts->getValue()}
</body>
</html>
HTML
            );
            $debugContacts = $htmlDocument->getElementById('debug_contacts');
            self::assertEmpty($debugContacts, 'Debug contacts should not be used at all');
        }
    }

    private function createRulesMainContent(Configuration $configuration): RulesMainContent
    {
        $head = $this->mockery(Head::class);
        $head->shouldReceive('getValue')
            ->andReturn('');
        $body = $this->mockery(Body::class);
        $body->shouldReceive('getValue')
            ->andReturn('');
        /** @var Head $head */
        /** @var Body $body */

        return new RulesMainContent(
            $configuration,
            $this->createHtmlHelper(),
            $head,
            $body,
            new DebugContactsBody()
        );
    }

    /**
     * @test
     */
    public function Every_plus_after_2d6_is_upper_indexed(): void
    {
        self::assertSame(
            0,
            \preg_match(
                '~.{0,10}2k6\s*(?!<span class="upper-index">\+</span>).{0,20}\+~',
                $this->getContentWithoutIds(),
                $matches
            ),
            \var_export($matches, true)
        );
    }

    private function getContentWithoutIds(): string
    {
        $document = clone $this->getHtmlDocument();
        /** @var Element $body */
        $body = $document->getElementsByTagName('body')[0];
        $this->removeIds($body);

        return $document->saveHTML();
    }

    private function removeIds(Element $element): void
    {
        if ($element->hasAttribute('id')) {
            $element->removeAttribute('id');
        }
        foreach ($element->children as $child) {
            $this->removeIds($child);
        }
    }

    /**
     * @test
     */
    public function Every_registered_trademark_and_trademark_symbols_are_upper_indexed(): void
    {
        self::assertSame(
            0,
            \preg_match(
                '~.{0,10}(?:(?<!<span class="upper-index">)\s*[®™]|[®™]\s*(?!</span>).{0,10})~u',
                $this->getContent(),
                $matches
            ),
            \var_export($matches, true)
        );
    }

    /**
     * @test
     */
    public function I_can_navigate_to_every_heading_by_expected_anchor(): void
    {
        $htmlDocument = $this->getHtmlDocument();
        $totalHeadingsCount = 0;
        for ($tagLevel = 1; $tagLevel <= 6; $tagLevel++) {
            $headings = $htmlDocument->getElementsByTagName('h' . $tagLevel);
            $totalHeadingsCount += \count($headings);
            foreach ($headings as $heading) {
                $id = $heading->id;
                self::assertNotEmpty($id, 'Expected some ID for ' . $heading->outerHTML);
                $anchors = $heading->getElementsByTagName('a');
                self::assertCount(1, $anchors, 'Expected single anchor in ' . $heading->outerHTML);
                $anchor = $anchors->current();
                $href = $anchor->getAttribute('href');
                self::assertNotEmpty($href, 'Expected some href of anchor in ' . $heading->outerHTML);
                self::assertSame('#' . $id, $href, 'Expected anchor pointing to the heading ID');
                $headingText = '';
                foreach ($anchor->childNodes as $childNode) {
                    /** @var Node $childNode */
                    if ($childNode->nodeType === \XML_TEXT_NODE) {
                        $headingText = $childNode->textContent;
                        break;
                    }
                }
                self::assertNotEmpty($headingText, 'Expected some human name for heading ' . $heading->outerHTML);
                $idFromText = HtmlHelper::toId($headingText);
                self::assertSame($id, $idFromText, "Expected different ID as created from '$headingText' heading");
            }
        }
        if (!$this->isSkeletonChecked() && !$this->getTestsConfiguration()->hasHeadings()) {
            self::assertSame(0, $totalHeadingsCount, 'No headings expected due to tests configurationF');
        } else {
            self::assertGreaterThan(0, $totalHeadingsCount, 'Expected some headings');
        }
    }

    /**
     * @test
     */
    public function Authors_got_heading(): void
    {
        $authorsHeading = $this->getHtmlDocument()->getElementById(HtmlHelper::ID_AUTHORS);
        if (!$this->isSkeletonChecked() && !$this->getTestsConfiguration()->hasAuthors()) {
            self::assertEmpty($authorsHeading, 'Authors are not expected');

            return;
        }
        self::assertNotEmpty($authorsHeading, 'Authors should have h3 heading');
        self::assertSame(
            'h3',
            $authorsHeading->nodeName,
            'Authors heading should be h3, but is ' . $authorsHeading->nodeName
        );
    }

    /**
     * @test
     */
    public function Authors_are_mentioned(): void
    {
        $body = $this->getHtmlDocument()->body;
        $rulesAuthors = $body->getElementsByClassName(HtmlHelper::CLASS_RULES_AUTHORS);
        if (!$this->isSkeletonChecked() && !$this->getTestsConfiguration()->hasAuthors()) {
            self::assertCount(0, $rulesAuthors, 'No rules authors expected due to tests configuration');

            return;
        }
        self::assertCount(
            1,
            $rulesAuthors,
            "Expected one '" . HtmlHelper::CLASS_RULES_AUTHORS . "' HTML class in rules content, got {$rulesAuthors->count()} of them"
        );
        $rulesAuthors = $rulesAuthors->current();
        self::assertNotEmpty(\trim($rulesAuthors->textContent), 'Expected some content of rules authors');
    }
}
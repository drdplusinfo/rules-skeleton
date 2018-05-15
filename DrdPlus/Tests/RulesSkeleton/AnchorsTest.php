<?php
namespace DrdPlus\Tests\RulesSkeleton;

use Granam\String\StringTools;
use Gt\Dom\Element;

class AnchorsTest extends \DrdPlus\Tests\FrontendSkeleton\AnchorsTest
{

    use AbstractContentTestTrait;

    /**
     * @test
     */
    public function All_anchors_point_to_syntactically_valid_links(): void
    {
        parent::All_anchors_point_to_syntactically_valid_links();
        $invalidAnchors = $this->parseInvalidAnchors($this->getOwnershipConfirmationContent());
        self::assertCount(
            0,
            $invalidAnchors,
            'Some anchors from ownership confirmation points to invalid links ' . implode(',', $invalidAnchors)
        );
    }

    /**
     * @test
     */
    public function I_can_go_directly_to_eshop_item_page(): void
    {
        if (\defined('FREE_ACCESS') && FREE_ACCESS) {
            self::assertFileNotExists(
                $this->getEshopFileName(),
                'Text-only and free content is accessible for anyone and can not be bought'
            );

            return;
        }
        self::assertFileExists($this->getEshopFileName());
        $eshopUrl = \trim(\file_get_contents($this->getEshopFileName()));
        self::assertRegExp('~^https://obchod\.altar\.cz/[^/]+\.html$~', $eshopUrl);
        $link = $this->getLinkToEshopFromRulesAuthorsBlock();

        self::assertSame(
            $eshopUrl,
            $link->getAttribute('href'),
            'Link to rules in eshop in \'rules-authors\' differs from that in ' . \basename($this->getEshopFileName())
        );
    }

    private function getLinkToEshopFromRulesAuthorsBlock(): Element
    {
        $body = $this->getHtmlDocument()->body;
        $rulesAuthors = $body->getElementsByClassName('rules-authors');
        self::assertGreaterThan(
            0,
            $rulesAuthors->count(),
            'Missing \'rules-authors\' HTML class in rules content ' . \var_export($body->nodeValue, true)
        );
        /** @var Element $rulesAuthors */
        $rulesAuthors = $rulesAuthors[0];
        $titles = $rulesAuthors->getElementsByClassName('title');
        self::assertNotEmpty($titles, 'Missing a \'title\' in \'rules-authors\'');
        self::assertCount(1, $titles);
        /** @var Element $title */
        $title = $titles[0];
        $rulesLinks = $title->getElementsByTagName('a');
        self::assertNotEmpty($rulesLinks, 'Missing a link to rules in \'rules-authors\'');
        self::assertCount(1, $rulesLinks);

        return $rulesLinks[0];
    }

    /**
     * @test
     */
    public function Links_to_vukogvazd_uses_https(): void
    {
        $linksToVukogvazd = [];
        foreach ($this->getExternalAnchors() as $anchor) {
            $link = $anchor->getAttribute('href');
            if (\strpos($link, 'vukogvazd.cz')) {
                $linksToVukogvazd[] = $link;
            }
        }
        if (\count($linksToVukogvazd) === 0) {
            self::assertFalse(false, 'No links to Vukogvazd have been found');
        } else {
            foreach ($linksToVukogvazd as $linkToVukogvazd) {
                self::assertStringStartsWith('https', $linkToVukogvazd, "Every link to vukogvazd should be via https: '$linkToVukogvazd'");
            }
        }
    }

    /**
     * @test
     */
    public function Character_sheet_comes_from_drdplus_info(): void
    {
        $linksToCharacterSheet = [];
        foreach ($this->getExternalAnchors() as $anchor) {
            $link = $anchor->getAttribute('href');
            $link = $this->turnToLocalLink($link);
            if (\strpos($link, 'charakternik.pdf')) {
                $linksToCharacterSheet[] = $link;
            }
        }
        if (((\defined('JUST_TEXT_TESTING') && JUST_TEXT_TESTING)
                || (\defined('NOT_FOR_PLAYERS') && NOT_FOR_PLAYERS)
            )
            && \count($linksToCharacterSheet) === 0
        ) {
            self::assertFalse(false, 'No links to PDF character sheet have been found');

            return;
        }
        self::assertGreaterThan(0, \count($linksToCharacterSheet), 'PDF character sheet is missing');
        $expectedOriginalLink = 'https://www.drdplus.info/pdf/charakternik.pdf';
        $expectedLink = $this->turnToLocalLink($expectedOriginalLink);
        foreach ($linksToCharacterSheet as $linkToCharacterSheet) {
            self::assertSame(
                $expectedLink,
                $linkToCharacterSheet,
                "Every link to PDF character sheet should lead to $expectedOriginalLink"
            );
        }
    }

    /**
     * @test
     */
    public function Journal_comes_from_drdplus_info(): void
    {
        $linksToJournal = [];
        foreach ($this->getExternalAnchors() as $anchor) {
            $link = $anchor->getAttribute('href');
            $link = $this->turnToLocalLink($link);
            if (\preg_match('~/denik_\w+\.pdf$~', $link)) {
                $linksToJournal[] = $link;
            }
        }
        if (((\defined('JUST_TEXT_TESTING') && JUST_TEXT_TESTING)
                || (\defined('NOT_FOR_PLAYERS') && NOT_FOR_PLAYERS)
            )
            || \count($linksToJournal) === 0
        ) {
            self::assertFalse(false, 'No links to PDF journal have been found');

            return;
        }
        self::assertGreaterThan(0, \count($linksToJournal), 'PDF journals are missing');
        if (\defined('WITHOUT_SPECIFIC_JOURNAL') && WITHOUT_SPECIFIC_JOURNAL) {
            foreach ($linksToJournal as $linkToJournal) {
                self::assertRegExp(
                    '~^http://www.drdplus[.]loc/pdf/deniky/denik_\w+[.]pdf$~',
                    $linkToJournal,
                    'Every link to PDF journal should lead to https://www.drdplus.info/pdf/deniky/denik_foo.pdf'
                );
            }

            return;
        }
        $expectedOriginalLink = $this->getExpectedLinkToJournal();
        $expectedLink = $this->turnToLocalLink($expectedOriginalLink);
        foreach ($linksToJournal as $linkToJournal) {
            self::assertSame(
                $expectedLink,
                $linkToJournal,
                "Every link to PDF journal should lead to $expectedOriginalLink"
            );
        }
    }

    private function getExpectedLinkToJournal(): string
    {
        return 'https://www.drdplus.info/pdf/deniky/denik_' . StringTools::toConstant($this->getProfessionName()) . '.pdf';
    }

    private function getProfessionName(): string
    {
        $pageTitle = $this->getPageTitle();
        self::assertSame(
            1,
            \preg_match('~\s(?<lastWord>\w+)$~u', $pageTitle, $matches),
            "No last word found in '$pageTitle'"
        );
        $lastWord = $matches['lastWord'];

        return \rtrim($lastWord, 'aeiouy');
    }
}
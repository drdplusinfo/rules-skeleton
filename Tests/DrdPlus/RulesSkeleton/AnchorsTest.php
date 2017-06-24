<?php
namespace Tests\DrdPlus\RulesSkeleton;

use Gt\Dom\Element;
use Gt\Dom\HTMLDocument;

class AnchorsTest extends AbstractContentTest
{
    /**
     * @test
     */
    public function All_anchors_point_to_syntactically_valid_links()
    {
        $invalidAnchors = $this->parseInvalidAnchors($this->getOwnershipConfirmationContent());
        self::assertCount(
            0,
            $invalidAnchors,
            'Some anchors from ownership confirmation points to invalid links ' . implode(',', $invalidAnchors)
        );
        $invalidAnchors = $this->parseInvalidAnchors($this->getRulesContent());
        self::assertCount(
            0,
            $invalidAnchors,
            'Some anchors from rules points to invalid links ' . implode(',', $invalidAnchors)
        );
    }

    /**
     * @param string $content
     * @return array
     */
    private function parseInvalidAnchors(string $content): array
    {
        preg_match_all('~(?<invalidAnchors><a[^>]+href="(?:(?!#|http|/).)+[^>]+>)~', $content, $matches);

        return $matches['invalidAnchors'];
    }

    /**
     * @test
     */
    public function Local_anchors_with_hashes_point_to_existing_ids()
    {
        $html = $this->getRulesHtmlDocument();
        foreach ($this->getLocalAnchors() as $localAnchor) {
            $expectedId = substr($localAnchor->getAttribute('href'), 1); // just remove leading #
            /** @var Element $target */
            $target = $html->getElementById($expectedId);
            self::assertNotEmpty($target, 'No element found by ID ' . $expectedId);
            self::assertNotContains('hidden', $target->className);
            self::assertNotRegExp('~(display:\s*none|visibility:\s*hidden)~', $target->getAttribute('style'));
        }
    }

    /**
     * @return array|Element[]
     */
    private function getLocalAnchors(): array
    {
        $html = $this->getRulesHtmlDocument();
        $localAnchors = [];
        /** @var Element $anchor */
        foreach ($html->getElementsByTagName('a') as $anchor) {
            if (strpos($anchor->getAttribute('href'), '#') === 0) {
                $localAnchors[] = $anchor;
            }
        }

        return $localAnchors;
    }

    /**
     * @test
     */
    public function All_external_anchors_can_be_reached()
    {
        foreach ($this->getExternalAnchors() as $anchor) {
            $link = $anchor->getAttribute('href');
            $curl = curl_init($link);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($curl, CURLOPT_HEADER, 1);
            curl_setopt($curl, CURLOPT_NOBODY, 1); // to get headers only
            curl_exec($curl);
            $responseHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            self::assertTrue(
                $responseHttpCode >= 200 && $responseHttpCode < 300,
                "Could not reach $link, got response code $responseHttpCode"
            );
        }
    }

    /**
     * @return array|Element[]
     */
    private function getExternalAnchors(): array
    {
        $html = $this->getRulesHtmlDocument();
        $externalAnchors = [];
        /** @var Element $anchor */
        foreach ($html->getElementsByTagName('a') as $anchor) {
            if (preg_match('~^(http|//)~', $anchor->getAttribute('href'))) {
                $externalAnchors[] = $anchor;
            }
        }

        return $externalAnchors;
    }

    /**
     * @test
     */
    public function External_anchors_with_hashes_point_to_existing_ids()
    {
        foreach ($this->getExternalAnchorsWithHash() as $anchor) {
            $link = $anchor->getAttribute('href');
            if (strpos($link, 'drdplus.info') > 0) {
                $link = str_replace(['drdplus.info', 'https'], ['drdplus.loc', 'http'], $link); // turn link into local version
            }
            $html = $this->getExternalHtmlDocument($link);
            $expectedId = substr($link, strpos($link, '#') + 1); // just remove leading #
            /** @var Element $target */
            $target = $html->getElementById($expectedId);
            self::assertNotEmpty(
                $target,
                'No element found by ID ' . $expectedId . ' in a document with URL ' . $link
                . ($link !== $anchor->getAttribute('href') ? ' (originally ' . $anchor->getAttribute('href') . ')' : '')
            );
            self::assertNotRegExp('~(display:\s*none|visibility:\s*hidden)~', $target->getAttribute('style'));
        }
    }

    /**
     * @var HTMLDocument[]|array
     */
    private $externalHtmlDocuments;

    /**
     * @return array|Element[]
     */
    private function getExternalAnchorsWithHash(): array
    {
        $externalAnchorsWithHash = [];
        foreach ($this->getExternalAnchors() as $anchor) {
            if (strpos($anchor->getAttribute('href'), '#') > 0) {
                $externalAnchorsWithHash[] = $anchor;
            }
        }

        return $externalAnchorsWithHash;
    }

    private function getExternalHtmlDocument(string $href): HTMLDocument
    {
        $link = substr($href, 0, strpos($href, '#') ?: null);
        if (($this->externalHtmlDocuments[$link] ?? null) === null) {
            $curl = curl_init($link);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);

            if (strpos($link, 'drdplus.loc') !== false || strpos($link, 'drdplus.info') !== false) {
                self::assertNotEmpty(preg_match('~//(?<prefix>[^.]+)\.drdplus\.~', $link, $matches));
                curl_setopt($curl, CURLOPT_COOKIE, $this->getCookieNameForOwnershipConfirmation($matches['prefix']) . '=1');
            }
            $content = curl_exec($curl);
            curl_close($curl);
            self::assertNotEmpty($content, 'Nothing has been fetched from URL ' . $link);
            $this->externalHtmlDocuments[$link] = @new HTMLDocument($content);
            if (strpos($link, 'drdplus.loc') !== false || strpos($link, 'drdplus.info') !== false) {
                self::assertCount(
                    0,
                    $this->externalHtmlDocuments[$link]->getElementsByTagName('form'),
                    'Seems we have not passed ownership check for ' . $href
                );
            }
        }

        return $this->externalHtmlDocuments[$link];
    }

}
<?php
namespace Tests\DrdPlus\RulesSkeleton;

class AnchorsTest extends AbstractContentTest
{
    /**
     * @test
     */
    public function All_anchors_point_to_valid_links()
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
}
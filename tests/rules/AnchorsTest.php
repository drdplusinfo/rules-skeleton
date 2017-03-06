<?php
namespace PPH;

use PHPUnit\Framework\TestCase;

class AnchorsTest extends TestCase
{
    protected function setUp()
    {
        if (!defined(DRD_PLUS_RULES_INDEX_FILE_NAME_TO_TEST)) {
            self::markTestSkipped('Missing constant \'DRD_PLUS_RULES_INDEX_FILE_NAME_TO_TEST\'');
        }
    }

    /**
     * @test
     */
    public function All_anchors_point_to_valid_links()
    {
        ob_start();
        include DRD_PLUS_RULES_INDEX_FILE_NAME_TO_TEST;
        $content = ob_get_clean();
        preg_match_all('~(?<invalidAnchors><a[^>]+href="(?:(?!#|http|/).)+[^>]+>)~', $content, $matches);
        self::assertCount(
            0,
            $matches['invalidAnchors'],
            'Some anchors points to invalid links ' . implode(',', $matches['invalidAnchors'])
        );
    }
}
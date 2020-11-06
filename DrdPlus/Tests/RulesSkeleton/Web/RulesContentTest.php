<?php

namespace DrdPlus\Tests\RulesSkeleton\Web;

use PHPUnit\Framework\TestCase;

class RulesContentTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideLinksToPrefetch
     */
    public function Links_can_be_prefetched(string $anchorRegexp, array $links, array $expectedPrefetchedLinks): void
    {
        self::markTestSkipped('Test this ' . __FUNCTION__);
    }

    public function provideLinksToPrefetch(): array
    {
        return [
            ['~^[.]/~', ['./foo'], ['./foo']],
        ];
    }
}

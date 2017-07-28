<?php
namespace Tests\DrdPlus\RulesSkeleton;

class ContentTest extends AbstractContentTest
{
    /**
     * @test
     */
    public function Every_plus_after_2d6_is_upper_indexed()
    {
        self::assertSame(
            0,
            preg_match('~.{0,10}2k6\s*(?!<span class="upper-index">\+</span>).{0,20}\+~', $this->getRulesContent(), $matches),
            var_export($matches, true)
        );
    }

    /**
     * @test
     */
    public function Every_registered_trademark_and_trademark_symbols_are_upper_indexed()
    {
        self::assertSame(
            0,
            preg_match(
                '~.{0,10}(?:(?<!<span class="upper-index">)\s*[®™]|[®™]\s*(?!</span>).{0,10})~u',
                $this->getRulesContent(),
                $matches
            ),
            var_export($matches, true)
        );
    }
}
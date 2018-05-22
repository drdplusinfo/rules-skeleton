<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\RulesSkeleton\Request;

class RequestTest extends \DrdPlus\Tests\FrontendSkeleton\RequestTest
{
    /**
     * @test
     * @backupGlobals
     * @dataProvider provideTablesIdsParameterName
     * @param string $parameterName
     */
    public function I_can_get_wanted_tables_ids(string $parameterName): void
    {
        self::assertSame([], (new Request())->getWantedTablesIds());
        $_GET[$parameterName] = '    ';
        self::assertSame([], (new Request())->getWantedTablesIds());
        $_GET[$parameterName] = 'foo';
        self::assertSame(['foo'], (new Request())->getWantedTablesIds());
        $_GET[$parameterName] .= ',bar,baz';
        self::assertSame(['foo', 'bar', 'baz'], (new Request())->getWantedTablesIds());
    }

    public function provideTablesIdsParameterName(): array
    {
        return [
            ['tables'],
            ['tabulky'],
        ];
    }
}
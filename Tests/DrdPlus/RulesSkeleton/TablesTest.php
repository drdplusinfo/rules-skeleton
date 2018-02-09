<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace Tests\DrdPlus\RulesSkeleton;

class TablesTest extends AbstractContentTest
{
    /**
     * @test
     */
    public function I_can_get_tables_only()
    {
        $withTables = $this->getRulesHtmlDocument('', ['tables' => '' /* all of them */]);
        $body = $withTables->getElementsByTagName('body')[0];
        $tables = $body->getElementsByTagName('table');
        self::assertGreaterThan(0, \count($tables), 'Expected some tables');
    }
}
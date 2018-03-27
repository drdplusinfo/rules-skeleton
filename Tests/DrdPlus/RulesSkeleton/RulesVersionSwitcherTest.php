<?php
namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\RulesSkeleton\RulesVersions;
use PHPUnit\Framework\TestCase;

class RulesVersionSwitcherTest extends TestCase
{

    /**
     * @test
     */
    public function I_can_switch_to_another_version(): void
    {
        $rulesVersions = new RulesVersions(\dirname(DRD_PLUS_RULES_INDEX_FILE_NAME_TO_TEST));
        $versions = $rulesVersions->getAllVersions();
        self::assertGreaterThan(
            1,
            \count($versions),
            'Expected at least two versions to test, got only ' . \implode($versions)
        );
    }
}

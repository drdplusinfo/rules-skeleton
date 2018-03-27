<?php
namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\RulesSkeleton\RulesVersions;
use DrdPlus\RulesSkeleton\RulesVersionSwitcher;
use PHPUnit\Framework\TestCase;

class RulesVersionSwitcherTest extends TestCase
{
    private $currentVersion;

    protected function setUp()
    {
        parent::setUp();
        $this->currentVersion = (new RulesVersions(\dirname(DRD_PLUS_RULES_INDEX_FILE_NAME_TO_TEST)))->getCurrentVersion();
    }

    protected function tearDown()
    {
        (new RulesVersionSwitcher(new RulesVersions(\dirname(DRD_PLUS_RULES_INDEX_FILE_NAME_TO_TEST))))
            ->switchToVersion($this->currentVersion);
        parent::tearDown();
    }

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
        $rulesVersionSwitcher = new RulesVersionSwitcher($rulesVersions);
        self::assertFalse(
            $rulesVersionSwitcher->switchToVersion($this->currentVersion),
            'Changing version to the same should result into false as nothing changed'
        );
        $otherVersions = \array_diff($versions, [$this->currentVersion]);
        foreach ($otherVersions as $otherVersion) {
            self::assertTrue(
                $rulesVersionSwitcher->switchToVersion($otherVersion),
                'Changing version should result into true as changed'
            );
        }
    }
}

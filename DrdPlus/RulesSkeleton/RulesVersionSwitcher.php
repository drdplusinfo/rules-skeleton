<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\RulesSkeleton;

use Granam\Strict\Object\StrictObject;

class RulesVersionSwitcher extends StrictObject
{

    /**
     * @var RulesVersions
     */
    private $rulesVersions;

    public function __construct(RulesVersions $rulesVersions)
    {
        $this->rulesVersions = $rulesVersions;
    }

    /**
     * @param string $version
     * @throws \DrdPlus\RulesSkeleton\Exceptions\ExecutingCommandFailed
     * @throws \DrdPlus\RulesSkeleton\Exceptions\InvalidVersionToSwitchInto
     * @throws \DrdPlus\RulesSkeleton\Exceptions\CanNotSwitchGitVersion
     */
    public function switchToVersion(string $version): void
    {
        if ($version === $this->rulesVersions->getCurrentVersion()) {
            return;
        }
        if (!$this->rulesVersions->hasVersion($version)) {
            throw new Exceptions\InvalidVersionToSwitchInto("Required version {$version} does not exist");
        }
        \exec('git checkout ' . \escapeshellarg($version), $rows, $returnCode);
        if ($returnCode !== 0) {
            throw new Exceptions\CanNotSwitchGitVersion(
                "Can not switch to required version '{$version}', got return code '{$returnCode}' and output "
                . \implode("\n", $rows));
        }
    }
}
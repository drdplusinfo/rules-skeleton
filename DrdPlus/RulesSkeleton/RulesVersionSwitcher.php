<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\RulesSkeleton;

use Granam\Strict\Object\StrictObject;

class RulesVersionSwitcher extends StrictObject
{

    /** @var RulesVersions */
    private $rulesVersions;
    /** @var VersionSwitchMutex */
    private $versionSwitchMutex;

    public function __construct(RulesVersions $rulesVersions, VersionSwitchMutex $versionSwitchMutex)
    {
        $this->rulesVersions = $rulesVersions;
        $this->versionSwitchMutex = $versionSwitchMutex;
    }

    /**
     * @param string $version
     * @return bool
     * @throws \DrdPlus\RulesSkeleton\Exceptions\ExecutingCommandFailed
     * @throws \DrdPlus\RulesSkeleton\Exceptions\InvalidVersionToSwitchInto
     * @throws \DrdPlus\RulesSkeleton\Exceptions\CanNotSwitchGitVersion
     * @throws \DrdPlus\RulesSkeleton\Exceptions\CanNotWriteLockOfVersionMutex
     * @throws \DrdPlus\RulesSkeleton\Exceptions\CanNotLockVersionMutex
     */
    public function switchToVersion(string $version): bool
    {
        // do NOT unlock it as we need the version to be locked until we fill or use the cache (lock will be unlocked automatically on script end)
        if ($version === $this->rulesVersions->getCurrentVersion()) {
            return false;
        }
        $this->versionSwitchMutex->lock();
        if (!$this->rulesVersions->hasVersion($version)) {
            throw new Exceptions\InvalidVersionToSwitchInto("Required version {$version} does not exist");
        }
        $command = 'git checkout ' . \escapeshellarg($version) . ' 2>&1';
        \exec($command, $rows, $returnCode);
        if ($returnCode !== 0) {
            throw new Exceptions\CanNotSwitchGitVersion(
                "Can not switch to required version '{$version}' by command '{$command}'"
                . ", got return code '{$returnCode}' and output\n"
                . \implode("\n", $rows)
            );
        }

        return true;
    }
}
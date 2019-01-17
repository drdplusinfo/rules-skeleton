<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

use DrdPlus\RulesSkeleton\Partials\CurrentMinorVersionProvider;
use DrdPlus\RulesSkeleton\Partials\CurrentPatchVersionProvider;
use Granam\Git\Git;
use Granam\Strict\Object\StrictObject;

/**
 * Reader of GIT tags defining available versions of web filesF
 */
class CurrentWebVersion extends StrictObject implements CurrentMinorVersionProvider, CurrentPatchVersionProvider
{

    public const LAST_UNSTABLE_VERSION = 'master';

    /** @var Configuration */
    private $configuration;
    /** @var string */
    private $currentCommitHash;
    /** @var string */
    private $currentPatchVersion;
    /** @var string[] */
    private $existingMinorVersions = [];
    /** @var string */
    private $lastUnstableVersionRoot;
    /** @var string */
    private $currentVersionRoot;
    /** @var Request */
    private $request;
    /** @var Git */
    private $git;
    /** @var \DrdPlus\WebVersions\WebVersions */
    private $webVersions;

    public function __construct(Configuration $configuration, Request $request, Git $git)
    {
        $this->configuration = $configuration;
        $this->request = $request;
        $this->git = $git;
    }

    public function getCurrentVersionRoot(): string
    {
        if ($this->currentVersionRoot === null) {
            $this->ensureMinorVersionExists($this->getCurrentMinorVersion());
            $this->currentVersionRoot = $this->configuration->getDirs()->getVersionRoot($this->getCurrentMinorVersion());
        }

        return $this->currentVersionRoot;
    }

    /**
     * @return string probably 'master'
     */
    private function getLastUnstableVersion(): string
    {
        return static::LAST_UNSTABLE_VERSION;
    }

    private function hasMinorVersion(string $version): bool
    {
        return $this->getWebVersions()->hasMinorVersion($version);
    }

    private function getWebVersions(): \DrdPlus\WebVersions\WebVersions
    {
        if (($this->webVersions ?? null) === null) {
            $this->webVersions = new \DrdPlus\WebVersions\WebVersions(
                $this->git,
                $this->getLastUnstableVersionRoot(),
                $this->getLastUnstableVersion()
            );
        }

        return $this->webVersions;
    }

    private function getLastUnstableVersionRoot(): string
    {
        if ($this->lastUnstableVersionRoot === null) {
            $this->ensureMinorVersionExists($this->getLastUnstableVersion());
            $this->lastUnstableVersionRoot = $this->configuration->getDirs()->getVersionRoot($this->getLastUnstableVersion());
        }

        return $this->lastUnstableVersionRoot;
    }

    public function getCurrentPatchVersion(): string
    {
        if ($this->currentPatchVersion === null) {
            $this->currentPatchVersion = $this->getLastPatchVersionOf($this->getCurrentMinorVersion());
        }

        return $this->currentPatchVersion;
    }

    public function getCurrentMinorVersion(): string
    {
        $requestedMinorVersion = $this->request->getRequestedVersion();
        if ($requestedMinorVersion && $this->hasMinorVersion($requestedMinorVersion)) {
            return $requestedMinorVersion;
        }

        return $this->configuration->getWebLastStableMinorVersion();
    }

    /**
     * @return string
     */
    public function getCurrentCommitHash(): string
    {
        if ($this->currentCommitHash === null) {
            $this->ensureMinorVersionExists($this->getCurrentMinorVersion());
            $this->currentCommitHash = $this->git->getLastCommitHash(
                $this->configuration->getDirs()->getVersionRoot($this->getCurrentMinorVersion())
            );
        }

        return $this->currentCommitHash;
    }

    /**
     * @param string $minorVersion
     * @return bool
     */
    private function ensureMinorVersionExists(string $minorVersion): bool
    {
        if (($this->existingMinorVersions[$minorVersion] ?? null) === null) {
            $toMinorVersionDir = $this->configuration->getDirs()->getVersionRoot($minorVersion);
            if (!\file_exists($toMinorVersionDir)) {
                $this->clone($minorVersion, $toMinorVersionDir);
            }
            $this->existingMinorVersions[$minorVersion] = true;
        }

        return true;
    }

    /**
     * @param string $minorVersion
     * @param string $toVersionDir
     * @return array
     */
    private function clone(string $minorVersion, string $toVersionDir): array
    {
        return $this->git->cloneBranch($minorVersion, $this->configuration->getWebRepositoryUrl(), $toVersionDir);
    }

    /**
     * @param string $minorVersion
     * @return array
     * @throws \Granam\Git\Exceptions\UnknownMinorVersion
     */
    public function update(string $minorVersion): array
    {
        $toMinorVersionDir = $this->configuration->getDirs()->getVersionRoot($minorVersion);
        if (!\file_exists($toMinorVersionDir)) {
            return $this->clone($minorVersion, $toMinorVersionDir);
        }

        return $this->git->updateBranch($minorVersion, $toMinorVersionDir);
    }

    /**
     * @param string $superiorVersion
     * @return string
     * @throws \Granam\Git\Exceptions\NoPatchVersionsMatch
     */
    public function getLastPatchVersionOf(string $superiorVersion): string
    {
        return $this->getWebVersions()->getLastPatchVersionOf($superiorVersion);
    }
}
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
class CurrentWebVersions extends StrictObject implements CurrentMinorVersionProvider, CurrentPatchVersionProvider
{

    public const LAST_UNSTABLE_VERSION = 'master';

    /** @var Configuration */
    private $configuration;
    /** @var string */
    private $lastStableMinorVersion;
    /** @var string */
    private $currentCommitHash;
    /** @var string */
    private $currentPatchVersion;
    /** @var string[] */
    private $existingMinorVersions = [];
    /** @var string */
    private $lastUnstableVersionRoot;
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

    /**
     * Intentionally are versions taken from branches only, not tags, to lower amount of versions to switch into.
     * @return array|string[]
     */
    public function getAllMinorVersions(): array
    {
        return $this->getWebVersionsReader()->getAllMinorVersions();
    }

    public function getAllStableMinorVersions(): array
    {
        return $this->getWebVersionsReader()->getAllStableMinorVersions();
    }

    private function getWebVersionsReader(): \DrdPlus\WebVersions\WebVersions
    {
        if (($this->webVersions ?? null) === null) {
            $this->webVersions = new \DrdPlus\WebVersions\WebVersions(
                $this->git,
                $this->getLastUnstableVersionWebRoot(),
                $this->getLastUnstableVersion()
            );
        }

        return $this->webVersions;
    }

    private function getLastUnstableVersionWebRoot(): string
    {
        if ($this->lastUnstableVersionRoot === null) {
            $this->ensureMinorVersionExists($this->getLastUnstableVersion());
            $this->lastUnstableVersionRoot = $this->configuration->getDirs()->getVersionRoot($this->getLastUnstableVersion());
        }

        return $this->lastUnstableVersionRoot;
    }

    /**
     * Gives last STABLE version, if any, or 'master' if not
     * @return string
     */
    public function getLastStableMinorVersion(): string
    {
        return $this->lastStableMinorVersion = $this->getWebVersionsReader()->getLastStableMinorVersion();
    }

    /**
     * Gives last STABLE patch version, if any, or 'master' if not
     * @return string
     */
    public function getLastStablePatchVersion(): string
    {
        return $this->getWebVersionsReader()->getLastStablePatchVersion();
    }

    /**
     * @return string probably 'master'
     */
    public function getLastUnstableVersion(): string
    {
        return static::LAST_UNSTABLE_VERSION;
    }

    /**
     * @param string $version
     * @return bool
     */
    public function hasMinorVersion(string $version): bool
    {
        return $this->getWebVersionsReader()->hasMinorVersion($version);
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
        $requestedMinorVersion = $this->request->getValue(Request::VERSION);
        if ($requestedMinorVersion && $this->hasMinorVersion($requestedMinorVersion)) {
            return $requestedMinorVersion;
        }

        return $this->configuration->getWebLastStableMinorVersion();
    }

    public function getVersionHumanName(string $version): string
    {
        return $this->getWebVersionsReader()->getVersionHumanName($version);
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
        return $this->getWebVersionsReader()->getLastPatchVersionOf($superiorVersion);
    }

    public function getAllPatchVersions(): array
    {
        return $this->getWebVersionsReader()->getAllPatchVersions();
    }

    public function isCurrentVersionStable(): bool
    {
        return $this->getCurrentMinorVersion() !== $this->getLastUnstableVersion();
    }
}
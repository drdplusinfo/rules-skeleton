<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

use DrdPlus\WebVersions\WebVersions;
use Granam\Git\Git;
use Granam\Strict\Object\StrictObject;

/**
 * Reader of GIT tags defining available versions of web filesF
 */
class CurrentWebVersion extends StrictObject
{

    public const LAST_UNSTABLE_VERSION = 'master';

    /** @var Configuration */
    private $configuration;
    /** @var string */
    private $currentCommitHash;
    /** @var string */
    private $currentPatchVersion;
    /** @var Request */
    private $request;
    /** @var Git */
    private $git;
    /** @var \DrdPlus\WebVersions\WebVersions */
    private $webVersions;

    public function __construct(Configuration $configuration, Request $request, Git $git, WebVersions $webVersions)
    {
        $this->configuration = $configuration;
        $this->request = $request;
        $this->git = $git;
        $this->webVersions = $webVersions;
    }

    public function getCurrentMinorVersion(): string
    {
        $requestedMinorVersion = $this->request->getRequestedVersion();
        if ($requestedMinorVersion && $this->hasMinorVersion($requestedMinorVersion)) {
            return $requestedMinorVersion;
        }

        return $this->configuration->getWebLastStableMinorVersion();
    }

    private function hasMinorVersion(string $version): bool
    {
        return $this->webVersions->hasMinorVersion($version);
    }

    public function getCurrentPatchVersion(): string
    {
        if ($this->currentPatchVersion === null) {
            $this->currentPatchVersion = $this->getLastPatchVersionOf($this->getCurrentMinorVersion());
        }

        return $this->currentPatchVersion;
    }

    private function getLastPatchVersionOf(string $superiorVersion): string
    {
        return $this->webVersions->getLastPatchVersionOf($superiorVersion);
    }

    public function getCurrentCommitHash(): string
    {
        if ($this->currentCommitHash === null) {
            $this->currentCommitHash = $this->git->getLastCommitHash($this->configuration->getDirs()->getProjectRoot());
        }

        return $this->currentCommitHash;
    }
}
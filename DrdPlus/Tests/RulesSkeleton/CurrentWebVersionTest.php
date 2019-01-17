<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\RulesSkeleton\Configuration;
use DrdPlus\RulesSkeleton\CurrentWebVersion;
use DrdPlus\Tests\RulesSkeleton\Partials\AbstractContentTest;
use DrdPlus\WebVersions\WebVersions;
use Granam\Git\Git;
use PHPUnit\Framework\TestCase;

class CurrentWebVersionTest extends AbstractContentTest
{

    /**
     * @test
     */
    public function I_can_get_current_version(): void
    {
        $webVersions = $this->createCurrentWebVersion(
            $this->getConfiguration(),
            $this->createRequest(CurrentWebVersion::LAST_UNSTABLE_VERSION),
            $this->createGit()
        );
        self::assertSame(CurrentWebVersion::LAST_UNSTABLE_VERSION, $webVersions->getCurrentMinorVersion());
    }

    /**
     * @test
     */
    public function I_can_get_current_patch_version(): void
    {
        $webVersions = $this->createCurrentWebVersion();
        if ($webVersions->getCurrentMinorVersion() === $this->getTestsConfiguration()->getExpectedLastUnstableVersion()) {
            self::assertSame(
                $this->getTestsConfiguration()->getExpectedLastUnstableVersion(),
                $webVersions->getCurrentPatchVersion()
            );
        } else {
            self::assertRegExp(
                '~^' . \preg_quote($webVersions->getCurrentMinorVersion(), '~') . '[.]\d+$~',
                $webVersions->getCurrentPatchVersion()
            );
        }
    }

    /**
     * @test
     */
    public function I_can_ask_it_if_code_has_specific_version(): void
    {
        $webVersions = $this->createWebVersions();
        self::assertTrue($webVersions->hasMinorVersion($this->getTestsConfiguration()->getExpectedLastUnstableVersion()));
        if ($this->isSkeletonChecked() || $this->getTestsConfiguration()->hasMoreVersions()) {
            self::assertTrue($webVersions->hasMinorVersion('1.0'));
        }
        self::assertFalse($webVersions->hasMinorVersion('-1'));
    }

    /**
     * @test
     */
    public function I_can_get_last_stable_version(): void
    {
        $webVersions = $this->createWebVersions();
        $lastStableVersion = $webVersions->getLastStableMinorVersion();
        if (!$this->isSkeletonChecked() && !$this->getTestsConfiguration()->hasMoreVersions()) {
            self::assertSame($this->getTestsConfiguration()->getExpectedLastUnstableVersion(), $lastStableVersion);
        } else {
            self::assertNotSame($this->getTestsConfiguration()->getExpectedLastUnstableVersion(), $lastStableVersion);
            self::assertGreaterThanOrEqual(0, \version_compare($lastStableVersion, '1.0'));
        }
        self::assertSame(
            $this->getTestsConfiguration()->getExpectedLastVersion(),
            $lastStableVersion,
            'Tests configuration requires different version'
        );
    }

    /**
     * @test
     */
    public function I_will_get_unstable_version_if_there_is_no_last_stable_version(): void
    {
        $unstableVersionRoot = $this->getDirs()->getVersionRoot(CurrentWebVersion::LAST_UNSTABLE_VERSION);
        $gitWithoutLastStableVersion = $this->createGitWithoutLastStableVersion($unstableVersionRoot);
        $webVersions = $this->createWebVersions($gitWithoutLastStableVersion, $unstableVersionRoot);
        self::assertSame(CurrentWebVersion::LAST_UNSTABLE_VERSION, $webVersions->getLastUnstableVersion());
        self::assertSame($webVersions->getLastUnstableVersion(), $webVersions->getLastStableMinorVersion());
    }

    private function createGitWithoutLastStableVersion(string $expectedRepositoryDir): Git
    {
        return new class($expectedRepositoryDir) extends Git
        {
            private $expectedRepositoryDir;

            public function __construct(string $expectedRepositoryDir)
            {
                $this->expectedRepositoryDir = $expectedRepositoryDir;
            }

            public function getLastStableMinorVersion(
                string $dir,
                bool $readLocal = self::INCLUDE_LOCAL_BRANCHES,
                bool $readRemote = self::INCLUDE_REMOTE_BRANCHES
            ): ?string
            {
                TestCase::assertSame($this->expectedRepositoryDir, $dir);

                return null;
            }

        };
    }

    /**
     * @test
     */
    public function I_can_get_last_unstable_version(): void
    {
        $webVersions = $this->createWebVersions();
        self::assertSame($this->getTestsConfiguration()->getExpectedLastUnstableVersion(), $webVersions->getLastUnstableVersion());
        $versions = $webVersions->getAllMinorVersions();
        $lastVersion = \reset($versions);
        self::assertSame($lastVersion, $webVersions->getLastUnstableVersion());
    }

    /**
     * @test
     */
    public function I_can_get_all_stable_versions(): void
    {
        $webVersions = $this->createWebVersions();
        $allVersions = $webVersions->getAllMinorVersions();
        $expectedStableVersions = [];
        foreach ($allVersions as $version) {
            if ($version !== $this->getTestsConfiguration()->getExpectedLastUnstableVersion()) {
                $expectedStableVersions[] = $version;
            }
        }
        self::assertSame($expectedStableVersions, $webVersions->getAllStableMinorVersions());
    }

    /**
     * @test
     */
    public function I_can_get_current_commit_hash(): void
    {
        $webVersions = $this->createCurrentWebVersion();
        $currentCommitHash = $webVersions->getCurrentCommitHash(); // called before reading .git/HEAD to ensure it exists
        $versionRoot = $this->getDirs()->getVersionRoot($this->getTestsConfiguration()->getExpectedLastVersion());
        $lastCommitHashFromGitHeadFile = $this->getLastCommitHashFromGitHeadFile($versionRoot);
        self::assertSame(
            $lastCommitHashFromGitHeadFile,
            $currentCommitHash,
            'Expected different last commit for version ' . $this->getTestsConfiguration()->getExpectedLastVersion()
            . ' taken from dir ' . $versionRoot
        );
    }

    /**
     * @param string $dir
     * @return string
     * @throws \DrdPlus\Tests\RulesSkeleton\Exceptions\CanNotReadGitHead
     */
    private function getLastCommitHashFromGitHeadFile(string $dir): string
    {
        $head = \file_get_contents($dir . '/.git/HEAD');
        if (\preg_match('~^[[:alnum:]]{40,}$~', $head)) {
            return $head; // the HEAD file contained the has itself
        }
        $gitHeadFile = \trim(\preg_replace('~ref:\s*~', '', \file_get_contents($dir . '/.git/HEAD')));
        $gitHeadFilePath = $dir . '/.git/' . $gitHeadFile;
        if (!\is_readable($gitHeadFilePath)) {
            throw new Exceptions\CanNotReadGitHead(
                "Could not read $gitHeadFilePath, in that dir are files "
                . \implode(',', \scandir(\dirname($gitHeadFilePath), SCANDIR_SORT_NONE))
            );
        }

        return \trim(\file_get_contents($gitHeadFilePath));
    }

    /**
     * @test
     */
    public function I_can_get_all_web_versions(): void
    {
        $webVersions = $this->createWebVersions();
        $allWebVersions = $webVersions->getAllMinorVersions();
        self::assertNotEmpty($allWebVersions, 'At least single web version (from GIT) expected');
        if (!$this->isSkeletonChecked() && !$this->getTestsConfiguration()->hasMoreVersions()) {
            self::assertSame([$this->getTestsConfiguration()->getExpectedLastUnstableVersion()], $allWebVersions);
        } else {
            self::assertSame(
                $this->getVersionsRange($this->getTestsConfiguration()->getExpectedLastVersion()),
                $allWebVersions
            );
        }
    }

    protected function getVersionsRange(string $lastVersion): array
    {
        $stableVersions = \range(1.0, (float)$lastVersion);

        $stringStableVersions = \array_map(function (float $version) {
            $stringVersion = (string)$version;
            if (\strpos($stringVersion, '.') === false) {
                $stringVersion .= '.0';
            }

            return $stringVersion;
        }, $stableVersions);
        \array_unshift($stringStableVersions, CurrentWebVersion::LAST_UNSTABLE_VERSION);

        return $stringStableVersions;
    }

    /**
     * @test
     */
    public function I_can_get_patch_versions(): void
    {
        $tags = $this->runCommand(
            'git -C ' . \escapeshellarg($this->getConfiguration()->getDirs()->getVersionRoot($this->getTestsConfiguration()->getExpectedLastUnstableVersion())) . ' tag'
        );
        $expectedVersionTags = [];
        foreach ($tags as $tag) {
            if (\preg_match('~^(\d+[.]){2}[[:alnum:]]+([.]\d+)?$~', $tag)) {
                $expectedVersionTags[] = $tag;
            }
        }
        if (!$this->isSkeletonChecked() && !$this->getTestsConfiguration()->hasMoreVersions()) {
            self::assertCount(0, $expectedVersionTags, 'No version tags expected as there are no versions');

            return;
        }
        $webVersions = $this->createWebVersions();
        self::assertNotEmpty(
            $expectedVersionTags,
            'Some version tags expected as we have versions ' . \implode(',', $webVersions->getAllStableMinorVersions())
        );
        $sortedExpectedVersionTags = $this->sortVersionsFromLatest($expectedVersionTags);
        self::assertSame($sortedExpectedVersionTags, $webVersions->getAllPatchVersions());
        $this->I_can_get_last_patch_version_for_every_stable_version($sortedExpectedVersionTags, $webVersions);
    }

    private function sortVersionsFromLatest(array $versions): array
    {
        \usort($versions, 'version_compare');

        return \array_reverse($versions);
    }

    private function I_can_get_last_patch_version_for_every_stable_version(array $expectedVersionTags, WebVersions $webVersions): void
    {
        foreach ($webVersions->getAllStableMinorVersions() as $stableVersion) {
            $matchingPatchVersionTags = [];
            foreach ($expectedVersionTags as $expectedVersionTag) {
                if (\strpos($expectedVersionTag, $stableVersion) === 0) {
                    $matchingPatchVersionTags[] = $expectedVersionTag;
                }
            }
            self::assertNotEmpty($matchingPatchVersionTags, "Missing patch version tags for version $stableVersion");
            $sortedMatchingVersionTags = $this->sortVersionsFromLatest($matchingPatchVersionTags);
            self::assertSame(
                \reset($sortedMatchingVersionTags),
                $webVersions->getLastPatchVersionOf($stableVersion),
                "Expected different patch version tag for $stableVersion"
            );
        }
    }

    /**
     * @test
     */
    public function I_will_get_last_unstable_version_as_patch_version(): void
    {
        $webVersions = $this->createWebVersions();
        self::assertSame($webVersions->getLastUnstableVersion(), $webVersions->getLastPatchVersionOf($webVersions->getLastUnstableVersion()));
    }

    /**
     * @test
     * @expectedException \Granam\Git\Exceptions\NoPatchVersionsMatch
     */
    public function I_can_not_get_last_patch_version_for_non_existing_version(): void
    {
        $nonExistingVersion = '-999.999';
        $webVersions = $this->createWebVersions();
        try {
            self::assertNotContains($nonExistingVersion, $webVersions->getAllMinorVersions(), 'This version really exists?');
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getMessage());
        }
        $webVersions->getLastPatchVersionOf($nonExistingVersion);
    }

    /**
     * @test
     */
    public function I_can_get_index_of_another_version(): void
    {
        $webVersions = $this->createWebVersions();
        $versions = $webVersions->getAllMinorVersions();
        if (!$this->isSkeletonChecked() && !$this->getTestsConfiguration()->hasMoreVersions()) {
            self::assertCount(1, $versions, 'Only a single version expected due to a config');

            return;
        }
        self::assertGreaterThan(
            1,
            \count($versions),
            'Expected at least two versions to test, got only ' . \implode(',', $versions)
        );
    }

    /**
     * @test
     */
    public function I_can_update_already_fetched_web_version(): void
    {
        $currentWebVersions = $this->createCurrentWebVersion();
        foreach ($this->createWebVersions()->getAllMinorVersions() as $version) {
            $result = $currentWebVersions->update($version);
            self::assertNotEmpty($result);
        }
    }

    /**
     * @test
     */
    public function I_can_update_web_version_even_if_not_yet_fetched_locally(): void
    {
        $webVersions = $this->createWebVersions();
        $currentWebVersions = $this->createCurrentWebVersion();
        $dirs = $this->getDirs();
        foreach ($webVersions->getAllMinorVersions() as $version) {
            $versionRoot = $dirs->getVersionRoot($version);
            if (\file_exists($versionRoot)) {
                $versionRootEscaped = \escapeshellarg($versionRoot);
                \exec("rm -fr $versionRootEscaped 2>&1", $output, $returnCode);
                self::assertSame(0, $returnCode, "Can not remove $versionRoot, got " . implode("\n", $output));
            }
            $result = $currentWebVersions->update($version);
            self::assertNotEmpty($result);
        }
    }

    /**
     * @test
     * @expectedException \Granam\Git\Exceptions\UnknownMinorVersion
     * @expectedExceptionMessageRegExp ~999[.]999~
     */
    public function I_can_not_update_non_existing_web_version(): void
    {
        $webVersions = $this->createCurrentWebVersion();
        $webVersions->update('999.999');
    }

    /**
     * @test
     */
    public function I_can_get_current_minor_version(): void
    {
        $configuration = $this->mockery($this->getConfigurationClass());
        $configuration->expects('getWebLastStableMinorVersion')
            ->andReturn('foo.bar.baz');
        /** @var Configuration $configuration */
        $webVersions = $this->createCurrentWebVersion($configuration, $this->createRequest(null /* no version */));

        self::assertSame('foo.bar.baz', $webVersions->getCurrentMinorVersion());
    }
}
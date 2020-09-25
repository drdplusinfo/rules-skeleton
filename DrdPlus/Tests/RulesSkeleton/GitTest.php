<?php declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\Tests\RulesSkeleton\Partials\AbstractContentTest;

class GitTest extends AbstractContentTest
{
    /**
     * @test
     */
    public function Generic_assets_are_versioned(): void
    {
        foreach (['css/generic', 'images/generic', 'js/generic'] as $assetsDir) {
            $this->isGenericAssetDirVersioned($assetsDir);
        }
    }

    private function isGenericAssetDirVersioned(string $assetsDir)
    {
        ['output' => $output, 'resultCode' => $resultCode] = $this->getGitFolderIgnoring($assetsDir);
        if (!$this->getTestsConfiguration()->areGenericAssetsVersioned()) {
            self::assertSame(
                1,
                $resultCode,
                sprintf(
                    "The $assetsDir dir should be ignored by Git as tests configuration says by '%s'",
                    TestsConfiguration::ARE_GENERIC_ASSETS_VERSIONED
                )
            ); // GIT check-ignore results into 0 if dir is ignored
            self::assertSame(
                [$assetsDir],
                $output,
                sprintf(
                    "The $assetsDir dir should be ignored by Git as tests configuration says by '%s'",
                    TestsConfiguration::ARE_GENERIC_ASSETS_VERSIONED
                )
            );
        } else {
            self::assertLessThanOrEqual(
                1, // GIT check-ignore results into 1 if dir is NOT ignored
                $resultCode,
                sprintf(
                    "The $assetsDir dir should not be ignored by Git as tests configuration says by '%s'",
                    TestsConfiguration::ARE_GENERIC_ASSETS_VERSIONED
                )
            );
            self::assertSame(
                [],
                $output,
                sprintf(
                    "The $assetsDir dir should not be ignored by Git as tests configuration says by '%s'",
                    TestsConfiguration::ARE_GENERIC_ASSETS_VERSIONED
                )
            );
        }
    }

    /**
     * @test
     */
    public function Vendor_dir_is_versioned_as_well(): void
    {
        ['output' => $output, 'resultCode' => $resultCode] = $this->getGitFolderIgnoring($this->getVendorRoot());
        if ($this->isSkeletonChecked()) {
            self::assertSame(0, $resultCode);
            self::assertSame([$this->getVendorRoot()], $output, 'The vendor dir should be ignored for skeleton');
        } elseif (!$this->getTestsConfiguration()->isVendorDirVersioned()) {
            self::assertLessThanOrEqual(
                0, // GIT check-ignore results into 0 if dir is ignored
                $resultCode,
                sprintf(
                    "The vendor dir '{$this->getVendorRoot()}' should be ignored by Git as tests configuration says by '%s'",
                    TestsConfiguration::IS_VENDOR_DIR_VERSIONED
                )
            );
            self::assertSame(
                [$this->getVendorRoot()],
                $output,
                sprintf(
                    "The vendor dir '{$this->getVendorRoot()}' should be ignored by Git as tests configuration says by '%s'",
                    TestsConfiguration::IS_VENDOR_DIR_VERSIONED
                )
            );
        } else {
            self::assertLessThanOrEqual(
                1, // GIT check-ignore results into 1 if dir is not ignored
                $resultCode,
                "Vendor dir should not be ignored for final project ({$this->getVendorRoot()})"
            );
            self::assertSame([], $output, "The vendor dir should be versioned, but is ignored ({$this->getVendorRoot()})");
        }
    }

    /**
     * @test
     */
    public function Local_project_config_is_ignored(): void
    {
        ['output' => $output, 'resultCode' => $resultCode] = $this->getGitFolderIgnoring('config.local.yml');
        self::assertSame(0, $resultCode, 'config.local.yml should be ignored'); // GIT check-ignore results into 0 if dir is ignored
        self::assertSame(['config.local.yml'], $output, 'config.local.yml should be ignored');
    }

    /**
     * @test
     */
    public function Cache_dir_is_ignored(): void
    {
        $cacheBaseRoot = \dirname($this->getDirs()->getCacheRoot()); // cache/cli => cache
        $cacheGitIgnore = $cacheBaseRoot . '/.gitignore';
        self::assertFileExists($cacheGitIgnore, 'Expected .gitignore in cache dir');
        self::assertSame(<<<TEXT
*
!/.gitignore
TEXT
            , \file_get_contents($cacheGitIgnore),
            'Expected different content in ' . $cacheGitIgnore
        );
    }
}
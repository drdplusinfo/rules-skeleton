<?php
namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\Tests\FrontendSkeleton\AbstractContentTest;

class PartsTest extends AbstractContentTest
{
    /**
     * @test
     */
    public function All_parts_from_frontend_skeleton_are_accessible_in_current_generic_parts_dir(): void
    {
        $genericPartsRoot = null;
        $indexPath = $this->getDocumentRoot() . '/index.php';
        self::assertFileExists($indexPath, 'Index is missing');
        \ob_start();
        /** @noinspection PhpIncludeInspection */
        include $indexPath;
        \ob_end_clean();
        self::assertNotEmpty($genericPartsRoot, 'Generic parts root stays empty after including index');
        self::assertDirectoryExists($genericPartsRoot, 'Generic parts root does not exist');
        $genericParts = $this->getDirFiles($genericPartsRoot);
        self::assertNotEmpty($genericParts, "NO generic parts found in {$genericPartsRoot}");
        $missingGenericParts = \array_diff($this->getFrontendSkeletonGenericParts(), $genericParts);
        self::assertEmpty(
            $missingGenericParts,
            "Some frontend skeleton generic parts are not included in {$genericPartsRoot}: "
            . \print_r($missingGenericParts, true)
        );
    }

    private function getFrontendSkeletonGenericParts(): array
    {
        $expectedGenericPartsDir = $this->getDocumentRoot() . '/vendor/drd-plus/frontend-skeleton/parts/frontend-skeleton';
        self::assertDirectoryExists($expectedGenericPartsDir, 'Can not find frontend skeleton parts dir');
        $expectedGenericParts = $this->getDirFiles($expectedGenericPartsDir);
        self::assertNotEmpty($expectedGenericParts, "No frontend skeleton generic parts found in {$expectedGenericPartsDir}");

        return $expectedGenericParts;
    }

    private function getDirFiles(string $dir): array
    {
        $folders = [];
        foreach (\scandir($dir, \SCANDIR_SORT_NONE) as $folder) {
            if ($folder === '.' || $folder === '..') {
                continue;
            }
            $folders[] = $folder;
        }

        return $folders;
    }
}
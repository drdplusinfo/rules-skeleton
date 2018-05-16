<?php
$documentRoot = $documentRoot ?? __DIR__;

$dirToScanForFrontendSkeleton = $documentRoot;
$currentDir = \str_replace('\\', '/', __DIR__);
if (\strpos($currentDir, '/vendor/') !== false) {
    $dirToScanForFrontendSkeleton = \substr($currentDir, 0, \strpos($currentDir, '/vendor/'));
}
do {
    foreach (\scandir($dirToScanForFrontendSkeleton, SCANDIR_SORT_NONE) as $folder) {
        if ($folder === 'vendor') {
            $frontendSkeletonIndex = $dirToScanForFrontendSkeleton . '/vendor/drd-plus/frontend-skeleton/index.php';
            if (\file_exists($frontendSkeletonIndex)) {
                /** @noinspection PhpIncludeInspection */
                require $frontendSkeletonIndex;

                return;
            }
            unset($frontendSkeletonIndex);
        }
    }
    $dirToScanForFrontendSkeleton .= '/..';
} while (\is_readable($dirToScanForFrontendSkeleton));
unset($dirToScanForFrontendSkeleton, $currentDir);

require __DIR__ . '/vendor/drd-plus/frontend-skeleton/index.php';

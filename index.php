<?php
$documentRoot = $documentRoot ?? (PHP_SAPI !== 'cli' ? \rtrim(\dirname($_SERVER['SCRIPT_FILENAME']), '\/') : \getcwd());
$vendorRoot = $vendorRoot ?? $documentRoot . '/vendor';
$partsRoot = \file_exists($documentRoot . '/parts')
    ? ($documentRoot . '/parts')
    : ($vendorRoot . '/drd-plus/rules-skeleton/parts');
$genericPartsRoot = $genericPartsRoot ?? __DIR__ . '/parts/rules-skeleton';

require_once $vendorRoot . '/autoload.php';

$controller = $controller ?? new \DrdPlus\RulesSkeleton\Controller($documentRoot, $documentRoot . '/web/passed', $vendorRoot, $partsRoot, $genericPartsRoot);

/** @noinspection PhpIncludeInspection */
require $vendorRoot . '/drd-plus/frontend-skeleton/index.php';
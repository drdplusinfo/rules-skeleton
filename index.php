<?php
$documentRoot = $documentRoot ?? (PHP_SAPI !== 'cli' ? \rtrim(\dirname($_SERVER['SCRIPT_FILENAME']), '\/') : \getcwd());
$vendorRoot = $vendorRoot ?? $documentRoot . '/vendor';

require_once $vendorRoot . '/autoload.php';

$controller = $controller ?? new \DrdPlus\RulesSkeleton\RulesController(
        $documentRoot,
        $webRoot ?? $documentRoot . '/web/passed', // pass.php will change it to /web/pass if access is not allowed yet
        $vendorRoot ?? null,
        $partsRoot ?? null,
        $genericPartsRoot ?? __DIR__ . '/parts/rules-skeleton'
    );

$vendorRoot = $controller->getVendorRoot();
$webRoot = $controller->getWebRoot();
$partsRoot = $controller->getPartsRoot();
$genericPartsRoot = $controller->getGenericPartsRoot();

/** @noinspection PhpIncludeInspection */
require $vendorRoot . '/drd-plus/frontend-skeleton/index.php';
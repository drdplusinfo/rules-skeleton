<?php
$documentRoot = $documentRoot ?? (PHP_SAPI !== 'cli' ? \rtrim(\dirname($_SERVER['SCRIPT_FILENAME']), '\/') : \getcwd());
$vendorRoot = $vendorRoot ?? $documentRoot . '/vendor';

require_once $vendorRoot . '/autoload.php';

$controller = $controller ?? new \DrdPlus\RulesSkeleton\RulesController(
        $googleAnalyticsId ?? 'UA-121206931-1',
        \DrdPlus\RulesSkeleton\HtmlHelper::createFromGlobals($documentRoot),
        $documentRoot,
        $webRoot ?? $documentRoot . '/web/passed' // pass.php will change it to /web/pass if access is not allowed yet
    );
if (!\is_a($controller, \DrdPlus\RulesSkeleton\RulesController::class)) {
    throw new \LogicException('Invalid controller class, expected ' . \DrdPlus\RulesSkeleton\RulesController::class
        . ' or descendant, got ' . \get_class($controller)
    );
}

$vendorRoot = $controller->getVendorRoot();
$webRoot = $controller->getWebRoot();
$partsRoot = $controller->getPartsRoot();
$genericPartsRoot = $controller->getGenericPartsRoot();

/** @noinspection PhpIncludeInspection */
require $vendorRoot . '/drd-plus/frontend-skeleton/index.php';
<?php
\error_reporting(-1);
if ((!empty($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] === '127.0.0.1') || PHP_SAPI === 'cli') {
    \ini_set('display_errors', '1');
} else {
    \ini_set('display_errors', '0');
}
$documentRoot = $documentRoot ?? (PHP_SAPI !== 'cli' ? \rtrim(\dirname($_SERVER['SCRIPT_FILENAME']), '\/') : \getcwd());
$vendorRoot = $vendorRoot ?? $documentRoot . '/vendor';
$latestVersion = $latestVersion ?? '1.0';

if (!require __DIR__ . '/parts/rules-skeleton/solve_version.php') {
    /** @noinspection PhpIncludeInspection */
    require_once __DIR__ . '/parts/rules-skeleton/safe_autoload.php';

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
}
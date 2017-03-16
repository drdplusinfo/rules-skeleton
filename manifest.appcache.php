<?php
$documentRoot = rtrim(!empty($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : __DIR__, '\/');

/** @noinspection PhpIncludeInspection */
require_once $documentRoot . '/vendor/autoload.php';

header('Content-Type: text/cache-manifest');
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1
header('Pragma: no-cache'); // HTTP 1.0
header('Expires: 0'); // Proxies

$request = new \DrdPlus\RulesSkeleton\Request();
$manifestCache = new \DrdPlus\RulesSkeleton\ManifestCache($documentRoot, new \DrdPlus\RulesSkeleton\Request());
if (!$manifestCache->manifestCacheIsValid()) {
    header('HTTP/1.0 404 Not Found');
} else {
    echo $manifestCache->getManifest($request->getRequestRelativeRootUrl());
}
exit;
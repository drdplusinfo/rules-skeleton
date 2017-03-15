<?php
$documentRoot = rtrim(!empty($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : __DIR__, '\/');

/** @noinspection PhpIncludeInspection */
require_once $documentRoot . '/vendor/autoload.php';

header('Content-Type: text/cache-manifest');
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1
header('Pragma: no-cache'); // HTTP 1.0
header('Expires: 0'); // Proxies

$manifestAccessCount = ($_COOKIE['manifestAccessCount'] ?? 0) + 1;
setcookie('manifestAccessCount', $manifestAccessCount);
$_COOKIE['manifestAccessCount'] = $manifestAccessCount;
if (empty($_COOKIE['manifestId'])) {
    $manifestId = uniqid('manifest', true);
    setcookie('manifestId', $manifestId);
    $_COOKIE['manifestId'] = $manifestId;
}

$request = new \DrdPlus\RulesSkeleton\Request();
$manifestCache = new \DrdPlus\RulesSkeleton\ManifestCache($documentRoot, new \DrdPlus\RulesSkeleton\Request());
echo $manifestCache->getManifest(str_replace('//manifest.', '//', $request->getRequestRelativeRootUrl()));
if ($manifestAccessCount >= 2) { // time for reset
    setcookie('manifestAccessCount', 0);
    setcookie('manifestId', '');
}
exit;
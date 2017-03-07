<?php
$documentRoot = rtrim(!empty($_SERVER['SCRIPT_FILENAME']) ? dirname($_SERVER['SCRIPT_FILENAME']) : __DIR__, '\/');

require_once $documentRoot . '/vendor/autoload.php';

header('Content-Type: text/cache-manifest');
header('Expires: on, 01 Jan 1970 00:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
setcookie('manifest-id', uniqid('manifest', true));

$manifestCache = new \DrdPlus\RulesSkeleton\ManifestCache($documentRoot);
echo $manifestCache->getManifest();
exit;
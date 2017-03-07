<?php
$documentRoot = rtrim(dirname($_SERVER['SCRIPT_FILENAME']), '\/');

require_once $documentRoot . '/vendor/autoload.php';

$manifestCache = new \DrdPlus\RulesSkeleton\ManifestCache($documentRoot);
echo $manifestCache->getManifest();
exit;
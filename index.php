<?php
error_reporting(-1);
if ((!empty($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] === '127.0.0.1') || PHP_SAPI === 'cli') {
    ini_set('display_errors', '1');
} else {
    ini_set('display_errors', '0');
}

$documentRoot = PHP_SAPI !== 'cli' ? rtrim(dirname($_SERVER['SCRIPT_FILENAME']), '\/') : getcwd();

/** @noinspection PhpIncludeInspection */
require_once $documentRoot . '/vendor/autoload.php';

if (($_SERVER['QUERY_STRING'] === 'pdf' || !file_exists($documentRoot . '/html'))
    && file_exists($documentRoot . '/pdf') && glob($documentRoot . '/pdf/*.pdf')
) {
    $pdfFileBasename = glob($documentRoot . '/pdf/*.pdf')[0];
    $pdfFile = $documentRoot . '/pdf/' . $pdfFileBasename;
    header('Content-type:application/pdf');
    header("Content-Disposition:attachment;filename='{$pdfFileBasename}'");
    header('Content-Length: ' . filesize($pdfFile));
    readfile($pdfFile);
    exit;
}

$usagePolicy = new \DrdPlus\RulesSkeleton\UsagePolicy(basename($documentRoot));
$visitorHasConfirmedOwnership = $usagePolicy->hasVisitorConfirmedOwnership();
if (!$visitorHasConfirmedOwnership) {
    require __DIR__ . '/visitorLicenceOwningConfirmation.php';
    $visitorHasConfirmedOwnership = $usagePolicy->hasVisitorConfirmedOwnership(); // could changed
}

if (!$visitorHasConfirmedOwnership) {
    exit;
}

require __DIR__ . '/content.php';
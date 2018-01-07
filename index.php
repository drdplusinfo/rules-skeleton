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

if (array_key_exists('tables', $_GET) || array_key_exists('tabulky', $_GET)) { // we do not require licence confirmation for tables only
    /** @see vendor/drd-plus/rules-html-skeleton/get_tables.php */
    echo include __DIR__ . '/get_tables.php';

    return;
}

$usagePolicy = new \DrdPlus\RulesSkeleton\UsagePolicy(basename($documentRoot));
$visitorHasConfirmedOwnership = $usagePolicy->hasVisitorConfirmedOwnership();
if (!$visitorHasConfirmedOwnership) {
    /** @see vendor/drd-plus/rules-html-skeleton/licence_owning_confirmation.php */
    require __DIR__ . '/licence_owning_confirmation.php';
    $visitorHasConfirmedOwnership = $usagePolicy->hasVisitorConfirmedOwnership(); // could changed
}

if (!$visitorHasConfirmedOwnership) {
    return;
}

if ((($_SERVER['QUERY_STRING'] ?? false) === 'pdf' || !file_exists($documentRoot . '/html'))
    && file_exists($documentRoot . '/pdf') && glob($documentRoot . '/pdf/*.pdf')
) {
    /** @see vendor/drd-plus/rules-html-skeleton/get_pdf.php */
    echo include __DIR__ . '/get_pdf.php';

    return;
}

/** @see vendor/drd-plus/rules-html-skeleton/content.php */
echo require __DIR__ . '/content.php';
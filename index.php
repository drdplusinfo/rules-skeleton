<?php
$documentRoot = $documentRoot ?? (PHP_SAPI !== 'cli' ? \rtrim(\dirname($_SERVER['SCRIPT_FILENAME']), '\/') : \getcwd());
$vendorRoot = $vendorRoot ?? $documentRoot . '/vendor';
$partsRoot = \file_exists($documentRoot . '/parts')
    ? ($documentRoot . '/parts')
    : ($vendorRoot . '/drd-plus/rules-skeleton/parts');

require $vendorRoot . '/drd-plus/frontend-skeleton/index.php';
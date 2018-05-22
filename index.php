<?php
$documentRoot = $documentRoot ?? (PHP_SAPI !== 'cli' ? \rtrim(\dirname($_SERVER['SCRIPT_FILENAME']), '\/') : \getcwd());
$vendorRoot = $vendorRoot ?? $documentRoot . '/vendor';
$partsRoot = $documentRoot . '/parts';

require $vendorRoot . '/drd-plus/frontend-skeleton/index.php';
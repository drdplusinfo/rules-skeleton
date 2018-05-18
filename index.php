<?php
$documentRoot = $documentRoot ?? __DIR__;

$vendorRoot = $vendorRoot ?? __DIR__ . '/vendor';
$partsRoot = __DIR__ . '/parts';
if (\file_exists($vendorRoot . '/drd-plus/frontend-skeleton/index.php')) {
    require $vendorRoot . '/drd-plus/frontend-skeleton/index.php';
} else {
    require __DIR__ . '/vendor/drd-plus/frontend-skeleton/index.php';
}
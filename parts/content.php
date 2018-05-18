<?php
if (\file_exists($partsRoot . '/router.php')) {
    if (require $partsRoot . '/router.php') {
        return ''; // routing solved
    }
} elseif (\file_exists($vendorRoot . '/drd-plus/rules-skeleton/parts/router.php')) {
    if (require $vendorRoot . '/drd-plus/rules-skeleton/parts/router.php') {
        return ''; // routing solved
    }
}

return require $vendorRoot . '/drd-plus/frontend-skeleton/parts/content.php';
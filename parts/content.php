<?php
if (\file_exists($partsRoot . '/router.php')) {
    if (require $partsRoot . '/router.php') {
        return ''; // routing solved
    }
} elseif (require __DIR__ . '/router.php') {
    return ''; // routing solved
}

return require __DIR__ . '/../vendor/drd-plus/frontend-skeleton/parts/content.php';
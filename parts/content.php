<?php
if (\file_exists($documentRoot . '/parts/router.php')) {
    if (require $documentRoot . '/parts/router.php') {
        return ''; // routing solved
    }
} elseif (require __DIR__ . '/router.php') {
    return ''; // routing solved
}

return require __DIR__ . '/../vendor/drd-plus/frontend-skeleton/parts/content.php';
<?php
if (require $genericPartsRoot . '/router.php') {
    return ''; // routing solved
}

/** @noinspection PhpIncludeInspection */
return require $vendorRoot . '/drd-plus/frontend-skeleton/parts/frontend-skeleton/content.php';
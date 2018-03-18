<?php
if (empty($visitorCanAccessContent)) {
    header('HTTP/1.0 403 Forbidden');
    echo '403 Forbidden (that does not mean you are doomed, though)';

    return;
}
$pageCache = new \DrdPlus\RulesSkeleton\PageCache($documentRoot);

if ($pageCache->cacheIsValid()) {
    return $pageCache->getCachedContent();
}
$previousMemoryLimit = \ini_set('memory_limit', '1G');
$htmlHelper = new \DrdPlus\RulesSkeleton\HtmlHelper(
    $documentRoot,
    !empty($_GET['mode']) && strpos(trim($_GET['mode']), 'dev') === 0,
    !empty($_GET['hide']) && strpos(trim($_GET['hide']), 'cover') === 0,
    !empty($_GET['show']) && strpos(trim($_GET['show']), 'intro') === 0
);
ob_start();
?>
    <!DOCTYPE html>
    <html lang="cs">
    <head>
        <title><?= $htmlHelper->getPageTitle() ?></title>
        <link rel="shortcut icon" href="/favicon.ico">
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, viewport-fit=cover">
        <?php
        /** @var array|string[] $cssFiles */
        $jsRoot = $documentRoot . '/js';
        $jsFiles = new \DrdPlus\RulesSkeleton\JsFiles($jsRoot);
        foreach ($jsFiles as $jsFile) { ?>
            <script type="text/javascript"
                    src="js/<?= $jsFile ?>"></script>
        <?php }
        /** @var array|string[] $cssFiles */
        $cssRoot = $documentRoot . '/css';
        $cssFiles = new \DrdPlus\RulesSkeleton\CssFiles($cssRoot);
        foreach ($cssFiles as $cssFile) { ?>
            <link rel="stylesheet" type="text/css" href="css/<?= $cssFile ?>">
        <?php } ?>
    </head>
    <body class="container">
    <div class="background-image"></div>
    <?php
    // $contactsFixed = true; // (default is on top or bottom of the content)
    // $contactsBottom = true; // (default is top)
    // $hideHomeButton = true; // (default is to show)
    include __DIR__ . '/contacts.php';
    $content = ob_get_contents();
    ob_clean();

    if (file_exists($documentRoot . '/custom_body_content.php')) {
        /** @noinspection PhpIncludeInspection */
        include $documentRoot . '/custom_body_content.php';
        $content .= ob_get_contents();
        ob_clean();
    }

    /** @var array|string[] $sortedHtmlFiles */
    $sortedHtmlFiles = new \DrdPlus\RulesSkeleton\HtmlFiles($documentRoot . '/html');
    foreach ($sortedHtmlFiles as $htmlFile) {
        $content .= file_get_contents($htmlFile);
    } ?>
    </body>
    </html>
<?php
$content .= ob_get_clean();
$pageCache->saveContentForDebug($content); // for debugging purpose
$htmlDocument = new \DrdPlus\RulesSkeleton\HtmlDocument($content);
$htmlHelper->prepareSourceCodeLinks($htmlDocument);
$htmlHelper->addIdsToTablesAndHeadings($htmlDocument);
$htmlHelper->replaceDiacriticsFromIds($htmlDocument);
$htmlHelper->replaceDiacriticsFromAnchorHashes($htmlDocument);
$htmlHelper->addAnchorsToIds($htmlDocument);
$htmlHelper->resolveDisplayMode($htmlDocument);
$htmlHelper->markExternalLinksByClass($htmlDocument);
$htmlHelper->externalLinksTargetToBlank($htmlDocument);
$htmlHelper->injectIframesWithRemoteTables($htmlDocument);
$htmlHelper->addVersionHashToAssets($htmlDocument);
if ((!empty($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] === '127.0.0.1') || PHP_SAPI === 'cli') {
    $htmlHelper->makeExternalLinksLocal($htmlDocument);
}
$updated = $htmlDocument->saveHTML();
$pageCache->cacheContent($updated);

if ($previousMemoryLimit !== false) {
    \ini_set('memory_limit', $previousMemoryLimit);
}

return $updated;

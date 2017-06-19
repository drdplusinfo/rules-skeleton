<?php
if (empty($visitorHasConfirmedOwnership)) {
    header('HTTP/1.0 403 Forbidden');
    echo '403 Forbidden (that does not means you are doomed, though)';
    exit;
}

$pageCache = new \DrdPlus\RulesSkeleton\PageCache($documentRoot);

if ($pageCache->cacheIsValid()) {
    echo $pageCache->getCachedContent();

    return;
}
ob_start();
?>
    <!DOCTYPE html>
    <html lang="cs">
    <head>
        <title><?= is_readable($documentRoot . '/name.txt') ? file_get_contents($documentRoot . '/name.txt') : ('Drd+ ' . basename($documentRoot)) ?></title>
        <link rel="shortcut icon" href="favicon.ico">
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
        <?php
        /** @var array|string[] $cssFiles */
        $jsRoot = $documentRoot . '/js';
        $jsFiles = new \DrdPlus\RulesSkeleton\JsFiles($jsRoot);
        foreach ($jsFiles as $jsFile) { ?>
            <script type="text/javascript"
                    src="js/<?php echo "$jsFile?version=" . md5_file($jsRoot . '/' . ltrim($jsFile, '\/')); ?>"></script>
        <?php }
        /** @var array|string[] $cssFiles */
        $cssRoot = $documentRoot . '/css';
        $cssFiles = new \DrdPlus\RulesSkeleton\CssFiles($cssRoot);
        foreach ($cssFiles as $cssFile) { ?>
            <link rel="stylesheet" type="text/css"
                  href="css/<?php echo "$cssFile?version=" . md5_file($cssRoot . '/' . ltrim($cssFile, '\/')); ?>">
        <?php } ?>
    </head>
    <body>
    <div class="background-image"></div>
    <?php
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
        /*$part = $htmlHelper->prepareCodeLinks($fileContent);
        $part = $htmlHelper->addIdsToTables($part);
        $part = $htmlHelper->addAnchorsToIds($part);
        $part = $htmlHelper->hideCovered($part);*/
    } ?>
    </body>
    </html>
<?php
$content .= ob_get_contents();
ob_end_clean();

$htmlDocument = new \Gt\Dom\HTMLDocument($content);
$htmlHelper = new \DrdPlus\RulesSkeleton\HtmlHelper(
    !empty($_GET['mode']) && preg_match('~^\s*dev~', $_GET['mode']),
    !empty($_GET['hide']) && trim($_GET['hide']) === 'covered'
);
$htmlHelper->prepareCodeLinks($htmlDocument);
$htmlHelper->addIdsToTablesAndHeadings($htmlDocument);
$htmlHelper->replaceDiacriticsFromIds($htmlDocument);
$htmlHelper->addAnchorsToIds($htmlDocument);
$htmlHelper->hideCovered($htmlDocument);
$updated = $htmlDocument->saveHTML();
echo $updated;
$pageCache->cacheContent($updated);
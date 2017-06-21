<?php
$tablesCache = new \DrdPlus\RulesSkeleton\TablesCache($documentRoot);
if ($tablesCache->cacheIsValid()) {
    return $tablesCache->getCachedContent();
}

$visitorHasConfirmedOwnership = true; // just a little hack
$rawContent = require __DIR__ . '/content.php';
?>
    <!DOCTYPE html>
    <html lang="cs">
    <head>
        <title>Tabulky pro Drd+ <?= basename($documentRoot) ?></title>
        <link rel="shortcut icon" href="favicon.ico">
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
        <?php
        /** @var array|string[] $cssFiles */
        $cssRoot = $documentRoot . '/css';
        $cssFiles = new \DrdPlus\RulesSkeleton\CssFiles($cssRoot);
        foreach ($cssFiles as $cssFile) { ?>
            <link rel="stylesheet" type="text/css"
                  href="css/<?php echo "$cssFile?version=" . md5_file($cssRoot . '/' . ltrim($cssFile, '\/')); ?>">
        <?php } ?>
        <style>
            table {
                float: left;
            }
        </style>
    </head>
    <body>
    <?php
    $content = ob_get_contents();
    ob_clean();
    $htmlHelper = new \DrdPlus\RulesSkeleton\HtmlHelper(
        !empty($_GET['mode']) && preg_match('~^\s*dev~', $_GET['mode']),
        !empty($_GET['hide']) && trim($_GET['hide']) === 'covered'
    );
    $tables = $htmlHelper->findTablesWithIds(new \Gt\Dom\HTMLDocument($rawContent));
    foreach ($tables as $table) {
        $content .= $table->outerHTML . "\n";
    }
    ?>
    </body>
    </html>
<?php
$content .= ob_get_clean();
$tablesCache->cacheContent($content);
return $content;
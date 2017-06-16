<?php
$tablesCache = new \DrdPlus\RulesSkeleton\TablesCache($documentRoot);
if ($tablesCache->cacheIsValid()) {
    echo $tablesCache->getCachedContent();

    return;
}

ob_start();
$visitorHasConfirmedOwnership = true; // just a little hack
require __DIR__ . '/content.php';
$content = ob_get_clean();

preg_match_all('~<\s*table[^>]*>(?:(?!</table>).)*</table>~si', $content, $matches);
/** @var array|string[] $tables */
$tables = $matches[0];
ob_start();
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
    </head>
    <body>
<?php foreach ($tables as $table) {
    echo $table;
}
$content = ob_get_clean();
$tablesCache->cacheContent($content);
echo $content;
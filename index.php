<?php
error_reporting(-1);
if ((!empty($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] === '127.0.0.1') || PHP_SAPI === 'cli') {
    ini_set('display_errors', '1');
} else {
    ini_set('display_errors', '0');
}

$documentRoot = PHP_SAPI !== 'cli' ? rtrim(dirname($_SERVER['SCRIPT_FILENAME']), '\/') : getcwd();

/** @noinspection PhpIncludeInspection */
require_once $documentRoot . '/vendor/autoload.php';

$request = new \DrdPlus\RulesSkeleton\Request();
$pageCache = new \DrdPlus\RulesSkeleton\PageCache($documentRoot);

if ($pageCache->pageCacheIsValid()) {
    echo $pageCache->getCachedPage();
    exit;
}
ob_start();
?>
    <!DOCTYPE html>
    <html lang="cs">
    <head>
        <title>Drd+ <?= basename($documentRoot) ?></title>
        <!--suppress HtmlUnknownTarget -->
        <link rel="shortcut icon" href="favicon.ico">
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
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
    <article>
        <?php
        $content = ob_get_contents();
        ob_clean();

        $htmlHelper = new \DrdPlus\RulesSkeleton\HtmlHelper(
            !empty($_GET['mode']) && preg_match('~^\s*dev~', $_GET['mode']),
            !empty($_GET['hide']) && trim($_GET['hide']) === 'covered'
        );

        if (file_exists($documentRoot . '/custom_body_content.php')) {
            /** @noinspection PhpIncludeInspection */
            include $documentRoot . '/custom_body_content.php';
            $content .= ob_get_contents();
            ob_clean();
        }

        /** @var array|string[] $sortedHtmlFiles */
        $sortedHtmlFiles = new \DrdPlus\RulesSkeleton\HtmlFiles($documentRoot . '/html');
        foreach ($sortedHtmlFiles as $htmlFile) {
            $fileContent = file_get_contents($htmlFile);
            ?>
            <?php
            $part = $htmlHelper->prepareCodeLinks($fileContent);
            $part = $htmlHelper->addIdsToTables($part);
            $part = $htmlHelper->addAnchorsToIds($part);
            $part = $htmlHelper->hideCovered($part);
            echo $part; ?>
            <?php
            $content .= ob_get_contents();
            /** @noinspection DisconnectedForeachInstructionInspection */
            ob_clean();
        } ?>
    </article>
    </body>
    </html>
<?php
$content .= ob_get_contents();
ob_end_clean();
echo $content;
$pageCache->cachePage($content);
exit;
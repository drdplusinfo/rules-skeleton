<?php
error_reporting(-1);
if ((!empty($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] === '127.0.0.1') || PHP_SAPI === 'cli') {
    ini_set('display_errors', '1');
} else {
    ini_set('display_errors', '0');
}

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="DrD plus"');
    header('HTTP/1.0 401 Unauthorized');
    echo '401 Unauthorized';
    exit;
} else if ($_SERVER['PHP_AUTH_USER'] !== 'drd' || $_SERVER['PHP_AUTH_PW'] !== '+') {
    header('HTTP/1.0 401 Unauthorized');
    echo '401 Unauthorized';
    exit;
}

$documentRoot = rtrim(dirname($_SERVER['SCRIPT_FILENAME']), '\/');

/** @noinspection PhpIncludeInspection */
require_once $documentRoot . '/vendor/autoload.php';

$request = new \DrdPlus\RulesSkeleton\Request();
$pageCache = new \DrdPlus\RulesSkeleton\PageCache($documentRoot);

if ($pageCache->pageCacheIsValid()) {
    echo $pageCache->getCachedPage();
    exit;
} else {
    ob_start();
}
?>
    <!DOCTYPE html>
    <html lang="cs">
    <head>
        <title>Drd+ <?= basename($documentRoot) ?></title>
        <link rel="shortcut icon" href="favicon.ico">
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://fonts.googleapis.com/css?family=Courgette|Marck+Script"
              rel="stylesheet">
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
    <?php
    $content = ob_get_contents();
    ob_clean();

    $htmlHelper = new \DrdPlus\RulesSkeleton\HtmlHelper(
        !empty($_GET['mode']) && preg_match('~^\s*dev~', $_GET['mode']),
        !empty($_GET['hide']) && trim($_GET['hide']) === 'covered'
    );

    /** @var array|string[] $sortedHtmlFiles */
    $sortedHtmlFiles = new \DrdPlus\RulesSkeleton\HtmlFiles($documentRoot . '/html');
    foreach ($sortedHtmlFiles as $htmlFile) {
        $fileContent = file_get_contents($htmlFile);
        ?>
        <article>
            <?php
            $article = $htmlHelper->prepareCodeLinks($fileContent);
            $article = $htmlHelper->addIdsToTables($article);
            $article = $htmlHelper->addAnchorsToIds($article);
            $article = $htmlHelper->hideCovered($article);
            echo $article; ?>
        </article>
        <?php
        $content .= ob_get_contents();
        /** @noinspection DisconnectedForeachInstructionInspection */
        ob_clean();
    } ?>
    </body>
    </html>
<?php
$content .= ob_get_contents();
ob_end_clean();
echo $content;
$pageCache->cachePage($content);
exit;
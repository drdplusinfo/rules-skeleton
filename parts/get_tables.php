<?php
if (!\headers_sent()) {
    // anyone can show content of this page
    \header('Access-Control-Allow-Origin: *', true);
}
$vendorRoot = $vendorRoot ?? __DIR__ . '/..';
require_once $vendorRoot . '/autoload.php';

$tablesCache = new \DrdPlus\RulesSkeleton\TablesCache($documentRoot, $webVersions, $htmlHelper->isInProduction());
if ($tablesCache->isCacheValid()) {
    return $tablesCache->getCachedContent();
}

$visitorCanAccessContent = true; // just a little hack
// must NOT include current content.php as it uses router and that requires this script so endless recursion happens
$rawContent = require $vendorRoot . '/drd-plus/frontend-skeleton/parts/content.php';
\ob_start();
?>
  <!DOCTYPE html>
  <html lang="cs">
    <head>
      <title>Tabulky pro Drd+ <?= \basename($documentRoot) ?></title>
      <link rel="shortcut icon" href="../favicon.ico">
      <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
        <?php
        /** @var array|string[] $cssFiles */
        $cssRoot = $documentRoot . '/css';
        $cssFiles = new \DrdPlus\FrontendSkeleton\CssFiles($cssRoot);
        foreach ($cssFiles as $cssFile) { ?>
          <link rel="stylesheet" type="text/css"
                href="css/<?= $cssFile ?>">
        <?php } ?>
      <style>
        table {
          float: left;
        }
      </style>
      <script type="text/javascript">
          // let just second level domain to be the document domain to allow access to iframes from other subdomains
          document.domain = document.domain.replace(/^(?:[^.]+\.)*([^.]+\.[^.]+).*/, '$1');
      </script>
    </head>
    <body>
        <?php
        $content = \ob_get_contents();
        \ob_clean();
        $wantedTableIds = \array_map(
            function (string $id) {
                return \trim($id);
            },
            \explode(',', $_GET['tables'] ?? $_GET['tabulky'] ?? '')
        );
        $wantedTableIds = \array_filter(
            $wantedTableIds,
            function (string $id) {
                return $id !== '';
            }
        );
        $tables = $htmlHelper->findTablesWithIds(new \DrdPlus\FrontendSkeleton\HtmlDocument($rawContent), $wantedTableIds);
        foreach ($tables as $table) {
            $content .= $table->outerHTML . "\n";
        }
        ?>
    </body>
  </html>
<?php
$content .= \ob_get_clean();
$tablesCache->cacheContent($content);

return $content;
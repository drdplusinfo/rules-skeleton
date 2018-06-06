<?php
/** @var \DrdPlus\RulesSkeleton\Controller $controller */
\ob_start();
?>
  <!DOCTYPE html>
  <html lang="cs">
    <head>
      <title><?= $controller->getPageTitle() ?></title>
      <link rel="shortcut icon" href="/favicon.ico">
      <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, viewport-fit=cover">
      <link rel="stylesheet" href="/css/generic/skeleton/vendor/bootstrap.v4.0.0-alpha.6/bootstrap.min.css">
      <link rel="stylesheet" href="/css/generic/skeleton/flash-messages.css">
      <link rel="stylesheet" href="/css/generic/skeleton/contacts.css">
      <link rel="stylesheet" href="/css/generic/skeleton/graphics.css">
      <link rel="stylesheet" href="/css/generic/skeleton/rules-skeleton-graphics.css">
      <link rel="stylesheet" href="/css/generic/skeleton/ignore/licence.css">
    </head>
    <body>
      <div class="vertical-centered-wrapper">
        <div class="vertical-centered">
          <div class="horizontal-centered-wrapper">
            <div class="horizontal-centered">
              <div class="content">
                <div class="background-image"></div>
                  <?php
                  $contactsFixed = true; // (default is on top or bottom of the content)
                  // $contactsBottom = true; // (default is top)
                  /** @noinspection PhpIncludeInspection */
                  include $genericPartsRoot . '/menu.php';
                  unset($contactsFixed);
                  if ($controller->getUsagePolicy()->trialJustExpired()) { ?>
                    <div class="message warning">⌛ Čas tvého testování se naplnil ⌛</div><?php
                  } ?>
                <div>
                  <h1>Prohlášení</h1>
                    <?php if (\file_exists($documentRoot . '/name.txt')) {
                        $name = \file_get_contents($documentRoot . '/name.txt');
                    } else {
                        $name = \basename($documentRoot);
                    }
                    $eShop = \trim(\file_get_contents($documentRoot . '/eshop_url.txt'));
                    ?>
                  <form class="manifest trial" action="" method="post">
                    <p>
                      <label>
                        <input type="submit" name="trial" value="Juknu na <?= $name ?>">
                        Chci se na <?= $name ?> jen na chvíli podívat, ať vím, o co jde
                      </label>
                    </p>
                  </form>
                  <form class="manifest" action="<?= $eShop ?>">
                    <p>
                      <label>
                        <input type="submit" name="buy" value="Koupím <?= $name ?>">
                        Zatím nemám <strong><?= $name ?></strong>, tak si je od Altaru
                        <a href="<?= $eShop ?>">koupím (doporučujeme PDF verzi)</a>
                      </label>
                    </p>
                  </form>
                  <form class="manifest owning" action="" method="post"
                        onsubmit="return window.confirm('A klidně to potvrdím dvakrát')">
                    <p>
                      <label>
                        <input type="submit" name="confirm" value="Vlastním <?= $name ?>">
                        Prohlašuji na svou čest, že vlastním
                        legální kopii <a href="<?= $eShop ?>"><strong><?= $name ?></strong></a>
                      </label>
                    </p>
                  </form>
                  <div class="footer">
                    <p>Dračí doupě<span class="upper-index">®</span>, DrD<span class="upper-index">TM</span>
                      a
                      ALTAR<span
                          class="upper-index">®</span> jsou zapsané ochranné známky nakladatelství <a
                          href="http://www.altar.cz/">ALTAR</a>.</p>
                    <p>Hledáš-li živou komunitu kolem RPG, mrkni na <a
                          href="https://rpgforum.cz">rpgforum.cz</a>, nebo rovnou na
                      <a href="https://rpgforum.cz/forum/viewforum.php?f=238">
                        sekci pro DrD+.
                      </a>
                    </p>

                    <div>Pokud nevlastníš pravidla DrD+, prosím, <a href="https://obchod.altar.cz">kup si
                        je</a>
                      - podpoříš autory a budoucnost DrD. Děkujeme!
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </body>
  </html>
<?php $passContent = \ob_get_clean();
unset($name);
$passCache = new \DrdPlus\RulesSkeleton\PassCache($cacheRoot, $webVersions, $htmlHelper->isInProduction());
$passCache->saveContentForDebug($passContent); // for debugging purpose
$passHtmlDocument = new \DrdPlus\FrontendSkeleton\HtmlDocument($passContent);
$htmlHelper = new \DrdPlus\FrontendSkeleton\HtmlHelper($documentRoot, false, false, false, false);
$htmlHelper->addVersionHashToAssets($passHtmlDocument);
if (PHP_SAPI === 'cli' || ($_SERVER['REMOTE_ADDR'] ?? null) === '127.0.0.1') {
    $htmlHelper->makeExternalLinksLocal($passHtmlDocument);
}
$updatedPassContent = $passHtmlDocument->saveHTML();
$passCache->cacheContent($updatedPassContent);

return $updatedPassContent;
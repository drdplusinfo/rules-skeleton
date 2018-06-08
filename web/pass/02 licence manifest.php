<?php
/** @var \DrdPlus\RulesSkeleton\Controller $controller */
?>
<h1>Prohlášení</h1>
<?php
$webName = $controller->getWebName();
$eShopUrl = $controller->getEshopUrl();
?>
<div class="row">
  <form class="manifest trial" action="" method="post">
    <p>
      <label>
        <input type="submit" name="trial" value="Juknu na <?= $webName ?>">
        <span class="col-6">Chci se na <strong><?= $webName ?></strong> jen na chvíli podívat, ať vím, o co jde</span>
      </label>
    </p>
  </form>
</div>
<div class="row">
  <form class="manifest buy" action="<?= $eShopUrl ?>">
    <p>
      <label>
        <input class="col" type="submit" name="buy" value="Koupím <?= $webName ?>">
        <span class="col">Zatím nemám <strong><?= $webName ?></strong>, tak si je od Altaru koupím <span class="note">(doporučujeme PDF verzi)</span></span>
      </label>
    </p>
  </form>
</div>
<div class="row">
  <form class="manifest owning" action="" method="post"
        onsubmit="return window.confirm('A klidně to potvrdím dvakrát')">
    <p>
      <label>
        <input class="col" type="submit" name="confirm" value="Vlastním <?= $webName ?>">
        <span class="col">Prohlašuji na svou čest, že vlastním legální kopii <strong><?= $webName ?></strong></span>
      </label>
    </p>
  </form>
</div>
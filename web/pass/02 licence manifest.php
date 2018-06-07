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
        Chci se na <strong><?= $webName ?></strong> jen na chvíli podívat, ať vím, o co jde
      </label>
    </p>
  </form>
</div>
<div class="row">
  <form class="manifest" action="<?= $eShopUrl ?>">
    <p>
      <label>
        <input type="submit" name="buy" value="Koupím <?= $webName ?>">
        Zatím nemám <strong><?= $webName ?></strong>, tak si je od Altaru
        <a href="<?= $eShopUrl ?>">koupím (doporučujeme PDF verzi)</a>
      </label>
    </p>
  </form>
</div>
<div class="row">
  <form class="manifest owning" action="" method="post"
        onsubmit="return window.confirm('A klidně to potvrdím dvakrát')">
    <p>
      <label>
        <input type="submit" name="confirm" value="Vlastním <?= $webName ?>">
        Prohlašuji na svou čest, že vlastním legální kopii <strong><?= $webName ?></strong>
      </label>
    </p>
  </form>
</div>
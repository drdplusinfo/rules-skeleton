<?php
/** @var \DrdPlus\RulesSkeleton\RulesController $controller */
$webName = $controller->getWebName();
$eShopUrl = $controller->getEshopUrl();
?>
<h1><?= $webName ?></h1>

<h3>Zkusím</h3>
<div class="row">
  <form class="manifest trial" action="/" method="post">
    <div class="col">
      <button type="submit" id="trial" name="trial" value="trial">Juknu na <?= $webName ?></button>
    </div>
    <div class="col">
      <ul>
        <li>
          <label for="trial">
            chci se na <strong><?= $webName ?></strong> jen na chvíli podívat, ať vím, o co jde
          </label>
        </li>
      </ul>
    </div>
  </form>
</div>
<h2>Koupím</h2>
<div class="row">
  <form class="manifest buy" action="<?= $eShopUrl ?>">
    <div class="col">
      <button type="submit" id="buy" name="buy" value="buy">Koupím <?= $webName ?></button>
    </div>
    <div class="col">
      <ul>
        <li>
          <label for="buy">
            zatím nemám <strong><?= $webName ?></strong>, tak si je od Altaru koupím <span class="note">(doporučujeme PDF verzi)</span>
          </label>
        </li>
      </ul>
    </div>
  </form>
</div>
<h3>Mám</h3>
<div class="row">
  <form class="manifest owning" action="/" method="post"
        onsubmit="return window.confirm('A klidně to potvrdím dvakrát')">
    <div class="col">
      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#confirmOwnership">
        Launch demo modal
      </button>
      <p><a data-toggle="modal" href="#confirmOwnership" class="btn btn-primary btn-large">Launch demo modal</a></p>
      <button type="submit" id="confirm" name="confirm" value="submit">Vlastním <?= $webName ?></button>
    </div>
    <div class="col">
      <ul>
        <li>
          <label for="confirm">
            prohlašuji na svou čest, že vlastním legální kopii <strong><?= $webName ?></strong>
          </label>
        </li>
      </ul>
    </div>
  </form>
</div>
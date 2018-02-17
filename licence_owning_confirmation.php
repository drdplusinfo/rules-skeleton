<?php
/** @var \DrdPlus\RulesSkeleton\UsagePolicy $usagePolicy */
if (!empty($_POST['confirm'])) {
    return $usagePolicy->confirmOwnershipOfVisitor(new \DateTime('+1 year'));
}
if (!empty($_POST['trial'])) {
    return $usagePolicy->activateTrial(new \DateTime('+4 minutes'));
}

?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <title>Drd+ <?= basename($documentRoot) ?></title>
    <link rel="shortcut icon" href="favicon.ico">
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="/css/generic/graphics.css">
    <link rel="stylesheet" href="/css/generic/flash_messages.css">
    <link rel="stylesheet" href="/css/generic/ignore/licence.css">
</head>
<body>
<div class="vertical-centered-wrapper">
    <div class="vertical-centered">
        <div class="horizontal-centered-wrapper">
            <div class="horizontal-centered">
                <div class="content">
                    <div class="background-image"></div>
                    <?php if ($usagePolicy->trialJustExpired()) { ?>
                        <div class="message warning">Čas tvého testování se naplnil ⌛</div><?php
                    } ?>
                    <div>
                        <h1>Prohlášení</h1>
                        <?php if (is_readable($documentRoot . '/name.txt')) {
                            $name = file_get_contents($documentRoot . '/name.txt');
                        } else {
                            $name = basename($documentRoot);
                        }
                        $eShop = 'https://obchod.altar.cz';
                        if (is_readable($documentRoot . '/eshop_url.txt')) {
                            $eShop = trim(file_get_contents($documentRoot . '/eshop_url.txt')) ?: $eShop;
                        }
                        ?>
                        <form class="manifest trial" action="" method="post">
                            <p>
                                <label>
                                    <input type="submit" name="trial" value="Mrknu na <?= $name ?>">
                                    Chci se na <?= $name ?> jen na chvíli podívat, ať vím, o co jde (na 4 minuty)
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
                            <p>Dračí doupě<span class="upper-index">®</span>, DrD<span class="upper-index">TM</span> a
                                ALTAR<span
                                        class="upper-index">®</span> jsou zapsané ochranné známky nakladatelství <a
                                        href="http://www.altar.cz/">ALTAR</a>.</p>
                            <p>Hledáš-li živou komunitu kolem RPG, mrkni na <a
                                        href="https://rpgforum.cz">rpgforum.cz</a>, nebo rovnou na
                                <a href="https://rpgforum.cz/forum/viewforum.php?f=238">
                                    sekci pro DrD+.
                                </a>
                            </p>

                            <div>Pokud nevlastníš pravidla DrD+, prosím, <a href="https://obchod.altar.cz">kup si je</a>
                                - podpoříš autory a budoucnost DrD. Děkujeme!
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
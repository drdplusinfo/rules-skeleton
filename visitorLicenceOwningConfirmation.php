<?php
if (!empty($_POST['buy'])) {
    header('Location: http://obchod.altar.cz', true, 302);
    exit;
}
if (!empty($_POST['confirm'])) {
    $usagePolicy->confirmOwnershipOfVisitor();

    return true;
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <title>Drd+ <?= basename($documentRoot) ?></title>
    <!--suppress HtmlUnknownTarget -->
    <link rel="shortcut icon" href="favicon.ico">
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="css/generic/visitor_licence_owning_confirmation.css">
</head>
<body>
<div class="vertical-centered-wrapper">
    <div class="vertical-centered">
        <div class="horizontal-centered-wrapper">
            <div class="horizontal-centered">
                <div class="content">
                    <div>
                        <h1>Prohlášení</h1>
                        <form action="" method="post">
                            <?php if (is_readable($documentRoot . '/name.txt')) {
                                $name = file_get_contents($documentRoot . '/name.txt');
                            } else {
                                $name = basename($documentRoot);
                            } ?>
                            <p>
                                <label>
                                    <input type="submit" name="buy" value="Koupím <?= $name ?>">
                                    Zatím nemám <strong><?= $name ?></strong>, tak si je od Altaru koupím (doporučujeme
                                    PDF verzi)
                                </label>
                            </p>
                            <p>
                                <label>
                                    <input type="submit" name="confirm" value="Vlastním <?= $name ?>"
                                           onclick="window.confirm('A klidně to potvrdím dvakrát')">
                                    Prohlašuji na svou čest, že vlastním
                                    legální kopii <strong><?= $name ?></strong>
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
                                <a href="https://rpgforum.cz/forum/viewforum.php?f=238&sid=a8a110335d3b47d604ad0ab10b630ba4">
                                    sekci pro DrD+
                                </a>.
                            </p>

                            <div>Pokud nevlastníš pravidla DrD+, prosím, <a href="http://obchod.altar.cz">kup si je</a>
                                - podpoříš autory a
                                budoucnost DrD. Děkujeme.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
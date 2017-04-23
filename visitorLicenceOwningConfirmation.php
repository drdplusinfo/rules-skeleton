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
    <style type="text/css">

        body {
            font-size: 20px;
            font-family: "Times New Roman", Times, serif;
        }

        input[type=submit] {
            font-size: inherit;
        }

        .vertical-centered-wrapper {
            position: absolute;
            width: 98%;
            height: 98%;
            display: table;
        }

        .vertical-centered {
            display: table-cell;
            vertical-align: middle;
        }

        .horizontal-centered-wrapper {
            text-align: center;
        }

        .horizontal-centered {
            display: inline-block;
            text-align: left;
        }

        .content {
            padding: 1em;
        }

        h1 {
            font-style: italic;
        }

        .upper-index {
            position: relative;
            top: -0.5em;
            font-size: 80%;
        }

        .footer {
            font-size: 15px;
            margin-top: 2em;
            font-style: italic;
        }

        .horizontal-centered:after {
            content: "";
            display: block;
            position: absolute;
            top: 0;
            left: 0;
            background-image: url(/images/graphics/pph-monochromatic.png);
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            width: 100%;
            height: 100%;
            opacity: 0.2;
            z-index: -1;
    </style>
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
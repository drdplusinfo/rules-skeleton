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

$mailer = new \PHPMailer\PHPMailer\PHPMailer();
if (is_readable('/etc/php/smtp/config.ini')) {
    $smtpConfig = parse_ini_file('/etc/php/smtp/config.ini');
    if ($smtpConfig) {
        $mailer->isSMTP(); // set mailer to SMTP in fact
        $mailer->Host = $smtpConfig['host'];
        $mailer->Port = $smtpConfig['port'];
        $mailer->Username = $smtpConfig['username'];
        $mailer->Password = $smtpConfig['password'];
    }
}
\Tracy\Debugger::setLogger(
    new \DrdPlus\RulesSkeleton\TracyLogger(
        '/var/log/php/tracy',
        'info@drdplus.info',
        new \Tracy\BlueScreen(),
        $mailer,
        \DrdPlus\RulesSkeleton\TracyLogger::INFO // includes deprecated
    )
);
\Tracy\Debugger::enable();

if (array_key_exists('tables', $_GET) || array_key_exists('tabulky', $_GET)) { // we do not require licence confirmation for tables only
    /** @see vendor/drd-plus/rules-html-skeleton/get_tables.php */
    echo include __DIR__ . '/parts/get_tables.php';

    return;
}

if (empty($visitorCanAccessContent)) { // can be defined externally by including script
    $visitorCanAccessContent = false;
    $visitorIsUsingTrial = false;
    $request = new \DrdPlus\RulesSkeleton\Request();
    $visitorCanAccessContent = $isVisitorBot = $request->isVisitorBot();
    if (!$isVisitorBot) {
        $usagePolicy = new \DrdPlus\RulesSkeleton\UsagePolicy(basename($documentRoot));
        $visitorCanAccessContent = $visitorHasConfirmedOwnership = $usagePolicy->hasVisitorConfirmedOwnership();
        if (!$visitorCanAccessContent) {
            $visitorCanAccessContent = $visitorIsUsingTrial = $usagePolicy->isVisitorUsingTrial();
        }
        if (!$visitorCanAccessContent) {
            /** @see vendor/drd-plus/rules-html-skeleton/pass.php */
            require __DIR__ . '/parts/pass.php';
            $visitorCanAccessContent = $visitorHasConfirmedOwnership = $usagePolicy->hasVisitorConfirmedOwnership(); // may changed
            if (!$visitorCanAccessContent) {
                $visitorCanAccessContent = $visitorIsUsingTrial = $usagePolicy->isVisitorUsingTrial(); // may changed
            }
        }
    }
}

if (!$visitorCanAccessContent) {
    return;
}

if ((($_SERVER['QUERY_STRING'] ?? false) === 'pdf' || !file_exists($documentRoot . '/html'))
    && file_exists($documentRoot . '/pdf') && glob($documentRoot . '/pdf/*.pdf')
) {
    /** @see vendor/drd-plus/rules-html-skeleton/get_pdf.php */
    echo include __DIR__ . '/parts/get_pdf.php';

    return;
}

/** @see vendor/drd-plus/rules-html-skeleton/content.php */
echo require __DIR__ . '/parts/content.php';
<?php
if (\array_key_exists('tables', $_GET) || \array_key_exists('tabulky', $_GET)) { // we do not require licence confirmation for tables only
    /** @see vendor/drd-plus/rules-html-skeleton/get_tables.php */
    echo include __DIR__ . '/get_tables.php';
    $versionSwitchMutex->unlock();

    return true; // solved
}

if (empty($visitorCanAccessContent)) { // can be defined externally by including script
    $visitorCanAccessContent = false;
    $visitorIsUsingTrial = false;
    $visitorCanAccessContent = $isVisitorBot = $request->isVisitorBot();
    if (!$isVisitorBot) {
        $usagePolicy = new \DrdPlus\FrontendSkeleton\UsagePolicy(basename($documentRoot));
        $visitorCanAccessContent = $visitorHasConfirmedOwnership = $usagePolicy->hasVisitorConfirmedOwnership();
        if (!$visitorCanAccessContent) {
            $visitorCanAccessContent = $visitorIsUsingTrial = $usagePolicy->isVisitorUsingTrial();
        }
        if (!$visitorCanAccessContent) {
            /** @see vendor/drd-plus/rules-html-skeleton/pass.php */
            echo __DIR__ . '/pass.php';
            $visitorCanAccessContent = $visitorHasConfirmedOwnership = $usagePolicy->hasVisitorConfirmedOwnership(); // may changed
            if (!$visitorCanAccessContent) {
                $visitorCanAccessContent = $visitorIsUsingTrial = $usagePolicy->isVisitorUsingTrial(); // may changed
            }
        }
    }
}

if (!$visitorCanAccessContent) {
    $versionSwitchMutex->unlock();

    header('HTTP/1.0 403 Forbidden');
    echo '403 Forbidden (that does not mean you are doomed, though)';

    return true; // solved
}

if ((($_SERVER['QUERY_STRING'] ?? false) === 'pdf' || !file_exists($documentRoot . '/html'))
    && file_exists($documentRoot . '/pdf') && glob($documentRoot . '/pdf/*.pdf')
) {
    /** @see vendor/drd-plus/rules-html-skeleton/get_pdf.php */
    echo include __DIR__ . '/get_pdf.php';
    $versionSwitchMutex->unlock();

    return true; // solved
}

return null; // not yet solved
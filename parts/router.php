<?php
if (\array_key_exists('tables', $_GET) || \array_key_exists('tabulky', $_GET)) { // we do not require licence confirmation for tables only
    /** @see vendor/drd-plus/rules-html-skeleton/get_tables.php */
    echo include __DIR__ . '/get_tables.php';
    $versionSwitchMutex->unlock();

    return true; // solved
}

if (empty($visitorCanAccessContent)) { // can be defined externally by including script
    $visitorIsUsingTrial = false;
    $visitorCanAccessContent = $request->isVisitorBot();
    if (!$visitorCanAccessContent) {
        $usagePolicy = new \DrdPlus\FrontendSkeleton\UsagePolicy(basename($documentRoot));
        $visitorCanAccessContent = $usagePolicy->hasVisitorConfirmedOwnership();
        if (!$visitorCanAccessContent) {
            $visitorCanAccessContent = $usagePolicy->isVisitorUsingTrial();
        }
        if (!$visitorCanAccessContent) {
            if (!empty($_POST['confirm'])) {
                $visitorCanAccessContent = $usagePolicy->confirmOwnershipOfVisitor(new \DateTime('+1 year'));
            }
            if (!$visitorCanAccessContent && !empty($_POST['trial'])) {
                $visitorCanAccessContent = $usagePolicy->activateTrial(new \DateTime('+4 minutes'));
            }
            if (!$visitorCanAccessContent) {
                if (\file_exists($partsRoot . '/pass.php')) {
                    echo require $partsRoot . '/pass.php';
                } elseif (\file_exists($vendorRoot . '/drd-plus/rules-skeleton/parts/pass.php')) {
                    echo require $documentRoot . '/drd-plus/rules-skeleton/parts/pass.php';
                } else {
                    echo require __DIR__ . '/pass.php';
                }
            }
        }
    }
}

if (!$visitorCanAccessContent) {
    $versionSwitchMutex->unlock();

    return true; // routing solved
}

if ((($_SERVER['QUERY_STRING'] ?? false) === 'pdf' || !\file_exists($documentRoot . '/web'))
    && \file_exists($documentRoot . '/pdf') && \glob($documentRoot . '/pdf/*.pdf')
) {
    if (\file_exists($partsRoot . '/get_pdf.php')) {
        echo include $partsRoot . '/get_pdf.php';
    } else if (\file_exists($vendorRoot . '/drd-plus/rules-skeleton/parts/get_pdf.php')) {
        echo include $vendorRoot . '/drd-plus/rules-skeleton/parts/get_pdf.php';
    } else {
        /** @see vendor/drd-plus/rules-html-skeleton/get_pdf.php */
        echo include __DIR__ . '/get_pdf.php';
    }
    $versionSwitchMutex->unlock();

    return true; // routing solved
}

return null; // routing not yet solved
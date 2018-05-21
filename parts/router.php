<?php
if (\array_key_exists('tables', $_GET) || \array_key_exists('tabulky', $_GET)) { // we do not require licence confirmation for tables only
    if (\file_exists($partsRoot)) {
        echo include $partsRoot . '/get_tables.php';
    } else {
        echo include $vendorRoot . '/drd-plus/rules-skeleton/parts/get_tables.php';
    }

    return true; // routing solved
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
                } else {
                    echo require $documentRoot . '/drd-plus/rules-skeleton/parts/pass.php';
                }
            }
        }
    }
}

if (!$visitorCanAccessContent) {
    return true; // routing solved
}

if ((($_SERVER['QUERY_STRING'] ?? false) === 'pdf' || !\file_exists($documentRoot . '/web'))
    && \file_exists($documentRoot . '/pdf') && \glob($documentRoot . '/pdf/*.pdf')
) {
    if (\file_exists($partsRoot . '/get_pdf.php')) {
        echo include $partsRoot . '/get_pdf.php';
    } else {
        echo include $vendorRoot . '/drd-plus/rules-skeleton/parts/get_pdf.php';
    }

    return true; // routing solved
}

return null; // routing not yet solved
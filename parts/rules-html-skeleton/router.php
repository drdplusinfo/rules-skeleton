<?php
/** @var \DrdPlus\RulesSkeleton\Controller $controller */
if (\array_key_exists('tables', $_GET) || \array_key_exists('tabulky', $_GET)) { // we do not require licence confirmation for tables only
    echo include $genericPartsRoot . '/get_tables.php';

    return true; // routing solved
}

if (empty($visitorCanAccessContent)) { // can be defined externally by including script
    $visitorIsUsingTrial = false;
    $visitorCanAccessContent = $controller->getUsagePolicy()->isVisitorBot();
    if (!$visitorCanAccessContent) {
        $visitorCanAccessContent = $controller->getUsagePolicy()->hasVisitorConfirmedOwnership();
        if (!$visitorCanAccessContent) {
            $visitorCanAccessContent = $controller->getUsagePolicy()->isVisitorUsingTrial();
        }
        if (!$visitorCanAccessContent) {
            if (!empty($_POST['confirm'])) {
                $visitorCanAccessContent = $controller->getUsagePolicy()->confirmOwnershipOfVisitor(new \DateTime('+1 year'));
            }
            if (!$visitorCanAccessContent && !empty($_POST['trial'])) {
                $visitorCanAccessContent = $controller->getUsagePolicy()->activateTrial(new \DateTime('+4 minutes'));
            }
            if (!$visitorCanAccessContent) {
                echo require $genericPartsRoot . '/pass.php';
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
    echo include $genericPartsRoot . '/get_pdf.php';

    return true; // routing solved
}

return null; // routing not yet solved
<?php
/** @var \DrdPlus\RulesSkeleton\Controller $controller */
if (\array_key_exists('tables', $_GET) || \array_key_exists('tabulky', $_GET)) { // we do not require licence confirmation for tables only
    echo include $controller->getGenericPartsRoot() . '/get_tables.php';

    return true; // routing solved
}

if ((($_SERVER['QUERY_STRING'] ?? false) === 'pdf' || !\file_exists($controller->getDocumentRoot() . '/web'))
    && \file_exists($controller->getDocumentRoot() . '/pdf') && \glob($controller->getDocumentRoot() . '/pdf/*.pdf')
) {
    echo include $controller->getGenericPartsRoot() . '/get_pdf.php';

    return true; // routing solved
}

if (empty($visitorCanAccessContent) && !$controller->isFreeAccess()) { // can be defined externally by including script
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
                $controller->setWebRoot(\file_exists($controller->getDocumentRoot() . '/web/pass')
                    ? $controller->getDocumentRoot() . '/web/pass'
                    : $controller->getVendorRoot() . '/drd-plus/rules-skeleton/web/pass'
                );
                $controller->addBodyClass('pass');
            }
        }
    }
}

return false; // routing passed to index
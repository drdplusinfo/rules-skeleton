<?php
$documentRoot = include __DIR__ . '/_bootstrap.php';

$environment = $environment ?? \DrdPlus\RulesSkeleton\Environment::createFromGlobals();
if (PHP_SAPI !== 'cli') {
    \DrdPlus\RulesSkeleton\TracyDebugger::enable($environment->isInProduction());
}
$dirs = $dirs ?? new \DrdPlus\RulesSkeleton\Configurations\Dirs($documentRoot);
$configuration = $configuration ?? \DrdPlus\RulesSkeleton\Configurations\Configuration::createFromYml($dirs);
$htmlHelper = $htmlHelper
    ?? \DrdPlus\RulesSkeleton\HtmlHelper::createFromGlobals($dirs, $environment, $configuration);
$servicesContainer = $servicesContainer ?? new \DrdPlus\RulesSkeleton\ServicesContainer($configuration, $environment, $htmlHelper);

return $rulesApplication ?? new \DrdPlus\RulesSkeleton\RulesApplication($servicesContainer);

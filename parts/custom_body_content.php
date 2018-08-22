<span class="invisible">Just some rules HTML skeleton custom body content</span>

<?php
/** @var \DrdPlus\RulesSkeleton\RulesController $controller */
/** @noinspection PhpIncludeInspection */
echo include $controller->getConfiguration()->getDirs()->getGenericPartsRoot() . '/debug_contacts.html';
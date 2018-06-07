<?php
global $testsConfiguration;
$testsConfiguration = new \DrdPlus\Tests\RulesSkeleton\TestsConfiguration();
$testsConfiguration->setSomeExpectedTableIds(['IAmSoAlone', 'JustSomeTable']);
$testsConfiguration->setExpectedWebName('HTML kostra pro DrDPlus, jakoby pravidla čaroděje');
$testsConfiguration->setExpectedPageTitle('☠️ HTML kostra pro DrDPlus, jakoby pravidla čaroděje');
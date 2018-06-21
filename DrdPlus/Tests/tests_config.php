<?php
global $testsConfiguration;
$testsConfiguration = new \DrdPlus\Tests\RulesSkeleton\TestsConfiguration('https://rules.drdplus.info');
$testsConfiguration->setSomeExpectedTableIds(['IAmSoAlone', 'JustSomeTable']);
$testsConfiguration->setExpectedWebName('HTML kostra pro DrDPlus, jakoby pravidla čaroděje');
$testsConfiguration->setExpectedPageTitle('☠️ HTML kostra pro DrDPlus, jakoby pravidla čaroděje');
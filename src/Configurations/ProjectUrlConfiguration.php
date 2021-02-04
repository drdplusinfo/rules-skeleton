<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Configurations;

interface ProjectUrlConfiguration
{
    public function getPublicUrlPartRegexp(): string;

    public function getPublicToLocalUrlReplacement(): string;
}

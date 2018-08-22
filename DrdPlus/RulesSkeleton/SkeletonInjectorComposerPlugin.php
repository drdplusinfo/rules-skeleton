<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

class SkeletonInjectorComposerPlugin extends \DrdPlus\FrontendSkeleton\SkeletonInjectorComposerPlugin
{
    public const RULES_SKELETON_PACKAGE_NAME = 'drdplus/rules-skeleton';

    protected function isChangedPackageThisOne(string $changedPackageName): bool
    {
        return $changedPackageName === static::RULES_SKELETON_PACKAGE_NAME
            || parent::isChangedPackageThisOne($changedPackageName);
    }

}
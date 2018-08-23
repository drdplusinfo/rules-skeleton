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

    protected function publishSkeletonImages(string $documentRoot): void
    {
        $this->passThrough(
            [
                'rm -f ./images/generic/skeleton/frontend*',
                'rm -f ./images/generic/skeleton/rules*',
                'cp -r ./vendor/drdplus/rules-skeleton/images/generic ./images/'
            ],
            $documentRoot
        );
    }

    protected function publishSkeletonCss(string $documentRoot): void
    {
        $this->passThrough(
            [
                'rm -f ./css/generic/skeleton/frontend*',
                'rm -f ./css/generic/skeleton/rules*',
                'rm -fr ./css/generic/skeleton/vendor/frontend',
                'cp -r ./vendor/drdplus/rules-skeleton/css/generic ./css/',
                'chmod -R g+w ./css/generic/skeleton/vendor/frontend'
            ],
            $documentRoot
        );
    }

    protected function publishSkeletonJs(string $documentRoot): void
    {
        $this->passThrough(
            [
                'rm -f ./js/generic/skeleton/frontend*',
                'rm -f ./js/generic/skeleton/rules*',
                'rm -fr ./js/generic/skeleton/vendor/frontend',
                'cp -r ./vendor/drdplus/rules-skeleton/js/generic ./js/',
                'chmod -R g+w ./js/generic/skeleton/vendor/frontend'
            ],
            $documentRoot
        );
    }


    protected function copyGoogleVerification(string $documentRoot): void
    {
        $this->passThrough(['cp ./vendor/drdplus/rules-skeleton/google8d8724e0c2818dfc.html .'], $documentRoot);
    }

    protected function copyPhpUnitConfig(string $documentRoot): void
    {
        if ($this->shouldSkipFile('phpunit.xml.dist')) {
            $this->io->write('Skipping copy of phpunit.xml.dist');
        } else {
            $this->passThrough(['cp ./vendor/drdplus/rules-skeleton/phpunit.xml.dist .'], $documentRoot);
        }
    }

    protected function copyProjectConfig(string $documentRoot): void
    {
        $this->passThrough(['cp --no-clobber ./vendor/drdplus/rules-skeleton/config.distribution.yml .'], $documentRoot);
    }

}
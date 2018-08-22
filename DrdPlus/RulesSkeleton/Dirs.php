<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

class Dirs extends \DrdPlus\FrontendSkeleton\Dirs
{

    /** @var bool */
    protected $restrictedWebRootActive;

    protected function populateSubRoots(string $documentRoot): void
    {
        parent::populateSubRoots($documentRoot);
        $this->genericPartsRoot = __DIR__ . '/../../parts/rules-skeleton';
        $this->restrictedWebRootActive = true;
    }

    public function isRestrictedWebRootActive(): bool
    {
        return $this->restrictedWebRootActive;
    }

    public function allowAccessToWebFiles(): void
    {
        $this->restrictedWebRootActive = false;
    }

    public function getVersionWebRoot(string $forVersion): string
    {
        if (!$this->restrictedWebRootActive) {
            return parent::getVersionWebRoot($forVersion);
        }

        return \file_exists($this->getVendorRoot() . '/drdplus/rules-skeleton/web/pass')
            ? $this->getVendorRoot() . '/drdplus/rules-skeleton/web/pass'
            : __DIR__ . '/../../web/pass';
    }
}
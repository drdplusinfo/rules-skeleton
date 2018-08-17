<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

class Dirs extends \DrdPlus\FrontendSkeleton\Dirs
{

    /** @var bool */
    private $restrictedWebRootActive;

    protected function populateSubRoots(string $documentRoot): void
    {
        parent::populateSubRoots($documentRoot);
        $this->genericPartsRoot = __DIR__ . '/../../parts/rules-skeleton';
        $this->restrictedWebRootActive = false;
    }

    public function activateRestrictedWebRoot(): void
    {
        $this->restrictedWebRootActive = true;
    }

    public function getVersionWebRoot(string $forVersion): string
    {
        if (!$this->restrictedWebRootActive) {
            return parent::getVersionWebRoot($forVersion);
        }

        return \file_exists($this->getVendorRoot() . '/drd-plus/rules-skeleton/web/pass')
            ? $this->getVendorRoot() . '/drd-plus/rules-skeleton/web/pass'
            : $this->getDocumentRoot() . '/web/pass';
    }
}
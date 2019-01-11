<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

class Dirs extends \Granam\WebContentBuilder\Dirs
{
    /** @var string */
    private $dirForVersions;
    /** @var string */
    private $cacheRoot;
    /** @var string */
    private $pdfRoot;

    public function __construct(string $projectRoot)
    {
        parent::__construct($projectRoot);
        $this->populateSubRoots($projectRoot);
    }

    private function populateSubRoots(string $documentRoot): void
    {
        $this->dirForVersions = $documentRoot . '/versions';
        $this->cacheRoot = $documentRoot . '/cache/' . \PHP_SAPI;
        $this->pdfRoot = $documentRoot . '/pdf';
    }

    public function getDirForVersions(): string
    {
        return $this->dirForVersions;
    }

    public function getCacheRoot(): string
    {
        return $this->cacheRoot;
    }

    public function getVersionRoot(string $forVersion): string
    {
        return $this->getDirForVersions() . '/' . $forVersion;
    }

    public function getVersionWebRoot(string $forVersion): string
    {
        return $this->getVersionRoot($forVersion) . '/web';
    }

    public function getPdfRoot(): string
    {
        return $this->pdfRoot;
    }
}
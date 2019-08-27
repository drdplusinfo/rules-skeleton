<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

class Dirs extends \Granam\WebContentBuilder\Dirs
{
    /** @var string */
    private $cacheRoot;
    /** @var string */
    private $pdfRoot;

    protected function populateSubRoots(string $projectRoot)
    {
        parent::populateSubRoots($projectRoot);
        $this->populateCacheRoot($projectRoot);
        $this->populatePdfRoot($projectRoot);
    }

    protected function populateCacheRoot(string $projectRoot)
    {
        $this->cacheRoot = $projectRoot . '/cache/' . \PHP_SAPI;
    }

    protected function populatePdfRoot(string $projectRoot)
    {
        $this->pdfRoot = $projectRoot . '/pdf';
    }

    public function getCacheRoot(): string
    {
        return $this->cacheRoot;
    }

    public function getPdfRoot(): string
    {
        return $this->pdfRoot;
    }
}
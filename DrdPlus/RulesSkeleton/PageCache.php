<?php
namespace DrdPlus\RulesSkeleton;

class PageCache extends Cache
{
    /** @var string */
    private $cacheRoot;

    /**
     * @param string $documentRoot
     * @throws \RuntimeException
     */
    public function __construct(string $documentRoot)
    {
        parent::__construct($documentRoot);
        $this->cacheRoot = "{$this->getDocumentRoot()}/cache/pages";
        if (!file_exists($this->cacheRoot) && !@mkdir($this->cacheRoot, 0775, true /* recursive */) && !is_dir($this->cacheRoot)) {
            throw new \RuntimeException('Can not create directory for page cache ' . $this->cacheRoot);
        }
    }

    public function pageCacheIsValid(): bool
    {
        return is_readable($this->getPageCacheFileName()) && $this->cachingHasSense();
    }

    private function getPageCacheFileName(): string
    {
        $currentCommitHash = $this->getCurrentCommitHash();
        $currentGetHash = $this->getCurrentGetHash();

        return $this->cacheRoot . "/{$currentCommitHash}_{$currentGetHash}.html";
    }

    public function getCachedPage(): string
    {
        return file_get_contents($this->getPageCacheFileName());
    }

    public function cachePage(string $content)
    {
        if ($this->cachingHasSense()) {
            file_put_contents($this->getPageCacheFileName(), $content);
        }
        $this->clearPagesOldCache();
    }

    private function clearPagesOldCache()
    {
        $currentCommitHash = $this->getCurrentCommitHash();
        $cachingHasSense = $this->cachingHasSense();
        foreach (scandir($this->cacheRoot, SCANDIR_SORT_NONE) as $folder) {
            if (in_array($folder, ['.', '..', '.gitignore'], true)) {
                continue;
            }
            if (!$cachingHasSense || strpos($folder, $currentCommitHash) === false) {
                unlink($this->cacheRoot . '/' . $folder);
            }
        }
    }
}
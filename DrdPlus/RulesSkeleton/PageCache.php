<?php
namespace DrdPlus\RulesSkeleton;

class PageCache extends Cache
{
    /**
     * @param string $documentRoot
     */
    public function __construct(string $documentRoot)
    {
        parent::__construct($documentRoot);
    }

    public function pageCacheIsValid(): bool
    {
        return is_readable($this->getPageCacheFileName()) && $this->cachingHasSense();
    }

    private function getPageCacheFileName(): string
    {
        $currentCommitHash = $this->getCurrentCommitHash();
        $currentGetHash = $this->getCurrentGetHash();

        return $this->getDocumentRoot() . "/cache/pages/{$currentCommitHash}_{$currentGetHash}.html";
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
        if (!is_dir($this->getDocumentRoot() . '/cache/pages')) {
            return;
        }
        $currentCommitHash = $this->getCurrentCommitHash();
        $cachingHasSense = $this->cachingHasSense();
        foreach (scandir($this->getDocumentRoot() . '/cache/pages') as $folder) {
            if (in_array($folder, ['.', '..', '.gitignore'], true)) {
                continue;
            }
            if (!$cachingHasSense || strpos($folder, $currentCommitHash) === false) {
                unlink($this->getDocumentRoot() . '/cache/pages/' . $folder);
            }
        }
    }
}
<?php
namespace DrdPlus\RulesSkeleton;

class PageCache extends Cache
{
    /**
     * @var bool
     */
    private $manifestCacheIsValid;

    /**
     * @param string $documentRoot
     * @param bool $manifestCacheIsValid
     */
    public function __construct(string $documentRoot, bool $manifestCacheIsValid)
    {
        parent::__construct($documentRoot);
        $this->manifestCacheIsValid = $manifestCacheIsValid;
    }

    public function pageCacheIsValid(): bool
    {
        return $this->manifestCacheIsValid && is_readable($this->getPageCacheFileName()) && $this->cachingHasSense();
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
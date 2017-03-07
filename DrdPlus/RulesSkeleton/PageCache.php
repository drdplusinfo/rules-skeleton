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
        return is_readable($this->getPageCacheFileName()) && $this->cachingHasSense() && $this->readyForManifest();
    }

    private function readyForManifest(): bool
    {
        $resource = fopen($this->getPageCacheFileName(), 'rb');
        $content = '';
        $matching['attributes'] = '';
        do {
            $row = fgets($resource);
            $content .= $row;
        } while ($row !== false && !preg_match('~<html(?<attributes>[^>]+)>~', $content, $matching));
        preg_match('~manifest\s*=\s*"(?<manifestUrl>[^"]*)"~', $matching['attributes'], $matching);
        $manifestUrl = $matching['manifestUrl'];

        return ($this->manifestCacheIsValid && $manifestUrl !== '') || (!$this->manifestCacheIsValid && $manifestUrl === '');
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
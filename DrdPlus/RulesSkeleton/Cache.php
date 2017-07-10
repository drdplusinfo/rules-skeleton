<?php
namespace DrdPlus\RulesSkeleton;

use Granam\Strict\Object\StrictObject;

abstract class Cache extends StrictObject
{
    /** @var string */
    private $documentRoot;
    /** @var string */
    private $cacheRoot;

    public function __construct(string $documentRoot)
    {
        $this->documentRoot = $documentRoot;
        $this->cacheRoot = "{$this->getDocumentRoot()}/cache/pages";
        if (!file_exists($this->cacheRoot) && !@mkdir($this->cacheRoot, 0775, true /* recursive */) && !is_dir($this->cacheRoot)) {
            throw new \RuntimeException('Can not create directory for page cache ' . $this->cacheRoot);
        }
    }

    public function inProduction(): bool
    {
        return !empty($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !== '127.0.0.1';
    }

    /**
     * @return string
     */
    private function getDocumentRoot(): string
    {
        return $this->documentRoot;
    }

    private function getCurrentGetHash(): string
    {
        return md5(serialize($_GET));
    }

    private function cachingHasSense(): bool
    {
        return $this->inProduction() || exec('git diff-index HEAD | wc -l') === '0';
    }

    public function cacheIsValid(): bool
    {
        return is_readable($this->getCacheFileName()) && $this->cachingHasSense();
    }

    private function getCacheFileName(): string
    {
        return $this->cacheRoot . "/{$this->getCachePrefix()}_{$this->getCurrentCommitHash()}_{$this->getCurrentGetHash()}_{$this->cachingHasSense()}.html";
    }

    private function getCurrentCommitHash(): string
    {
        $gitHeadFile = trim(preg_replace('~ref:\s*~', '', file_get_contents($this->documentRoot . '/.git/HEAD')));
        $gitHeadFilePath = $this->documentRoot . '/.git/' . $gitHeadFile;
        if (!is_readable($gitHeadFilePath)) {
            throw new \RuntimeException(
                "Could not read $gitHeadFilePath, in that dir are files "
                . implode(',', scandir(dirname($gitHeadFilePath), SCANDIR_SORT_NONE))
            );
        }

        return trim(file_get_contents($gitHeadFilePath));
    }

    abstract protected function getCachePrefix(): string;

    public function getCachedContent(): string
    {
        return file_get_contents($this->getCacheFileName());
    }

    public function cacheContent(string $content)
    {
        if (PHP_SAPI !== 'cli') {
            file_put_contents($this->getCacheFileName(), $content);
            if ($this->cachingHasSense()) {
                $this->clearOldCache();
            }
        }
    }

    private function clearOldCache()
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
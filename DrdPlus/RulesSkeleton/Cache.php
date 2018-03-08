<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\RulesSkeleton;

use Granam\Strict\Object\StrictObject;
use Granam\String\StringTools;

abstract class Cache extends StrictObject
{
    /** @var string */
    private $documentRoot;
    /** @var string */
    private $cacheRoot;

    /**
     * @param string $documentRoot
     * @throws \RuntimeException
     */
    public function __construct(string $documentRoot)
    {
        $this->documentRoot = $documentRoot;
        $this->cacheRoot = "{$this->getDocumentRoot()}/cache/" . (PHP_SAPI === 'cli' ? 'cli' : 'web');
        if (!\file_exists($this->cacheRoot)) {
            if (!@\mkdir($this->cacheRoot, 0775, true /* recursive */) && !\is_dir($this->cacheRoot)) {
                throw new \RuntimeException('Can not create directory for page cache ' . $this->cacheRoot);
            }
            \chmod($this->cacheRoot, 0775); // because mkdir mode does not work...
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
        return \md5(\serialize($_GET));
    }

    /**
     * @return string
     * @throws \DrdPlus\RulesSkeleton\Exceptions\CanNotGetGitStatus
     */
    private function getGitStamp(): string
    {
        if ($this->inProduction()) {
            return 'production';
        }
        // GIT status is same for any working dir, if it sub-dir of GIT project root
        \exec('git status --porcelain', $changedFiles, $return);
        if ($return !== 0) {
            throw new Exceptions\CanNotGetGitStatus(
                'Can not run `git status --porcelain`, got result code ' . $return
            );
        }
        if (\count($changedFiles) === 0) {
            return 'unchanged';
        }
        $allStamp = '';
        foreach ((array)$changedFiles as $changedFile) {
            \preg_match('~^\s*(?<change>\S+)\s+(?<relativeFileName>.+)$~', $changedFile, $matches);
            ['change' => $change, 'relativeFileName' => $changedRelativeFileName] = $matches;
            $changedRelativeFileName = StringTools::octalToUtf8($changedRelativeFileName);
            // double quotes trimmed as files with spaces in name are "double quoted" by GIT status
            $changedFileName = $this->getDocumentRoot() . '/' . \trim($changedRelativeFileName, '"');
            if ($change === 'D') { // file was deleted, so we can not get MD5 of its content
                $allStamp .= '0';
            } else {
                $allStamp .= \md5_file($changedFileName);
            }
        }

        return \md5($allStamp);
    }

    /**
     * @return bool
     * @throws \RuntimeException
     */
    public function cacheIsValid(): bool
    {
        return \is_readable($this->getCacheFileName());
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    private function getCacheFileName(): string
    {
        return $this->cacheRoot . "/{$this->getCacheFileBaseNamePartWithoutGet()}_{$this->getCurrentGetHash()}.html";
    }

    private function getCacheFileBaseNamePartWithoutGet(): string
    {
        return "{$this->getCachePrefix()}_{$this->getCurrentCommitHash()}_{$this->getGitStamp()}";
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    private function getCurrentCommitHash(): string
    {
        $head = \file_get_contents($this->documentRoot . '/.git/HEAD');
        if (\preg_match('~^[[:alnum:]]{40,}$~', $head)) {
            return $head; // the HEAD file contained the has itself
        }
        $gitHeadFile = \trim(\preg_replace('~ref:\s*~', '', \file_get_contents($this->documentRoot . '/.git/HEAD')));
        $gitHeadFilePath = $this->documentRoot . '/.git/' . $gitHeadFile;
        if (!\is_readable($gitHeadFilePath)) {
            throw new \RuntimeException(
                "Could not read $gitHeadFilePath, in that dir are files "
                . \implode(',', \scandir(\dirname($gitHeadFilePath), SCANDIR_SORT_NONE))
            );
        }

        return \trim(\file_get_contents($gitHeadFilePath));
    }

    abstract protected function getCachePrefix(): string;

    /**
     * @return string
     * @throws \RuntimeException
     */
    public function getCachedContent(): string
    {
        return \file_get_contents($this->getCacheFileName());
    }

    /**
     * @param string $content
     * @throws \RuntimeException
     */
    public function saveContentForDebug(string $content): void
    {
        \file_put_contents($this->getCacheDebugFileName(), $content, \LOCK_EX);
        \chmod($this->getCacheDebugFileName(), 0664);
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    private function getCacheDebugFileName(): string
    {
        return $this->cacheRoot . "/{$this->geCacheDebugFileBaseNamePartWithoutGet()}_{$this->getCurrentGetHash()}.html";
    }

    private function geCacheDebugFileBaseNamePartWithoutGet(): string
    {
        return 'debug_' . $this->getCacheFileBaseNamePartWithoutGet();
    }

    /**
     * @param string $content
     * @throws \RuntimeException
     */
    public function cacheContent(string $content): void
    {
        \file_put_contents($this->getCacheFileName(), $content, \LOCK_EX);
        \chmod($this->getCacheFileName(), 0664);
        $this->clearOldCache();
    }

    /**
     * @throws \RuntimeException
     */
    private function clearOldCache(): void
    {
        $foldersToSkip = ['.', '..', '.gitignore'];
        $currentCacheStamp = $this->getCurrentCommitHash();
        foreach (\scandir($this->cacheRoot, SCANDIR_SORT_NONE) as $folder) {
            if (\in_array($folder, $foldersToSkip, true)) {
                continue;
            }
            if (\strpos($folder, $currentCacheStamp) !== false) {
                continue;
            }
            \unlink($this->cacheRoot . '/' . $folder);
        }
    }
}
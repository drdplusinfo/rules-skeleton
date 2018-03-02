<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\RulesSkeleton;

use Granam\Strict\Object\StrictObject;

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
        $this->cacheRoot = "{$this->getDocumentRoot()}/cache/pages";
        if (!\file_exists($this->cacheRoot) && !@\mkdir($this->cacheRoot, 0775, true /* recursive */) && !\is_dir($this->cacheRoot)) {
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
            $changedFileRelativeName = \preg_replace('~^\s*\S+\s+~', '', $changedFile);
            $changedFileName = $this->getDocumentRoot() . '/' . $changedFileRelativeName;
            $allStamp .= \md5_file($changedFileName);
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
        return $this->cacheRoot . "/{$this->getCachePrefix()}_{$this->getCurrentCommitHash()}_{$this->getGitStamp()}_{$this->getCurrentGetHash()}.html";
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
        if (PHP_SAPI !== 'cli') {
            \file_put_contents($this->getDebuggingFileName(), $content);
        }
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    private function getDebuggingFileName(): string
    {
        return $this->cacheRoot . '/debug_' . \basename($this->getCacheFileName());
    }

    /**
     * @param string $content
     * @throws \RuntimeException
     */
    public function cacheContent(string $content): void
    {
        if (PHP_SAPI !== 'cli') {
            \file_put_contents($this->getCacheFileName(), $content);
            $this->clearOldCache();
        }
    }

    /**
     * @throws \RuntimeException
     */
    private function clearOldCache(): void
    {
        $currentCommitHash = $this->getCurrentCommitHash();
        foreach (\scandir($this->cacheRoot, SCANDIR_SORT_NONE) as $folder) {
            if (\in_array($folder, ['.', '..', '.gitignore'], true)) {
                continue;
            }
            if (\strpos($folder, $currentCommitHash) === false) {
                \unlink($this->cacheRoot . '/' . $folder);
            }
        }
    }
}
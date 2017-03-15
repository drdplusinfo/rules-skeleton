<?php
namespace DrdPlus\RulesSkeleton;

use Granam\Strict\Object\StrictObject;

/** @noinspection SingletonFactoryPatternViolationInspection */
abstract class Cache extends StrictObject
{
    /**
     * @var string
     */
    private $documentRoot;

    protected function __construct(string $documentRoot)
    {
        $this->documentRoot = $documentRoot;
    }

    public function inProduction(): bool
    {
        return !empty($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !== '127.0.0.1';
    }

    protected function getCurrentCommitHash(): string
    {
        $gitHeadFile = trim(preg_replace('~ref:\s*~', '', file_get_contents($this->documentRoot . '/.git/HEAD')));

        return trim(file_get_contents($this->documentRoot . '/.git/' . $gitHeadFile));
    }

    /**
     * @return string
     */
    protected function getDocumentRoot(): string
    {
        return $this->documentRoot;
    }

    protected function getCurrentGetHash(): string
    {
        return md5(serialize($_GET));
    }

    protected function cachingHasSense(): bool
    {
        return $this->inProduction() || exec('git diff-index HEAD | wc -l') === '0';
    }
}
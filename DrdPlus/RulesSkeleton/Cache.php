<?php
namespace DrdPlus\RulesSkeleton;

use Granam\Strict\Object\StrictObject;

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

    protected function getServerUrl(): string
    {
        $protocol = 'http';
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $protocol = $_SERVER['HTTP_X_FORWARDED_PROTO'];
        } else if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $protocol = 'https';
        } else if (!empty($_SERVER['REQUES_SCHEME'])) {
            $protocol = $_SERVER['REQUES_SCHEME'];
        }
        if (empty($_SERVER['SERVER_NAME'])) {
            return '';
        }
        $port = 80;
        if (!empty($_SERVER['SERVER_PORT']) && is_numeric($_SERVER['SERVER_PORT'])
            && (int)$_SERVER['SERVER_PORT'] !== 80
        ) {
            $port = (int)$_SERVER['SERVER_PORT'];
        }
        $portString = $port === 80
            ? ''
            : (':' . $port);

        return "{$protocol}://{$_SERVER['SERVER_NAME']}{$portString}";
    }

}
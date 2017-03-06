<?php
namespace DrdPlus\RulesSkeleton;

use Granam\Strict\Object\StrictObject;

class Cache extends StrictObject
{
    /**
     * @var string
     */
    private $documentRoot;

    public function __construct(string $documentRoot)
    {
        $this->documentRoot = $documentRoot;
    }

    public function inProduction(): bool
    {
        return !empty($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !== '127.0.0.1';
    }

    public function pageCacheIsValid(): bool
    {
        return is_readable($this->getPageCacheFileName()) && is_readable($this->getManifestCacheFilename())
            && $this->cachingHasSense();
    }

    public function getPageCacheFileName(): string
    {
        $currentCommitHash = $this->getCurrentCommitHash();
        $currentGetHash = $this->getCurrentGetHash();

        return $this->documentRoot . "/cache/pages/{$currentCommitHash}_{$currentGetHash}.html";
    }

    private function getCurrentCommitHash(): string
    {
        $gitHeadFile = trim(preg_replace('~ref:\s*~', '', file_get_contents($this->documentRoot . '/.git/HEAD')));

        return trim(file_get_contents($this->documentRoot . '/.git/' . $gitHeadFile));
    }

    private function getCurrentGetHash(): string
    {
        return md5(serialize($_GET));
    }

    public function getCachedPage(): string
    {
        return file_get_contents($this->getPageCacheFileName());
    }

    public function cachePage(string $content)
    {
        if ($this->cachingHasSense()) {
            $this->createManifest($content);
            file_put_contents($this->getPageCacheFileName(), $content);
        }
        $this->clearPagesOldCache();
    }

    private function cachingHasSense(): bool
    {
        return $this->inProduction() || exec('git diff-index HEAD | wc -l') === '0';
    }

    private function clearPagesOldCache()
    {
        $currentCommitHash = $this->getCurrentCommitHash();
        $cachingHasSense = $this->cachingHasSense();
        foreach (scandir($this->documentRoot . '/cache/pages') as $folder) {
            if (in_array($folder, ['.', '..', '.gitignore'], true)) {
                continue;
            }
            if (!$cachingHasSense || strpos($folder, $currentCommitHash) === false) {
                unlink($this->documentRoot . '/cache/pages/' . $folder);
            }
        }
    }

    public function getManifestCacheUrl(): string
    {
        return $this->getServerUrl() . '/cache/manifests/' . $this->getManifestCacheVersionHash() . '.appcache';
    }

    private function getServerUrl(): string
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

    private function getManifestCacheFilename(): string
    {
        return $this->documentRoot . '/cache/manifests/' . $this->getManifestCacheVersionHash() . '.appcache';
    }

    private function getManifestCacheVersionHash(): string
    {
        return $this->getCurrentCommitHash() . '_' . $this->getCurrentGetHash();
    }

    private function createManifest(string $pageContent)
    {
        if ($this->cachingHasSense()) {
            $assetsToCache = $this->parseAssetsToCache($pageContent);
            $assetsForManifest = implode("\n", $assetsToCache);
            $manifestCacheVersionHash = $this->getManifestCacheVersionHash();
            $date = date(DATE_ATOM);
            $manifestFilename = $this->getManifestCacheFilename();
            file_put_contents($manifestFilename, <<<MANIFEST
CACHE MANIFEST
# version {$manifestCacheVersionHash} since the {$date}
/
{$assetsForManifest}
MANIFEST
            );
        }
        $this->clearManifestsOldCache();
    }

    private function parseAssetsToCache(string $html): array
    {
        preg_match_all('~<link\s+[^>]*href="(?<links>[^"]+)"[^>]*>~', $html, $matches);
        $assets = $matches['links'];
        preg_match_all('~<img\s+[^>]*src="(?<images>[^"]+)"[^>]*>~', $html, $matches);
        $assets = array_merge($assets, $matches['images']);

        return array_map(
            function (string $asset) {
                return strpos($asset, 'http') === 0
                    ? $asset
                    : ('/' . ltrim($asset, '/'));
            },
            $assets
        );
    }

    private function clearManifestsOldCache()
    {
        $currentCommitHash = $this->getCurrentCommitHash();
        $cachingHasSense = $this->cachingHasSense();
        foreach (scandir($this->documentRoot . '/cache/manifests') as $folder) {
            if (in_array($folder, ['.', '..', '.gitignore'], true)) {
                continue;
            }
            $manifestFilename = $this->documentRoot . '/cache/manifests/' . $folder;
            if (!$cachingHasSense || strpos(file_get_contents($manifestFilename), $currentCommitHash) === false) {
                unlink($manifestFilename);
            }
        }
    }
}
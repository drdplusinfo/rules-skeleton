<?php
namespace DrdPlus\RulesSkeleton;

class ManifestCache extends Cache
{

    public function __construct(string $documentRoot)
    {
        parent::__construct($documentRoot);
    }

    public function getManifestCacheUrl(): string
    {
        return $this->getServerUrl() . '/manifest.appcache.php';
    }

    private function getManifestCacheFilename(): string
    {
        return $this->getDocumentRoot() . '/cache/manifests/' . $this->getManifestCacheVersionHash() . '.appcache';
    }

    private function getManifestCacheVersionHash(): string
    {
        return $this->getCurrentCommitHash() . '_' . $this->getCurrentGetHash();
    }

    public function createManifest(string $pageContent)
    {
        if ($this->cachingHasSense()) {
            $assetsToCache = $this->parseAssetsToCache($pageContent);
            $assetsToCache = array_unique(array_merge($assetsToCache, $this->getGenericAssetsToCache()));
            $assetsForManifest = implode("\n", $assetsToCache);
            $manifestCacheVersionHash = $this->getManifestCacheVersionHash();
            $date = date(DATE_ATOM);
            $manifestFilename = $this->getManifestCacheFilename();
            file_put_contents($manifestFilename, <<<MANIFEST
CACHE MANIFEST
# version {$manifestCacheVersionHash} since the {$date}

CACHE:
{$assetsForManifest}

NETWORK:
* # everything not cached can be fetched from net
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

    private function getGenericAssetsToCache()
    {
        return $this->scanForAssets(__DIR__ . '/../../images', '/images');
    }

    /**
     * @param string $directory
     * @param string $relativeRoot
     * @return array|string[]
     */
    private function scanForAssets(string $directory, string $relativeRoot): array
    {
        $assets = [];
        $relativeRoot = rtrim($relativeRoot, '\/');
        foreach (scandir($directory) as $folder) {
            $folderPath = $directory . '/' . $folder;
            if (is_dir($folderPath)) {
                if ($folder === '.' || $folder === '..' || $folder === '.gitignore') {
                    continue;
                }
                $additionalAssets = $this->scanForAssets(
                    $folderPath,
                    ($relativeRoot !== '' ? ($relativeRoot . '/') : '') . $folder
                );
                foreach ($additionalAssets as $additionalAsset) {
                    $assets[] = $additionalAsset;
                }
            } else if (is_file($folderPath)) {
                $assets[] = ($relativeRoot !== '' ? ($relativeRoot . '/') : '') . $folder; // intentionally relative path
            }
        }

        return $assets;
    }

    public function clearManifestsOldCache()
    {
        $folders = array_filter(
            scandir($this->getDocumentRoot() . '/cache/manifests'),
            function (string $folder) {
                return $folder !== '.' && $folder !== '..' && $folder !== '.gitignore';
            }
        );
        if (count($folders) === 0) {
            return;
        }
        $currentCommitHash = $this->getCurrentCommitHash();
        $cachingHasSense = $this->cachingHasSense();
        foreach ($folders as $folder) {
            $manifestFilename = $this->getDocumentRoot() . '/cache/manifests/' . $folder;
            if (!$cachingHasSense || strpos(file_get_contents($manifestFilename), $currentCommitHash) === false) {
                unlink($manifestFilename);
            }
        }
    }

    public function manifestCacheIsValid(): bool
    {
        $this->clearManifestsOldCache();

        return is_readable($this->getManifestCacheFilename()) && $this->cachingHasSense();
    }

    public function getManifest(string $uriWithContentToCache): string
    {
        $this->createManifestIfNotExists($uriWithContentToCache);
        if (!$this->manifestCacheIsValid()) {
            $versionComment = !empty($_COOKIE['manifestId']) ? $_COOKIE['manifestId'] : microtime();

            return <<<MANIFEST
CACHE MANIFEST
# {$versionComment}
NETWORK:
/
*
MANIFEST;
        }

        return (string)file_get_contents($this->getManifestCacheFilename());
    }

    /**
     * @param string $uriWithContentToCache
     */
    private function createManifestIfNotExists(string $uriWithContentToCache)
    {
        if (!file_exists($this->getManifestCacheFilename()) && $this->cachingHasSense()) {
            $contents = file_get_contents($uriWithContentToCache);
            if ($contents) {
                $this->createManifest($contents);
            }
        }
    }
}
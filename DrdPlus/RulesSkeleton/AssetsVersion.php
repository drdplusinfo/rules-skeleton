<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\RulesSkeleton;

use Granam\Strict\Object\StrictObject;

class AssetsVersion extends StrictObject
{
    /** @var bool */
    private $scanDirsForCss;
    /** @var bool */
    private $scanDirsForHtml;

    public function __construct(bool $scanDirsForCss = null, bool $scanDirsForHtml = null)
    {
        $this->scanDirsForCss = false;
        $this->scanDirsForHtml = false;
        if ($scanDirsForCss === null && $scanDirsForHtml === null) { // default is to can for everything
            $this->scanDirsForCss = true;
            $this->scanDirsForHtml = true;
        } else { // only selected file types will be searched
            $this->scanDirsForCss = $scanDirsForCss ?? false;
            $this->scanDirsForHtml = $scanDirsForHtml ?? false;
        }
    }

    public function addVersionsToAssetLinks(
        string $documentRootDir,
        array $dirsToScan,
        array $excludeDirs,
        array $filesToEdit
    ): int
    {
        $changedFileCount = 0;
        $confirmedFilesToEdit = $this->getConfirmedFilesToEdit($dirsToScan, $excludeDirs, $filesToEdit);
        foreach ($confirmedFilesToEdit as $confirmedFileToEdit) {
            $content = \file_get_contents($confirmedFileToEdit);
            if ($content) { // TODO warning
                $replacedContent = $this->addVersionsToAssetLinksInContent($content, $documentRootDir);
                if ($replacedContent === $content) {
                    continue;
                }
                // TODO warnings
                if (\file_put_contents($confirmedFileToEdit, $replacedContent)) {
                    $changedFileCount++;
                }
            }
        }

        return $changedFileCount;
    }

    private function getConfirmedFilesToEdit(array $dirsToScan, array $excludeDirs, array $filesToEdit): array
    {
        $confirmedFilesToEdit = [];
        $wantedFileExtensions = [];
        if ($this->scanDirsForCss) {
            $wantedFileExtensions[] = 'css';
        }
        if ($this->scanDirsForHtml) {
            $wantedFileExtensions[] = 'html';
        }
        $excludeDirs = $this->unifyFolderNames($excludeDirs);
        $wantedFileExtensionsRegexp = '(' . \implode('|', $wantedFileExtensions) . ')';
        foreach ($dirsToScan as $dirToScan) {
            $directoryIterator = new \RecursiveDirectoryIterator(
                $dirToScan,
                \RecursiveDirectoryIterator::FOLLOW_SYMLINKS
                | \RecursiveDirectoryIterator::SKIP_DOTS
                | \RecursiveDirectoryIterator::UNIX_PATHS
                | \RecursiveDirectoryIterator::KEY_AS_FILENAME
                | \RecursiveDirectoryIterator::CURRENT_AS_SELF
            );
            /** @var \FilesystemIterator $folder */
            foreach (new \RecursiveIteratorIterator($directoryIterator) as $folderName => $folder) {
                $pathName = $folder->getPathname();
                $dirPath = \dirname($pathName);
                foreach ($excludeDirs as $excludeDir) {
                    if ($dirPath === $excludeDir || \strpos($dirPath, $excludeDir . '/') === 0) {
                        continue;
                    }
                }
                if (\preg_match('~[.]' . $wantedFileExtensionsRegexp . '$~', $folderName)) {
                    $confirmedFilesToEdit[] = $pathName;
                }
            }
        }
        foreach ($filesToEdit as $fileToEdit) {
            // TODO warnings
            if (\is_file($fileToEdit) && \is_readable($fileToEdit)) {
                $confirmedFilesToEdit[] = $fileToEdit;
            }
        }

        return \array_unique($confirmedFilesToEdit);
    }

    private function unifyFolderNames(array $folders): array
    {
        return \array_map(function (string $folder) {
            return \rtrim(\str_replace('\\', '/', $folder), '/');
        }, $folders);
    }

    private function addVersionsToAssetLinksInContent(string $content, string $documentRootDir): string
    {
        $srcFound = \preg_match_all('~(?<sources>(?:src="[^"]+"|src=\'[^\']+\'))~', $content, $sourceMatches);
        $urlFound = \preg_match_all('~(?<urls>(?:url\((?:(?<!data:)[^)])+\)|url\("(?:(?<!data:)[^)])+"\)|url\(\'(?:(?!data:)[^)])+\'\)))~', $content, $urlMatches);
        if (!$srcFound && !$urlFound) {
            return $content; // nothing to change
        }
        $stringsWithLinks = \array_merge($sourceMatches['sources'] ?? [], $urlMatches['urls'] ?? []);
        $replacedContent = $content;
        foreach ($stringsWithLinks as $stringWithLink) {
            $maybeQuotedLink = \preg_replace('~src|url\(([^)]+)\)~', '$1', $stringWithLink);
            $link = \trim($maybeQuotedLink, '"\'');
            $md5 = $this->getFileMd5($link, $documentRootDir);
            if (!$md5) {
                continue;
            }
            $versionedLink = $this->appendVersionHashToLink($link, $md5);
            if ($versionedLink === $link) {
                continue; // nothing changed for current link
            }
            $stringWithVersionedLink = \str_replace($link, $versionedLink, $stringWithLink);
            // do NOT replace link directly in content to avoid misleading replacement on places without wrapping url or src
            $replacedContent = \str_replace($stringWithLink, $stringWithVersionedLink, $replacedContent);
        }

        return $replacedContent;
    }

    private function getFileMd5(string $link, string $documentRootDir): ?string
    {
        $parts = \parse_url($link);
        $localPath = $parts['path'] ?? '';
        if ($localPath === '') {
            return null; // TODO warning
        }
        $file = $documentRootDir . '/' . $localPath;
        if (!\is_readable($file)) {
            return null; // TODO warning
        }

        return \md5_file($file);
    }

    private function appendVersionHashToLink(string $link, string $version): string
    {
        $parsed = \parse_url($link);
        $queryString = \urldecode((string)($parsed['query'] ?? ''));
        $queryChunks = explode('&', $queryString);
        $queryParts = [];
        foreach ($queryChunks as $queryChunk) {
            if ($queryChunk === '') {
                continue;
            }
            [$name, $value] = \explode('=', $queryChunk);
            $queryParts[$name] = $value;
        }
        if (!empty($queryParts['version'] && $queryParts['version'] === $version)) {
            return $link; // nothing to change
        }
        $queryParts['version'] = $version;
        $newQueryChunks = [];
        foreach ($queryParts as $name => $value) {
            $newQueryChunks[] = \urlencode($name) . '=' . \urlencode($value);
        }
        $versionedQuery = \implode('&', $newQueryChunks);
        $fragment = '';
        if ((string)($parsed['fragment'] ?? '') !== '') {
            $fragment .= '#' . $parsed['fragment'];
        }
        if ($fragment !== '') {
            $versionedQuery .= '#' . $fragment;
        }
        $withoutQuery = $link;
        $queryStartsAt = \strpos($link, '?');
        if ($queryStartsAt !== false) {
            $withoutQuery = \substr($link, 0, $queryStartsAt);
        }

        return $withoutQuery . '?' . $versionedQuery;
    }
}
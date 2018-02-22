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

    public function addVersionsToAssetLinks(string $documentRootDir, array $dirsToScan, array $filesToEdit)
    {
        $confirmedFilesToEdit = $this->getConfirmedFilesToEdit($dirsToScan, $filesToEdit);
        foreach ($confirmedFilesToEdit as $confirmedFileToEdit) {
            $content = \file_get_contents($confirmedFileToEdit);
            if ($content) { // TODO warning
                $replacedContent = $this->addVersionsToAssetLinksInContent($content, $documentRootDir);
            }
        }
    }

    private function getConfirmedFilesToEdit(array $dirsToScan, array $filesToEdit): array
    {
        $confirmedFilesToEdit = [];
        $wantedFileExtensions = [];
        if ($this->scanDirsForCss) {
            $wantedFileExtensions[] = 'css';
        }
        if ($this->scanDirsForHtml) {
            $wantedFileExtensions[] = 'html';
        }
        $wantedFileExtensionsRegexp = \implode('|', $wantedFileExtensions);
        foreach ($dirsToScan as $dirToScan) {
            $directoryIterator = new \RecursiveDirectoryIterator(
                $dirToScan,
                \RecursiveDirectoryIterator::FOLLOW_SYMLINKS
                | \RecursiveDirectoryIterator::SKIP_DOTS
                | \RecursiveDirectoryIterator::UNIX_PATHS
                | \RecursiveDirectoryIterator::KEY_AS_FILENAME
                | \RecursiveDirectoryIterator::CURRENT_AS_PATHNAME
            );
            /** @var  $folder */
            foreach (new \RecursiveIteratorIterator($directoryIterator) as $folderBaseName => $folderFullPath) {
                if (\preg_match('~' . $wantedFileExtensionsRegexp . '$', $folderBaseName)) {
                    $confirmedFilesToEdit[] = $folderBaseName;
                }
            }
        }
        foreach ($filesToEdit as $fileToEdit) {
            // TODO warnings
            // TODO relative paths
            if (\is_file($fileToEdit) && \is_readable($fileToEdit)) {
                $fileToEditRealPath = \realpath($fileToEdit);
                if ($fileToEditRealPath) {
                    $confirmedFilesToEdit[] = $fileToEditRealPath;
                }
            }
        }

        return $confirmedFilesToEdit;
    }

    private function addVersionsToAssetLinksInContent(string $content, string $documentRootDir): string
    {
        $srcFound = preg_match('~(?<src>(?:src="[^"]+"|src=\'[^\']+\'))~', $content, $srcMatches);
        $urlFound = preg_match('~(?<url>(?:url\([^)]+\)|url\("[^)]+"\)|url\(\'[^)]+\'\)))~', $content, $urlMatches);
        if (!$srcFound && !$urlFound) {
            return $content; // nothing to change
        }
        foreach ($srcMatches as $srcMatch) {

        }
    }
}
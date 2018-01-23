<?php
namespace DrdPlus\RulesSkeleton;

use Granam\Strict\Object\StrictObject;

class CssFiles extends StrictObject implements \IteratorAggregate
{
    /**
     * @var string
     */
    private $dirWithCss;

    public function __construct(string $dirWithCss)
    {
        $this->dirWithCss = rtrim($dirWithCss, '\/');
    }

    /**
     * @return \Iterator
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->getConfirmedStyleSheets());
    }

    /**
     * @return array|string[]
     */
    private function getConfirmedStyleSheets(): array
    {
        return $this->scanForCssFiles($this->dirWithCss);
    }

    /**
     * @param string $directory
     * @param string $cssRelativeRoot
     * @return array|string[]
     */
    private function scanForCssFiles(string $directory, string $cssRelativeRoot = ''): array
    {
        $genericCssFiles = [];
        $nonGenericCssFiles = [];
        $cssRelativeRoot = rtrim($cssRelativeRoot, '\/');
        foreach (scandir($directory, SCANDIR_SORT_NONE) as $folder) {
            $cssFiles = [];
            $folderPath = $directory . '/' . $folder;
            if (is_dir($folderPath)) {
                if ($folder === '.' || $folder === '..' || $folder === '.gitignore' || $folder === 'ignore') {
                    continue;
                }
                $anotherCssFiles = $this->scanForCssFiles(
                    $folderPath,
                    ($cssRelativeRoot !== '' ? ($cssRelativeRoot . '/') : '') . $folder
                );
                foreach ($anotherCssFiles as $anotherCssFile) {
                    $cssFiles[] = $anotherCssFile;
                }
            } elseif (is_file($folderPath) && strpos($folder, '.css') !== false) {
                $cssFiles[] = ($cssRelativeRoot !== '' ? ($cssRelativeRoot . '/') : '') . $folder; // intentionally relative path
            }
            if ($folder === 'generic') {
                foreach ($cssFiles as $cssFile) {
                    $genericCssFiles[] = $cssFile;
                }
            } else {
                foreach ($cssFiles as $cssFile) {
                    $nonGenericCssFiles[] = $cssFile;
                }
            }
        }

        return array_merge($genericCssFiles, $nonGenericCssFiles); // generic CSS files first to allow their overwrite
    }
}
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
        $cssFiles = [];
        $cssRelativeRoot = rtrim($cssRelativeRoot, '\/');
        foreach (scandir($directory, SCANDIR_SORT_NONE) as $folder) {
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
            } else if (is_file($folderPath) && strpos($folder, '.css') !== false) {
                $cssFiles[] = ($cssRelativeRoot !== '' ? ($cssRelativeRoot . '/') : '') . $folder; // intentionally relative path
            }
        }

        return $cssFiles;
    }
}
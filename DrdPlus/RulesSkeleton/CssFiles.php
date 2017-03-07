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
    private function getConfirmedStyleSheets()
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
        foreach (scandir($directory) as $folder) {
            $folderPath = $directory . '/' . $folder;
            if (is_dir($folderPath)) {
                if ($folder === '.' || $folder === '..' || $folder === '.gitignore') {
                    continue;
                }
                $cssFiles = array_merge(
                    $cssFiles,
                    $this->scanForCssFiles(
                        $folderPath,
                        ($cssRelativeRoot !== '' ? ($cssRelativeRoot . '/') : '') . $folder
                    )
                );
            } else if (is_file($folderPath) && strpos($folder, '.css') !== false) {
                $cssFiles[] = ($cssRelativeRoot !== '' ? ($cssRelativeRoot . '/') : '') . $folder; // intentionally relative path
            }
        }

        return $cssFiles;
    }
}
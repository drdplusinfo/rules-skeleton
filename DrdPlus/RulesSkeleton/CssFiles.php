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
        return $this->scanForCss($this->dirWithCss, '');
    }

    /**
     * @param string $directory
     * @param string $cssRelativeRoot
     * @return array|string[]
     */
    private function scanForCss(string $directory, string $cssRelativeRoot): array
    {
        $css = [];
        foreach (scandir($directory) as $folder) {
            $folderPath = $directory . '/' . $folder;
            if (is_dir($folderPath)) {
                if ($folder === '.' || $folder === '..') {
                    continue;
                }
                $css = array_merge(
                    $css,
                    $this->scanForCss(
                        $folderPath,
                        ($cssRelativeRoot !== '' ? ($cssRelativeRoot . '/') : '') . $folder
                    )
                );
            } else if (is_file($folderPath) && strpos($folder, '.css') !== false) {
                $css[] = $cssRelativeRoot . '/' . $folder; // intentionally relative path
            }
        }

        return $css;
    }
}
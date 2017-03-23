<?php
namespace DrdPlus\RulesSkeleton;

use Granam\Strict\Object\StrictObject;

class JsFiles extends StrictObject implements \IteratorAggregate
{
    /**
     * @var string
     */
    private $dirWithJs;

    public function __construct(string $dirWithJs)
    {
        $this->dirWithJs = rtrim($dirWithJs, '\/');
    }

    /**
     * @return \Iterator
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->getConfirmedJavaScripts());
    }

    /**
     * @return array|string[]
     */
    private function getConfirmedJavaScripts()
    {
        return $this->scanForJsFiles($this->dirWithJs);
    }

    /**
     * @param string $directory
     * @param string $jsRelativeRoot
     * @return array|string[]
     */
    private function scanForJsFiles(string $directory, string $jsRelativeRoot = ''): array
    {
        $jsFiles = [];
        $jsRelativeRoot = rtrim($jsRelativeRoot, '\/');
        foreach (scandir($directory) as $folder) {
            $folderPath = $directory . '/' . $folder;
            if (is_dir($folderPath)) {
                if ($folder === '.' || $folder === '..' || $folder === '.gitignore') {
                    continue;
                }
                $jsFiles = array_merge(
                    $jsFiles,
                    $this->scanForJsFiles(
                        $folderPath,
                        ($jsRelativeRoot !== '' ? ($jsRelativeRoot . '/') : '') . $folder
                    )
                );
            } else if (is_file($folderPath) && strpos($folder, '.js') !== false) {
                $jsFiles[] = ($jsRelativeRoot !== '' ? ($jsRelativeRoot . '/') : '') . $folder; // intentionally relative path
            }
        }

        return $jsFiles;
    }
}
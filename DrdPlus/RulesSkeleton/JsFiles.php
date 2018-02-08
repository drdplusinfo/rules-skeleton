<?php
namespace DrdPlus\RulesSkeleton;

class JsFiles extends AbstractPublicFiles
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
    private function getConfirmedJavaScripts(): array
    {
        $jsFiles = $this->scanForJsFiles($this->dirWithJs);

        return $this->addHashesToFileNames($jsFiles, $this->dirWithJs);
    }

    /**
     * @param string $directory
     * @param string $jsRelativeRoot
     * @return array|string[]
     */
    private function scanForJsFiles(string $directory, string $jsRelativeRoot = ''): array
    {
        if (!is_dir($directory)) {
            return [];
        }
        $genericJsFiles = [];
        $jsFiles = [];
        $jsRelativeRoot = \rtrim($jsRelativeRoot, '\/');
        foreach (\scandir($directory, \SCANDIR_SORT_NONE) as $folder) {
            $folderPath = $directory . '/' . $folder;
            if (\is_dir($folderPath)) {
                if ($folder === '.' || $folder === '..' || $folder === '.gitignore') {
                    continue;
                }
                $jsFilesFromDir = $this->scanForJsFiles(
                    $folderPath,
                    ($jsRelativeRoot !== '' ? ($jsRelativeRoot . '/') : '') . $folder
                );
                if ($folder === 'generic') {
                    foreach ($jsFilesFromDir as $jsFileFromDir) {
                        $genericJsFiles[] = $jsFileFromDir;
                    }
                } else {
                    foreach ($jsFilesFromDir as $jsFileFromDir) {
                        $jsFiles[] = $jsFileFromDir;
                    }
                }
            } elseif (\is_file($folderPath) && \strpos($folder, '.js') !== false) {
                $jsFiles[] = ($jsRelativeRoot !== '' ? ($jsRelativeRoot . '/') : '') . $folder; // intentionally relative path
            }
        }

        return \array_merge($genericJsFiles, $jsFiles); // generic first
    }
}
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
        $stylesheets = scandir($this->dirWithCss);
        $confirmedStylesheets = array_filter($stylesheets, function ($file) {
            return $file !== '.' && $file !== '..' && strpos($file, '.css') !== false;
        });

        return array_map(
            function ($cssFileBaseName) {
                return $this->dirWithCss . '/' . $cssFileBaseName; // intentionally relative path
            },
            $confirmedStylesheets
        );
    }
}
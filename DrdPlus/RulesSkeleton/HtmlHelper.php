<?php
namespace DrdPlus\RulesSkeleton;

use Granam\Strict\Object\StrictObject;
use Granam\String\StringTools;

class HtmlHelper extends StrictObject
{
    /**
     * @var bool
     */
    private $devMode;
    /**
     * @var bool
     */
    private $shouldHideCovered;

    public function __construct(bool $devMode, bool $shouldHideCovered)
    {
        $this->devMode = $devMode;
        $this->shouldHideCovered = $shouldHideCovered;
    }

    /**
     * @param string $html
     * @return string
     */
    public function prepareCodeLinks(string $html)
    {
        if (!$this->devMode) {
            $html = str_replace('source-code-title', 'hidden', $html);
            $html = preg_replace(
                '~\s*(data-source-code\s*=\s*"[^"]*")\s*~',
                '',
                $html
            );

            return preg_replace_callback(
                '~class\s*=\s*"(?<classes>[^"]*(?:covered-by-code|generic))"~',
                function (array $match) {
                    $classes = preg_split('~\s+~', $match['classes']);
                    $filteredClasses = array_filter($classes, function (string $class) {
                        return !in_array($class, ['covered-by-code', 'generic'], true);
                    });

                    return 'class="' . implode(' ', $filteredClasses) . '"';
                },
                $html
            );
        } else {
            return preg_replace(
                '~<(([[:alnum:]]+)(?:(?!data-source-code)[^>])*)\s+data-source-code\s*="([^"]*)"\s*>((?:(?!</\2>).)*)</\2>~s',
                '<$1>$4 <a class="source-code" href="$3">source code</a></$2>',
                $html
            );
        }
    }

    /**
     * @param string $html
     * @return string
     */
    public function addIdsToTables(string $html)
    {
        $thWithIds = preg_replace(
            '~<((th)(?:(?!id=")[^>])*)>(\s*(Tabulka\s+(?:(?!</\2>|<|\n).)*)(?:(?!</\2>).)*)</\2>~us',
            '<$1 id="$4">$3</$2>',
            $html
        );
        $thWithIdsAndOriginalIds = preg_replace(
            '~(\s+id\s*=\s*"([^"]+)")([^>]*)>~',
            '$1 data-original-id="$2"$3>',
            $thWithIds
        );

        return $thWithIdsAndOriginalIds;
    }

    /**
     * @param string $html
     * @return string
     */
    public function addAnchorsToIds(string $html)
    {
        $withAnchors = preg_replace(
            '~<(([[:alnum:]]+)(?:(?!id=")[^>])*id\s*=\s*"([^"]+)"[^>]*)>((?:(?!</\2>).)+)</\2>~is',
            '<$1><a href="#$3">$4</a></$2>',
            $html
        );
        $withoutDiacritics = preg_replace_callback(
            '~\s+(?:id\s*="|href\s*="#)(?<name>[^"]+)"~',
            function ($matches) {
                return str_replace($matches['name'], StringTools::toConstant($matches['name']), $matches[0]);
            },
            $withAnchors
        );
        $withAnchorsToOriginalIds = preg_replace(
            '~<(([[:alnum:]]+)(?:(?!data-original-id=")[^>])*)data-original-id\s*=\s*"([^"]+)"\s*([^>]*)>((?:(?!</\2>).)+)</\2>~is',
            '<$1 $4><span id="$3" class="invisible-id">#$3</span>$5</$2>',
            $withoutDiacritics
        );

        return $withAnchorsToOriginalIds;
    }

    /**
     * @param string $html
     * @return string
     */
    public function hideCovered(string $html)
    {
        if (!$this->devMode || !$this->shouldHideCovered) {
            return $html;
        }

        $withoutImages = preg_replace(
            '~<img\s+[^>]+>~i',
            '',
            $html
        );

        return preg_replace(
            '~class=\s*"[^"]*(covered-by-code|introduction|quote|generic|note|excluded)[^"]*"~i',
            'class="hidden"',
            $withoutImages
        );
    }
}
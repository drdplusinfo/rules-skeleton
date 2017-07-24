<?php
namespace DrdPlus\RulesSkeleton;

use Granam\Strict\Object\StrictObject;
use Granam\String\StringTools;
use Gt\Dom\Element;
use Gt\Dom\HTMLCollection;
use Gt\Dom\HTMLDocument;

class HtmlHelper extends StrictObject
{
    /** @var bool */
    private $inDevMode;
    /** @var bool */
    private $shouldHideCovered;
    /** @var bool */
    private $showIntroductionOnly;
    /** @var bool */
    private $externalUrlsMarked = false;

    public function __construct(bool $inDevMode, bool $shouldHideCovered, bool $showIntroductionOnly)
    {
        $this->inDevMode = $inDevMode;
        $this->shouldHideCovered = $shouldHideCovered;
        $this->showIntroductionOnly = $showIntroductionOnly;
    }

    /**
     * @param HTMLDocument $html
     */
    public function prepareSourceCodeLinks(HTMLDocument $html)
    {
        if (!$this->inDevMode) {
            foreach ($html->getElementsByClassName('source-code-title') as $withSourceCode) {
                $withSourceCode->className = str_replace('source-code-title', 'hidden', $withSourceCode->className);
                $withSourceCode->removeAttribute('data-source-code');
            }
        } else {
            foreach ($html->getElementsByClassName('source-code-title') as $withSourceCode) {
                $withSourceCode->appendChild($sourceCodeLink = new Element('a', 'source code'));
                $sourceCodeLink->setAttribute('class', 'source-code');
                $sourceCodeLink->setAttribute('href', $withSourceCode->getAttribute('data-source-code'));
            }
        }
    }

    /**
     * @param HTMLDocument $html
     */
    public function addIdsToTablesAndHeadings(HTMLDocument $html)
    {
        $elementNames = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'th'];
        foreach ($elementNames as $elementName) {
            /** @var Element $headerCell */
            foreach ($html->getElementsByTagName($elementName) as $headerCell) {

                if ($headerCell->getAttribute('id')) {
                    continue;
                }
                if ($elementName === 'th' && strpos(trim($headerCell->textContent), 'Tabulka') === false) {
                    continue;
                }
                $id = false;
                /** @var \DOMNode $childNode */
                foreach ($headerCell->childNodes as $childNode) {
                    if ($childNode->nodeType === XML_TEXT_NODE) {
                        $id = trim($childNode->nodeValue);
                        break;
                    }
                }
                if (!$id) {
                    continue;
                }
                $headerCell->setAttribute('id', $id);
            }
        }
    }

    public function replaceDiacriticsFromIds(HTMLDocument $html)
    {
        $this->replaceDiacriticsFromChildrenIds($html->body->children);
    }

    private function replaceDiacriticsFromChildrenIds(HTMLCollection $children)
    {
        foreach ($children as $child) {
            // recursion
            $this->replaceDiacriticsFromChildrenIds($child->children);
            $id = $child->getAttribute('id');
            if (!$id) {
                continue;
            }
            $idWithoutDiacritics = StringTools::toConstant($id);
            if ($idWithoutDiacritics === $id) {
                continue;
            }
            $child->setAttribute('data-original-id', $id);
            $child->setAttribute('id', urlencode($idWithoutDiacritics));
            $child->appendChild($invisibleId = new Element('span'));
            $invisibleId->setAttribute('id', urlencode($id));
            $invisibleId->className = 'invisible-id';
        }
    }

    public function replaceDiacriticsFromAnchorHashes(HTMLDocument $html)
    {
        $this->replaceDiacriticsFromChildrenAnchorHashes($html->getElementsByTagName('a'));
    }

    private function replaceDiacriticsFromChildrenAnchorHashes(\Traversable $children)
    {
        /** @var Element $child */
        foreach ($children as $child) {
            // recursion
            $this->replaceDiacriticsFromChildrenAnchorHashes($child->children);
            $href = $child->getAttribute('href');
            if (!$href) {
                continue;
            }
            $hashPosition = strpos($href, '#');
            if ($hashPosition === false) {
                continue;
            }
            $hash = substr($href, $hashPosition + 1);
            if ($hash === '') {
                continue;
            }
            $hashWithoutDiacritics = StringTools::toConstant($hash);
            if ($hashWithoutDiacritics === $hash) {
                continue;
            }
            $hrefWithoutDiacritics = substr($href, 0, $hashPosition) . '#' . $hashWithoutDiacritics;
            $child->setAttribute('href', $hrefWithoutDiacritics);
        }
    }

    /**
     * @param HTMLDocument $html
     */
    public function addAnchorsToIds(HTMLDocument $html)
    {
        $this->addAnchorsToChildrenWithIds($html->body->children);
    }

    private function addAnchorsToChildrenWithIds(HTMLCollection $children)
    {
        foreach ($children as $child) {
            if ($child->id && $child->getElementsByTagName('a')->length === 0) {
                $innerHtml = $child->innerHTML;
                $child->innerHTML = '';
                $anchorToSelf = new Element('a');
                $child->appendChild($anchorToSelf);
                $anchorToSelf->innerHTML = $innerHtml;
                $anchorToSelf->setAttribute('href', '#' . $child->id);
            }
            // recursion
            $this->addAnchorsToChildrenWithIds($child->children);
        }
    }

    private function containsOnlyTextAndSpans(\DOMNode $element): bool
    {
        if (!$element->hasChildNodes()) {
            return true;
        }
        /** @var \DOMNode $childNode */
        foreach ($element->childNodes as $childNode) {
            if ($childNode->nodeName !== 'span' && $childNode->nodeType !== XML_TEXT_NODE) {
                return false;
            }
            if (!$this->containsOnlyTextAndSpans($childNode)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param HTMLDocument $html
     */
    public function resolveDisplayMode(HTMLDocument $html)
    {
        if (!$this->inDevMode) {
            foreach ($html->getElementsByTagName('body') as $body) {
                $this->removeClassesAboutCodeCoverage($body);
            }

            return;
        }
        if ($this->showIntroductionOnly) {
            foreach ($html->getElementsByTagName('body') as $body) {
                $this->hideNonIntroduction($body);
            }

            return;
        }
        if (!$this->shouldHideCovered) {
            foreach ($html->children as $child) {
                $this->removeClassesAboutCodeCoverage($child);
            }
        }
        $this->removeImages($html->getElementsByTagName('body')[0]);

        $classesToHide = ['covered-by-code', 'introduction', 'quote', 'generic', 'note', 'excluded', 'rules-authors'];
        foreach ($classesToHide as $classToHide) {
            foreach ($html->getElementsByClassName($classToHide) as $nodeToHide) {
                $nodeToHide->className = str_replace($classToHide, 'hidden', $nodeToHide->className);
            }
        }
    }

    private function removeImages(Element $html)
    {
        /** @var Element $child */
        foreach ($html->children as $child) {
            if ($child->nodeName === 'img') {
                $html->removeChild($child);
            } else {
                $this->removeImages($child);
            }
        }
    }

    private function hideNonIntroduction(Element $html)
    {
        do {
            $somethingRemoved = false;
            foreach ($html->children as $child) {
                if (!$child->classList->contains('introduction')) {
                    $html->removeChild($child);
                    $somethingRemoved = true;
                }
                // introduction is expected only as direct descendant of the given element (body)
            }
            // do not know why, but some nodes are simply skipped on first removal so have to remove them again
        } while ($somethingRemoved);
    }

    private function removeClassesAboutCodeCoverage(Element $html)
    {
        $classesToRemove = ['covered-by-code', 'generic', 'excluded'];
        foreach ($html->children as $child) {
            foreach ($classesToRemove as $classToRemove) {
                $child->classList->remove($classToRemove);
            }
            // recursion
            $this->removeClassesAboutCodeCoverage($child);
        }
    }

    /**
     * @param HTMLDocument $html
     * @param array|string[] $requiredIds filter of required tables by their IDs
     * @return array|Element[]
     */
    public function findTablesWithIds(HTMLDocument $html, array $requiredIds = []): array
    {
        $tablesWithIds = [];
        /** @var Element $table */
        foreach ($html->getElementsByTagName('table') as $table) {
            if ($table->id) {
                $tablesWithIds[$table->id] = $table;
                continue;
            }
            $childId = $this->getChildId($table->children);
            if ($childId) {
                $tablesWithIds[$childId] = $table;
            }
        }
        if (count($requiredIds) === 0) {
            return $tablesWithIds;
        }

        return array_intersect_key($tablesWithIds, array_fill_keys($requiredIds, true));
    }

    /**
     * @param HTMLCollection $children
     * @return string|bool
     */
    private function getChildId(HTMLCollection $children)
    {
        foreach ($children as $child) {
            if ($child->id) {
                return $child->id;
            }
            $grandChildId = $this->getChildId($child->children);
            if ($grandChildId !== false) {
                return $grandChildId;
            }
        }

        return false;
    }

    public function markExternalLinksByClass(HTMLDocument $html)
    {
        /** @var Element $anchor */
        foreach ($html->getElementsByTagName('a') as $anchor) {
            if (preg_match('~^(https?:)?//[^#]~', $anchor->getAttribute('href'))) {
                $anchor->classList->add('external-url');
            }
        }
        $this->externalUrlsMarked = true;
    }

    public function externalLinksTargetToBlank(HTMLDocument $html)
    {
        if (!$this->externalUrlsMarked) {
            throw new \LogicException('External links have to marked first, use markExternalLinksByClass method for that');
        }
        /** @var Element $anchor */
        foreach ($html->getElementsByClassName('external-url') as $anchor) {
            if (!$anchor->getAttribute('target')) {
                $anchor->setAttribute('target', '_blank');
            }
        }
    }

    public function injectIframesWithRemoteTables(HTMLDocument $html)
    {
        if (!$this->externalUrlsMarked) {
            throw new \LogicException('External links have to marked first, use markExternalLinksByClass method for that');
        }
        $remoteDrdPlusLinks = [];
        /** @var Element $anchor */
        foreach ($html->getElementsByClassName('external-url') as $anchor) {
            if (!preg_match('~(?:https?:)?//(?<host>[[:alpha:]]+\.drdplus\.info)/[^#]*#(?<tableId>tabulka_\w+)~', $anchor->getAttribute('href'), $matches)) {
                continue;
            }
            $remoteDrdPlusLinks[$matches['host']][] = $matches['tableId'];
        }
        if (count($remoteDrdPlusLinks) === 0) {
            return;
        }
        /** @var Element $body */
        $body = $html->getElementsByTagName('body')[0];
        foreach ($remoteDrdPlusLinks as $remoteDrdPlusHost => $tableIds) {
            $iFrame = $html->createElement('iframe');
            $body->appendChild($iFrame);
            $iFrame->setAttribute('id', $remoteDrdPlusHost); // we will target that iframe via JS by remote host name
            $iFrame->setAttribute('src', "https://{$remoteDrdPlusHost}/?tables=" . htmlspecialchars(implode(',', $tableIds)));
            $iFrame->setAttribute('style', 'display:none');
        }
    }

    /**
     * @param HTMLDocument $html
     */
    public function makeExternalLinksLocal(HTMLDocument $html)
    {
        foreach ($html->getElementsByClassName('external-url') as $anchor) {
            $anchor->setAttribute('href', $this->makeDrdPlusHostLocal($anchor->getAttribute('href')));
        }
        /** @var Element $iFrame */
        foreach ($html->getElementsByTagName('iframe') as $iFrame) {
            $iFrame->setAttribute('src', $this->makeDrdPlusHostLocal($iFrame->getAttribute('src')));
            $iFrame->setAttribute('id', str_replace('drdplus.info', 'drdplus.loc', $iFrame->getAttribute('id')));
        }
    }

    private function makeDrdPlusHostLocal(string $linkWithRemoteDrdPlusHost): string
    {
        return preg_replace('~(?:https?:)?//([[:alpha:]]+)\.drdplus\.info/~', 'http://$1.drdplus.loc/', $linkWithRemoteDrdPlusHost);
    }
}
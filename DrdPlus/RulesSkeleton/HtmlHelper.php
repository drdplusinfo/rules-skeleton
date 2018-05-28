<?php

namespace DrdPlus\RulesSkeleton;

use DrdPlus\FrontendSkeleton\HtmlDocument;

/**
 * @method static HtmlHelper createFromGlobals(string $documentRootDir)
 */
class HtmlHelper extends \DrdPlus\FrontendSkeleton\HtmlHelper
{
    /**
     * @param string $blockName
     * @param HtmlDocument $document
     * @return HtmlDocument
     */
    public function getDocumentWithBlock(string $blockName, HtmlDocument $document): HtmlDocument
    {
        $blockParts = $document->getElementsByClassName('block-' . $blockName);
        $block = '';
        foreach ($blockParts as $blockPart) {
            $block .= $blockPart->outerHTML;
        }
        $documentWithBlock = clone $document;
        $documentWithBlock->body->innerHTML = $block;

        return $documentWithBlock;
    }
}
<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use Granam\WebContentBuilder\HtmlDocument;
use Granam\WebContentBuilder\Web\Content;

class MainContent extends Content
{
    protected function buildHtmlDocument(string $content): HtmlDocument
    {
        $htmlDocument = parent::buildHtmlDocument($content);
        $htmlDocument->body->classList->add('container');

        return $htmlDocument;
    }
}
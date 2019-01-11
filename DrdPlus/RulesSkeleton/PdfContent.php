<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

use DrdPlus\RulesSkeletonWeb\RulesWebContent;
use Granam\WebContentBuilder\HtmlDocument;
use Granam\WebContentBuilder\Web\Body;

class PdfContent extends RulesWebContent
{
    /** @var Body */
    private $body;

    public function __construct(Body $body)
    {
        $this->body = $body;
    }

    public function getValue(): string
    {
        return $this->body->getValue();
    }

    public function getHtmlDocument(): HtmlDocument
    {
        throw new Exceptions\PdfContentDoesNotSupportHtmlFormat('Can not convert PDF to HTML');
    }

}
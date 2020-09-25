<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use DrdPlus\RulesSkeleton\HtmlHelper;
use Granam\Strict\Object\StrictObject;
use Granam\WebContentBuilder\HtmlDocument;

class PassBody extends StrictObject implements RulesBodyInterface
{
    /** @var Pass */
    private $pass;

    public function __construct(Pass $pass)
    {
        $this->pass = $pass;
    }

    public function __toString()
    {
        return $this->getValue();
    }

    public function getValue(): string
    {
        $backgroundImageClass = HtmlHelper::CLASS_BACKGROUND_IMAGE;
        return <<<HTML
<div class="main pass">
  <div class="{$backgroundImageClass}"></div>
  {$this->pass->getValue()}
</div>
HTML;
    }

    public function preProcessDocument(HtmlDocument $htmlDocument): HtmlDocument
    {
        return $htmlDocument;
    }

    public function postProcessDocument(HtmlDocument $htmlDocument): HtmlDocument
    {
        return $htmlDocument;
    }
}
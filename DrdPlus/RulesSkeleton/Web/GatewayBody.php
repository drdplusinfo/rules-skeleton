<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use DrdPlus\RulesSkeleton\HtmlHelper;
use Granam\Strict\Object\StrictObject;
use Granam\WebContentBuilder\HtmlDocument;

class GatewayBody extends StrictObject implements RulesBodyInterface
{
    /** @var Gateway */
    private $gateway;

    public function __construct(Gateway $gateway)
    {
        $this->gateway = $gateway;
    }

    public function __toString()
    {
        return $this->getValue();
    }

    public function getValue(): string
    {
        $backgroundImageClass = HtmlHelper::CLASS_BACKGROUND_IMAGE;
        return <<<HTML
<div class="main gateway">
  <div class="{$backgroundImageClass}"></div>
  {$this->gateway->getValue()}
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
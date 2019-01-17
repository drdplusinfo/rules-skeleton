<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use Granam\WebContentBuilder\Web\Body;

class PassBody extends Body
{
    /** @var Pass */
    private $pass;

    public function __construct(WebFiles $webFiles, Pass $pass)
    {
        $this->pass = $pass;
    }

    public function getValue(): string
    {
        return <<<HTML
<div class="main pass">
  <div class="background-image"></div>
  {$this->pass->getValue()}
</div>
HTML;
    }
}
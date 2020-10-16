<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use DrdPlus\RulesSkeleton\Web\Tools\TablesBodyPostProcessor;
use Granam\Strict\Object\StrictObject;
use Granam\WebContentBuilder\HtmlDocument;

class TablesBody extends StrictObject implements RulesBodyInterface
{

    /** @var RulesMainBody */
    private $rulesMainBody;
    /** @var TablesBodyPostProcessor */
    private $tablesBodyPostProcessor;

    public function __construct(
        RulesMainBody $rulesMainBody,
        TablesBodyPostProcessor $tablesBodyPostProcessor
    )
    {
        $this->rulesMainBody = $rulesMainBody;
        $this->tablesBodyPostProcessor = $tablesBodyPostProcessor;
    }

    public function __toString()
    {
        return $this->getValue();
    }

    public function getValue(): string
    {
        return $this->rulesMainBody->getValue();
    }

    public function preProcessDocument(HtmlDocument $htmlDocument): HtmlDocument
    {
        return $htmlDocument;
    }

    public function postProcessDocument(HtmlDocument $htmlDocument): HtmlDocument
    {
        return $this->tablesBodyPostProcessor->processDocument($htmlDocument);
    }
}
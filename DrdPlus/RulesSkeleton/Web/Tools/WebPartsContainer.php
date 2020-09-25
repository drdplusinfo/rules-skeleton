<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web\Tools;

use DrdPlus\RulesSkeleton\Configurations\Dirs;
use DrdPlus\RulesSkeleton\HtmlHelper;
use DrdPlus\RulesSkeleton\Request;
use DrdPlus\RulesSkeleton\Web\DebugContactsBody;
use DrdPlus\RulesSkeleton\Web\NotFoundBody;
use DrdPlus\RulesSkeleton\Web\Pass;
use DrdPlus\RulesSkeleton\Web\PassBody;
use DrdPlus\RulesSkeleton\Web\PdfBody;
use DrdPlus\RulesSkeleton\Web\RulesMainBody;
use DrdPlus\RulesSkeleton\Web\TablesBody;
use Granam\Strict\Object\StrictObject;

class WebPartsContainer extends StrictObject
{
    /** @var Pass */
    private $pass;
    /** @var Dirs */
    private $dirs;
    /** @var HtmlHelper */
    private $htmlHelper;
    /** @var Request */
    private $request;
    /** @var WebFiles */
    private $webFiles;
    /** @var PassBody */
    private $passBody;
    /** @var DebugContactsBody */
    private $debugContactsBody;
    /** @var PdfBody */
    private $pdfBody;
    /** @var RulesMainBody */
    private $rulesMainBody;
    /** @var RulesMainBodyPreProcessor */
    private $rulesMainBodyPreProcessor;
    /** @var NotFoundBody */
    private $notFoundBody;
    /** @var TablesBody */
    private $tablesBody;

    public function __construct(Pass $pass, WebFiles $webFiles, Dirs $dirs, HtmlHelper $htmlHelper, Request $request)
    {
        $this->pass = $pass;
        $this->dirs = $dirs;
        $this->htmlHelper = $htmlHelper;
        $this->request = $request;
        $this->webFiles = $webFiles;
    }

    public function getPassBody(): PassBody
    {
        if ($this->passBody === null) {
            $this->passBody = new PassBody($this->pass);
        }
        return $this->passBody;
    }

    public function getDebugContactsBody(): DebugContactsBody
    {
        if ($this->debugContactsBody === null) {
            $this->debugContactsBody = new DebugContactsBody();
        }
        return $this->debugContactsBody;
    }

    public function getPdfBody(): PdfBody
    {
        if ($this->pdfBody === null) {
            $this->pdfBody = new PdfBody($this->dirs);
        }
        return $this->pdfBody;
    }

    public function getTablesBody(): TablesBody
    {
        if ($this->tablesBody === null) {
            $this->tablesBody = new TablesBody($this->getRulesMainBody(), $this->htmlHelper, $this->request);
        }
        return $this->tablesBody;
    }

    public function getRulesMainBody(): RulesMainBody
    {
        if ($this->rulesMainBody === null) {
            $this->rulesMainBody = new RulesMainBody(
                $this->webFiles,
                $this,
                $this->getRulesMainBodyPreProcessor(),
                null
            );
        }
        return $this->rulesMainBody;
    }

    protected function getRulesMainBodyPreProcessor(): RulesMainBodyPreProcessor
    {
        if (!$this->rulesMainBodyPreProcessor) {
            $this->rulesMainBodyPreProcessor = new RulesMainBodyPreProcessor();
        }
        return $this->rulesMainBodyPreProcessor;
    }

    public function getNotFoundBody(): NotFoundBody
    {
        if ($this->notFoundBody === null) {
            $this->notFoundBody = new NotFoundBody($this->request, $this->getDebugContactsBody());
        }
        return $this->notFoundBody;
    }

}
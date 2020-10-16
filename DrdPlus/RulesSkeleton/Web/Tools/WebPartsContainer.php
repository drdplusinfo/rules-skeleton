<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web\Tools;

use DrdPlus\RulesSkeleton\Configurations\Dirs;
use DrdPlus\RulesSkeleton\HtmlHelper;
use DrdPlus\RulesSkeleton\Request;
use DrdPlus\RulesSkeleton\Web\DebugContactsBody;
use DrdPlus\RulesSkeleton\Web\NotFoundBody;
use DrdPlus\RulesSkeleton\Web\Gateway;
use DrdPlus\RulesSkeleton\Web\GatewayBody;
use DrdPlus\RulesSkeleton\Web\PdfBody;
use DrdPlus\RulesSkeleton\Web\RulesMainBody;
use DrdPlus\RulesSkeleton\Web\TablesBody;
use Granam\Strict\Object\StrictObject;

class WebPartsContainer extends StrictObject
{
    /** @var Gateway */
    private $pass;
    /** @var Dirs */
    private $dirs;
    /** @var HtmlHelper */
    private $htmlHelper;
    /** @var Request */
    private $request;
    /** @var WebFiles */
    private $webFiles;
    /** @var GatewayBody */
    private $passBody;
    /** @var DebugContactsBody */
    private $debugContactsBody;
    /** @var PdfBody */
    private $pdfBody;
    /** @var RulesMainBody */
    private $rulesMainBody;
    /** @var RulesMainBodyPreProcessor */
    private $rulesMainBodyPreProcessor;
    /** @var TablesBodyPostProcessor */
    private $tablesBodyPostProcessor;
    /** @var NotFoundBody */
    private $notFoundBody;
    /** @var TablesBody */
    private $tablesBody;

    public function __construct(Gateway $pass, WebFiles $webFiles, Dirs $dirs, HtmlHelper $htmlHelper, Request $request)
    {
        $this->pass = $pass;
        $this->dirs = $dirs;
        $this->htmlHelper = $htmlHelper;
        $this->request = $request;
        $this->webFiles = $webFiles;
    }

    public function getPassBody(): GatewayBody
    {
        if ($this->passBody === null) {
            $this->passBody = new GatewayBody($this->pass);
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
            $this->tablesBody = new TablesBody($this->getRulesMainBody(), $this->getTablesBodyPostProcessor());
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
            $this->rulesMainBodyPreProcessor = new RulesMainBodyPreProcessor($this->htmlHelper);
        }
        return $this->rulesMainBodyPreProcessor;
    }

    protected function getTablesBodyPostProcessor(): TablesBodyPostProcessor
    {
        if (!$this->tablesBodyPostProcessor) {
            $this->tablesBodyPostProcessor = new TablesBodyPostProcessor($this->request, $this->htmlHelper);
        }
        return $this->tablesBodyPostProcessor;
    }

    public function getNotFoundBody(): NotFoundBody
    {
        if ($this->notFoundBody === null) {
            $this->notFoundBody = new NotFoundBody($this->request, $this->getDebugContactsBody());
        }
        return $this->notFoundBody;
    }

}
<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

use DeviceDetector\Parser\Bot;
use DrdPlus\RulesSkeleton\Web\DebugContactsBody;
use DrdPlus\RulesSkeleton\Web\EmptyMenu;
use DrdPlus\RulesSkeleton\Web\Head;
use DrdPlus\RulesSkeleton\Web\MainContent;
use DrdPlus\RulesSkeleton\Web\Menu;
use DrdPlus\RulesSkeleton\Web\Pass;
use DrdPlus\RulesSkeleton\Web\PassBody;
use DrdPlus\RulesSkeleton\Web\PdfBody;
use DrdPlus\RulesSkeleton\Web\RulesMainContent;
use DrdPlus\RulesSkeleton\Web\TablesBody;
use DrdPlus\RulesSkeleton\Web\WebFiles;
use DrdPlus\WebVersions\WebVersions;
use Granam\Git\Git;
use Granam\Strict\Object\StrictObject;
use Granam\String\StringTools;
use Granam\WebContentBuilder\Web\Body;
use Granam\WebContentBuilder\Web\CssFiles;
use Granam\WebContentBuilder\Web\HtmlContentInterface;
use Granam\WebContentBuilder\Web\JsFiles;

class ServicesContainer extends StrictObject
{

    /** @var CurrentWebVersion */
    protected $currentWebVersion;
    /** @var WebVersions */
    protected $webVersions;
    /** @var Git */
    protected $git;
    /** @var Configuration */
    protected $configuration;
    /** @var HtmlHelper */
    protected $htmlHelper;
    /** @var WebCache */
    protected $webCache;
    /** @var Head */
    protected $head;
    /** @var Menu */
    protected $menu;
    /** @var Body */
    protected $body;
    /** @var TablesBody */
    protected $tablesBody;
    /** @var Cache */
    private $tablesWebCache;
    /** @var CssFiles */
    protected $cssFiles;
    /** @var JsFiles */
    protected $jsFiles;
    /** @var WebFiles */
    protected $webFiles;
    /** @var Request */
    protected $request;
    /** @var Bot */
    protected $botParser;
    /** @var RulesMainContent */
    protected $rulesWebContent;
    /** @var RulesMainContent */
    protected $rulesTablesWebContent;
    /** @var HtmlContentInterface */
    protected $rulesPdfWebContent;
    /** @var RulesMainContent */
    protected $rulesPassWebContent;
    /** @var CookiesService */
    private $cookiesService;
    /** @var \DateTimeImmutable */
    private $now;
    /** @var WebCache */
    private $passWebCache;
    /** @var WebCache */
    private $passedWebCache;
    /** @var UsagePolicy */
    private $usagePolicy;
    /** @var Pass */
    private $pass;
    /** @var PassBody */
    private $passBody;
    /** @var DebugContactsBody */
    private $debugContactsBody;
    /** @var PdfBody */
    private $pdfBody;

    public function __construct(Configuration $configuration, HtmlHelper $htmlHelper)
    {
        $this->configuration = $configuration;
        $this->htmlHelper = $htmlHelper;
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getCurrentWebVersion(): CurrentWebVersion
    {
        if ($this->currentWebVersion === null) {
            $this->currentWebVersion = new CurrentWebVersion(
                $this->getDirs(),
                $this->getGit(),
                $this->getWebVersions()
            );
        }

        return $this->currentWebVersion;
    }

    public function getWebVersions(): WebVersions
    {
        if ($this->webVersions === null) {
            $this->webVersions = new WebVersions($this->getGit(), $this->getDirs()->getProjectRoot());
        }

        return $this->webVersions;
    }

    public function getRequest(): Request
    {
        if ($this->request === null) {
            $this->request = new Request($this->getBotParser());
        }

        return $this->request;
    }

    public function getGit(): Git
    {
        if ($this->git === null) {
            $this->git = new Git();
        }

        return $this->git;
    }

    public function getBotParser(): Bot
    {
        if ($this->botParser === null) {
            $this->botParser = new Bot();
        }

        return $this->botParser;
    }

    public function getRulesWebContent(): RulesMainContent
    {
        if ($this->rulesWebContent === null) {
            $this->rulesWebContent = new RulesMainContent(
                $this->getConfiguration(),
                $this->getHtmlHelper(),
                $this->getHead(),
                $this->getBody(),
                $this->getDebugContactsBody()
            );
        }

        return $this->rulesWebContent;
    }

    public function getRulesTablesWebContent(): MainContent
    {
        if ($this->rulesTablesWebContent === null) {
            $this->rulesTablesWebContent = new MainContent(
                $this->getHtmlHelper(),
                $this->getHeadForTables(),
                $this->getTablesBody()
            );
        }

        return $this->rulesTablesWebContent;
    }

    public function getRulesPdfWebContent(): HtmlContentInterface
    {
        if ($this->rulesPdfWebContent === null) {
            $this->rulesPdfWebContent = new PdfContent($this->getPdfBody());
        }

        return $this->rulesPdfWebContent;
    }

    public function getRulesPassWebContent(): MainContent
    {
        if ($this->rulesPassWebContent === null) {
            $this->rulesPassWebContent = new MainContent(
                $this->getHtmlHelper(),
                $this->getHead(),
                $this->getPassBody()
            );
        }

        return $this->rulesPassWebContent;
    }

    public function getHtmlHelper(): HtmlHelper
    {
        return $this->htmlHelper;
    }

    public function getWebCache(): WebCache
    {
        if ($this->webCache === null) {
            $this->webCache = new WebCache(
                $this->getCurrentWebVersion(),
                $this->getConfiguration()->getDirs(),
                $this->getRequest(),
                $this->getGit(),
                $this->getHtmlHelper()->isInProduction()
            );
        }

        return $this->webCache;
    }

    public function getMenu(): Menu
    {
        if ($this->menu === null) {
            $this->menu = new Menu($this->getConfiguration(), $this->getWebVersions(), $this->getCurrentWebVersion(), $this->getRequest());
        }

        return $this->menu;
    }

    public function getHead(): Head
    {
        if ($this->head === null) {
            $this->head = new Head($this->getConfiguration(), $this->getHtmlHelper(), $this->getCssFiles(), $this->getJsFiles());
        }

        return $this->head;
    }

    public function getBody(): Body
    {
        if ($this->body === null) {
            $this->body = new Body($this->getWebFiles());
        }

        return $this->body;
    }

    public function getHeadForTables(): Head
    {
        return new Head(
            $this->getConfiguration(),
            $this->getHtmlHelper(),
            $this->getCssFiles(),
            $this->getJsFiles(),
            'Tabulky pro ' . $this->getHead()->getPageTitle()
        );
    }

    public function getTablesBody(): TablesBody
    {
        if ($this->tablesBody === null) {
            $this->tablesBody = new TablesBody($this->getWebFiles(), $this->getHtmlHelper(), $this->getRequest());
        }

        return $this->tablesBody;
    }

    public function getTablesWebCache(): Cache
    {
        if ($this->tablesWebCache === null) {
            $this->tablesWebCache = new Cache(
                $this->getCurrentWebVersion(),
                $this->getDirs(),
                $this->getRequest(),
                $this->getGit(),
                $this->getHtmlHelper()->isInProduction(),
                Cache::TABLES
            );
        }

        return $this->tablesWebCache;
    }

    public function getCssFiles(): CssFiles
    {
        if ($this->cssFiles === null) {
            $this->cssFiles = new CssFiles($this->getDirs(), $this->getHtmlHelper()->isInProduction());
        }

        return $this->cssFiles;
    }

    public function getJsFiles(): JsFiles
    {
        if ($this->jsFiles === null) {
            $this->jsFiles = new JsFiles($this->getConfiguration()->getDirs(), $this->getHtmlHelper()->isInProduction());
        }

        return $this->jsFiles;
    }

    public function getDirs(): Dirs
    {
        return $this->getConfiguration()->getDirs();
    }

    public function getWebFiles(): WebFiles
    {
        if ($this->webFiles === null) {
            $this->webFiles = new WebFiles($this->getDirs());
        }

        return $this->webFiles;
    }

    public function getCookiesService(): CookiesService
    {
        if ($this->cookiesService === null) {
            $this->cookiesService = new CookiesService();
        }

        return $this->cookiesService;
    }

    public function getNow(): \DateTimeImmutable
    {
        if ($this->now === null) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->now = new \DateTimeImmutable();
        }

        return $this->now;
    }

    public function getPassWebCache(): WebCache
    {
        if ($this->passWebCache === null) {
            $this->passWebCache = new WebCache(
                $this->getCurrentWebVersion(),
                $this->getDirs(),
                $this->getRequest(),
                $this->getGit(),
                $this->getHtmlHelper()->isInProduction(),
                'pass'
            );
        }

        return $this->passWebCache;
    }

    public function getPassedWebCache(): WebCache
    {
        if ($this->passedWebCache === null) {
            $this->passedWebCache = new WebCache(
                $this->getCurrentWebVersion(),
                $this->getDirs(),
                $this->getRequest(),
                $this->getGit(),
                $this->getHtmlHelper()->isInProduction(),
                'passed'
            );
        }

        return $this->passedWebCache;
    }

    public function getPassBody(): PassBody
    {
        if ($this->passBody === null) {
            $this->passBody = new PassBody($this->getPass());
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

    public function getPass(): Pass
    {
        if ($this->pass === null) {
            $this->pass = new Pass($this->getConfiguration(), $this->getUsagePolicy());
        }

        return $this->pass;
    }

    public function getUsagePolicy(): UsagePolicy
    {
        if ($this->usagePolicy === null) {
            $this->usagePolicy = new UsagePolicy(
                StringTools::toVariableName($this->getConfiguration()->getWebName()),
                $this->getRequest(),
                $this->getCookiesService()
            );
        }

        return $this->usagePolicy;
    }

    public function getPdfBody(): PdfBody
    {
        if ($this->pdfBody === null) {
            $this->pdfBody = new PdfBody($this->getDirs());
        }

        return $this->pdfBody;
    }

    public function getEmptyMenu(): EmptyMenu
    {
        return new EmptyMenu(
            $this->getConfiguration(),
            $this->getWebVersions(),
            $this->getCurrentWebVersion(),
            $this->getRequest()
        );
    }

    public function getDummyWebCache(): DummyWebCache
    {
        return new DummyWebCache(
            $this->getCurrentWebVersion(),
            $this->getDirs(),
            $this->getRequest(),
            $this->getGit(),
            $this->getHtmlHelper()->isInProduction(),
            'empty'
        );
    }
}
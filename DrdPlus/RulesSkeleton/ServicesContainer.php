<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

use DeviceDetector\Parser\Bot;
use DrdPlus\RulesSkeleton\Cache\Cache;
use DrdPlus\RulesSkeleton\Cache\CacheCleaner;
use DrdPlus\RulesSkeleton\Cache\ContentIrrelevantParametersFilter;
use DrdPlus\RulesSkeleton\Cache\ContentIrrelevantRequestAlias;
use DrdPlus\RulesSkeleton\Cache\ContentIrrelevantRequestAliases;
use DrdPlus\RulesSkeleton\Cache\DummyWebCache;
use DrdPlus\RulesSkeleton\Cache\RequestCachingPermissionProvider;
use DrdPlus\RulesSkeleton\Cache\RequestHashProvider;
use DrdPlus\RulesSkeleton\Cache\RouterCacheDirProvider;
use DrdPlus\RulesSkeleton\Cache\WebCache;
use DrdPlus\RulesSkeleton\Configurations\Configuration;
use DrdPlus\RulesSkeleton\Configurations\Dirs;
use DrdPlus\RulesSkeleton\Configurations\RoutedDirs;
use DrdPlus\RulesSkeleton\Web\Gateway\GatewayContent;
use DrdPlus\RulesSkeleton\Web\Head;
use DrdPlus\RulesSkeleton\Web\Main\MainContent;
use DrdPlus\RulesSkeleton\Web\Menu\EmptyMenuBody;
use DrdPlus\RulesSkeleton\Web\Menu\MenuBody;
use DrdPlus\RulesSkeleton\Web\NotFound\NotFoundContent;
use DrdPlus\RulesSkeleton\Web\PdfContent;
use DrdPlus\RulesSkeleton\Web\Tables\TablesContent;
use DrdPlus\RulesSkeleton\Web\Tools\WebFiles;
use DrdPlus\RulesSkeleton\Web\Tools\WebPartsContainer;
use DrdPlus\RulesSkeleton\Web\Tools\WebRootProvider;
use DrdPlus\WebVersions\WebVersions;
use Granam\Git\Git;
use Granam\Strict\Object\StrictObject;
use Granam\String\StringTools;
use Granam\WebContentBuilder\Web\CssFiles;
use Granam\WebContentBuilder\Web\HtmlContentInterface;
use Granam\WebContentBuilder\Web\JsFiles;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;

class ServicesContainer extends StrictObject
{

    /** @var CurrentWebVersion */
    private $currentWebVersion;
    /** @var WebVersions */
    private $webVersions;
    /** @var Git */
    private $git;
    /** @var Configuration */
    private $configuration;
    /** @var HomepageDetector */
    private $homepageDetector;
    /** @var Ticket */
    private $ticket;
    /** @var HtmlHelper */
    private $htmlHelper;
    /** @var Head */
    private $head;
    /** @var MenuBody */
    private $gatewayMenuBody;
    /** @var MenuBody */
    private $passedMenuBody;
    /** @var EmptyMenuBody */
    private $emptyMenuBody;
    /** @var Cache */
    private $tablesWebCache;
    /** @var CssFiles */
    private $cssFiles;
    /** @var JsFiles */
    private $jsFiles;
    /** @var WebFiles */
    private $routedWebFiles;
    /** @var WebFiles */
    private $rootWebFiles;
    /** @var WebRootProvider */
    private $routedWebRootProvider;
    /** @var WebRootProvider */
    private $rootWebRootProvider;
    /** @var RouteMatchingPathProvider */
    private $pathProvider;
    /** @var Request */
    private $request;
    /** @var Environment */
    private $environment;
    /** @var ContentIrrelevantRequestAliases */
    private $contentIrrelevantRequestAliases;
    /** @var ContentIrrelevantParametersFilter */
    private $contentIrrelevantParametersFilter;
    /** @var Bot */
    private $botParser;
    /** @var WebPartsContainer */
    private $rootWebPartsContainer;
    /** @var WebPartsContainer */
    private $routedWebPartsContainer;
    /** @var MainContent */
    private $rulesMainContent;
    /** @var MainContent */
    private $tablesMainContent;
    /** @var HtmlContentInterface */
    private $rulesPdfWebContent;
    /** @var GatewayContent */
    private $gatewayContent;
    /** @var NotFoundContent */
    private $notFoundContent;
    /** @var CookiesService */
    private $cookiesService;
    /** @var \DateTimeImmutable */
    private $now;
    /** @var CacheCleaner */
    private $cacheCleaner;
    /** @var Cache */
    private $gatewayWebCache;
    /** @var Cache */
    private $passedWebCache;
    /** @var RouterCacheDirProvider */
    private $routerCacheDirProvider;
    /** @var WebCache */
    private $routerCache;
    /** @var RequestCachingPermissionProvider */
    private $requestCachingPermissionProvider;
    /** @var RequestHashProvider */
    private $requestHashProvider;
    /** @var YamlFileLoader */
    private $projectRootFileLocator;
    /** @var Cache */
    private $notFoundCache;
    /** @var UsagePolicy */
    private $usagePolicy;
    /** @var RulesUrlMatcher */
    private $rulesUrlMatcher;
    /** @var TablesRequestDetector */
    private $tablesRequestDetector;

    public function __construct(Configuration $configuration, Environment $environment, HtmlHelper $htmlHelper)
    {
        $this->configuration = $configuration;
        $this->environment = $environment;
        $this->htmlHelper = $htmlHelper;
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getHomepageDetector(): HomepageDetector
    {
        if ($this->homepageDetector === null) {
            $this->homepageDetector = new HomepageDetector($this->getPathProvider());
        }
        return $this->homepageDetector;
    }

    public function getTicket(): Ticket
    {
        if ($this->ticket === null) {
            $this->ticket = new Ticket(
                $this->getConfiguration()->getGatewayConfiguration(),
                $this->getUsagePolicy()
            );
        }
        return $this->ticket;
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
            $this->request = Request::createFromGlobals($this->getBotParser(), $this->getEnvironment());
        }
        return $this->request;
    }

    public function getEnvironment(): Environment
    {
        return $this->environment;
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

    public function getRulesMainContent(): MainContent
    {
        if ($this->rulesMainContent === null) {
            $this->rulesMainContent = new MainContent(
                $this->getHtmlHelper(),
                $this->getEnvironment(),
                $this->getHead(),
                $this->getRoutedWebPartsContainer()->getRulesMainBody()
            );
        }
        return $this->rulesMainContent;
    }

    public function getRoutedWebPartsContainer(): WebPartsContainer
    {
        if ($this->routedWebPartsContainer === null) {
            $this->routedWebPartsContainer = new WebPartsContainer(
                $this->getConfiguration(),
                $this->getUsagePolicy(),
                $this->getRoutedWebFiles(),
                $this->getDirs(),
                $this->getHtmlHelper(),
                $this->getRequest()
            );
        }
        return $this->routedWebPartsContainer;
    }

    public function getTablesContent(): TablesContent
    {
        if ($this->tablesMainContent === null) {
            $this->tablesMainContent = new TablesContent(
                $this->getHtmlHelper(),
                $this->getEnvironment(),
                $this->getHeadForTables(),
                $this->getRootWebPartsContainer()->getTablesBody()
            );
        }
        return $this->tablesMainContent;
    }

    public function getRootWebPartsContainer(): WebPartsContainer
    {
        if ($this->rootWebPartsContainer === null) {
            $this->rootWebPartsContainer = new WebPartsContainer(
                $this->getConfiguration(),
                $this->getUsagePolicy(),
                $this->getRootWebFiles(),
                $this->getDirs(),
                $this->getHtmlHelper(),
                $this->getRequest()
            );
        }
        return $this->rootWebPartsContainer;
    }

    public function getPdfContent(): PdfContent
    {
        if ($this->rulesPdfWebContent === null) {
            $this->rulesPdfWebContent = new PdfContent($this->getRoutedWebPartsContainer()->getPdfBody());
        }
        return $this->rulesPdfWebContent;
    }

    public function getGatewayContent(): GatewayContent
    {
        if ($this->gatewayContent === null) {
            $this->gatewayContent = new GatewayContent(
                $this->getHtmlHelper(),
                $this->getEnvironment(),
                $this->getHead(),
                $this->getRoutedWebPartsContainer()->getGatewayBody()
            );
        }
        return $this->gatewayContent;
    }

    public function getNotFoundContent(): NotFoundContent
    {
        if ($this->notFoundContent === null) {
            $this->notFoundContent = new NotFoundContent(
                $this->getHtmlHelper(),
                $this->getEnvironment(),
                $this->getHead(),
                $this->getRoutedWebPartsContainer()->getNotFoundBody()
            );
        }
        return $this->notFoundContent;
    }

    public function getHtmlHelper(): HtmlHelper
    {
        return $this->htmlHelper;
    }

    public function getGatewayMenuBody(): MenuBody
    {
        if ($this->gatewayMenuBody === null) {
            $this->gatewayMenuBody = new MenuBody(
                $this->getConfiguration()->getMenuConfiguration(),
                $this->getHomepageDetector(),
                $this->getTicket()
            );
        }
        return $this->gatewayMenuBody;
    }

    public function getPassedMenuBody(): MenuBody
    {
        if ($this->passedMenuBody === null) {
            $this->passedMenuBody = new MenuBody(
                $this->getConfiguration()->getMenuConfiguration(),
                $this->getHomepageDetector(),
                $this->getTicket()
            );
        }
        return $this->passedMenuBody;
    }

    public function getHead(): Head
    {
        if ($this->head === null) {
            $this->head = new Head(
                $this->getConfiguration(),
                $this->getHtmlHelper(),
                $this->getEnvironment(),
                $this->getCssFiles(),
                $this->getJsFiles()
            );
        }
        return $this->head;
    }

    public function getHeadForTables(): Head
    {
        return new Head(
            $this->getConfiguration(),
            $this->getHtmlHelper(),
            $this->getEnvironment(),
            $this->getCssFiles(),
            $this->getJsFiles(),
            'Tabulky pro ' . $this->getHead()->getPageTitle()
        );
    }

    public function getContentIrrelevantRequestAliases(): ContentIrrelevantRequestAliases
    {
        if ($this->contentIrrelevantRequestAliases === null) {
            $this->contentIrrelevantRequestAliases = new ContentIrrelevantRequestAliases([
                new ContentIrrelevantRequestAlias(sprintf('/%s', Request::TABLES), [], sprintf('/%s', Request::TABULKY), []),
                new ContentIrrelevantRequestAlias(sprintf('/%s', Request::TABLES), [], '/', [Request::TABULKY => '']),
                new ContentIrrelevantRequestAlias(sprintf('/%s', Request::TABLES), [], '/', [Request::TABLES => '']),
            ]);
        }
        return $this->contentIrrelevantRequestAliases;
    }

    public function getContentIrrelevantParametersFilter(): ContentIrrelevantParametersFilter
    {
        if ($this->contentIrrelevantParametersFilter === null) {
            $this->contentIrrelevantParametersFilter = new ContentIrrelevantParametersFilter([Request::TRIAL, 'fbclid']);
        }
        return $this->contentIrrelevantParametersFilter;
    }

    public function getCssFiles(): CssFiles
    {
        if ($this->cssFiles === null) {
            $this->cssFiles = new CssFiles($this->getDirs(), $this->getEnvironment()->isInProduction());
        }
        return $this->cssFiles;
    }

    public function getJsFiles(): JsFiles
    {
        if ($this->jsFiles === null) {
            $this->jsFiles = new JsFiles($this->getConfiguration()->getDirs(), $this->getEnvironment()->isInProduction());
        }
        return $this->jsFiles;
    }

    public function getDirs(): Dirs
    {
        return $this->getConfiguration()->getDirs();
    }

    public function getRoutedWebFiles(): WebFiles
    {
        if ($this->routedWebFiles === null) {
            $this->routedWebFiles = new WebFiles($this->getRoutedWebRootProvider());
        }
        return $this->routedWebFiles;
    }

    protected function getRoutedWebRootProvider(): WebRootProvider
    {
        if ($this->routedWebRootProvider === null) {
            $this->routedWebRootProvider = new WebRootProvider($this->createRoutedDirs($this->getDirs()));
        }
        return $this->routedWebRootProvider;
    }

    public function getRootWebFiles(): WebFiles
    {
        if ($this->rootWebFiles === null) {
            $this->rootWebFiles = new WebFiles($this->getRootWebRootProvider());
        }
        return $this->rootWebFiles;
    }

    protected function getRootWebRootProvider(): WebRootProvider
    {
        if ($this->rootWebRootProvider === null) {
            $this->rootWebRootProvider = new WebRootProvider($this->getDirs());
        }
        return $this->rootWebRootProvider;
    }

    protected function createRoutedDirs(Dirs $dirs): RoutedDirs
    {
        return new RoutedDirs($dirs->getProjectRoot(), $this->getPathProvider());
    }

    protected function getPathProvider(): RouteMatchingPathProvider
    {
        if ($this->pathProvider === null) {
            $this->pathProvider = new RouteMatchingPathProvider($this->getRulesUrlMatcher(), $this->getRequest()->getCurrentUrl());
        }
        return $this->pathProvider;
    }

    public function getRulesUrlMatcher(): RulesUrlMatcher
    {
        if ($this->rulesUrlMatcher === null) {
            $this->rulesUrlMatcher = new RulesUrlMatcher($this->createUrlMatcher());
        }
        return $this->rulesUrlMatcher;
    }

    private function createUrlMatcher(): UrlMatcherInterface
    {
        $yamlFileWithRoutes = $this->getYamlFileWithRoutes();
        if ($yamlFileWithRoutes === '') {
            return new DummyUrlMatcher();
        }
        $_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? ''; // as http-foundation request requires string
        $router = new \Symfony\Component\Routing\Router(
            new YamlFileLoader(new FileLocator([$this->getDirs()->getProjectRoot()])),
            $yamlFileWithRoutes,
            ['cache_dir' => $this->getRouterCacheDirProvider()->getRouterCacheDir()],
            (new RequestContext())->fromRequest(\Symfony\Component\HttpFoundation\Request::createFromGlobals())
        );
        return $router->getMatcher();
    }

    private function getProjectRootFileLocator(): FileLocator
    {
        if ($this->projectRootFileLocator === null) {
            $this->projectRootFileLocator = new FileLocator([$this->getDirs()->getProjectRoot()]);
        }
        return $this->projectRootFileLocator;
    }

    public function getRouterCacheDirProvider(): RouterCacheDirProvider
    {
        if ($this->routerCacheDirProvider === null) {
            $this->routerCacheDirProvider = new RouterCacheDirProvider(
                $this->getProjectRootFileLocator(),
                $this->getYamlFileWithRoutes(),
                $this->getRouterCache()
            );
        }
        return $this->routerCacheDirProvider;
    }

    protected function getRouterCache(): WebCache
    {
        if ($this->routerCache === null) {
            $this->routerCache = new WebCache(
                $this->getCurrentWebVersion(),
                $this->getDirs(),
                WebCache::ROUTER,
                $this->getRequest(),
                $this->getRequestCachingPermissionProvider(),
                $this->getRequestHashProvider(),
                $this->getGit(),
                $this->getConfiguration(),
                $this->getEnvironment()->isInProduction()
            );
        }
        return $this->routerCache;
    }

    public function getRequestCachingPermissionProvider(): RequestCachingPermissionProvider
    {
        if ($this->requestCachingPermissionProvider === null) {
            $this->requestCachingPermissionProvider = new RequestCachingPermissionProvider($this->getRequest());
        }
        return $this->requestCachingPermissionProvider;
    }

    public function getRequestHashProvider(): RequestHashProvider
    {
        if ($this->requestHashProvider === null) {
            $this->requestHashProvider = new RequestHashProvider(
                $this->getRequest(),
                $this->getContentIrrelevantRequestAliases(),
                $this->getContentIrrelevantParametersFilter()
            );
        }
        return $this->requestHashProvider;
    }

    protected function getYamlFileWithRoutes(): string
    {
        $yamlFileWithRoutes = $this->getConfiguration()->getYamlFileWithRoutes();
        if ($yamlFileWithRoutes !== '') {
            return $yamlFileWithRoutes;
        }
        $defaultYamlFileWithRoutes = $this->getDirs()->getProjectRoot() . '/' . $this->getConfiguration()->getDefaultYamlFileWithRoutes();
        if (!file_exists($defaultYamlFileWithRoutes)) {
            return '';
        }
        return $defaultYamlFileWithRoutes;
    }

    public function getCookiesService(): CookiesService
    {
        if ($this->cookiesService === null) {
            $this->cookiesService = new CookiesService($this->getRequest());
        }
        return $this->cookiesService;
    }

    public function getNow(): \DateTimeImmutable
    {
        if ($this->now === null) {
            $this->now = new \DateTimeImmutable();
        }
        return $this->now;
    }

    public function getCacheCleaner(): CacheCleaner
    {
        if ($this->cacheCleaner === null) {
            $this->cacheCleaner = new CacheCleaner($this->getDirs()->getCacheRoot());
        }
        return $this->cacheCleaner;
    }

    public function getTablesWebCache(): Cache
    {
        if ($this->tablesWebCache === null) {
            $this->tablesWebCache = new WebCache(
                $this->getCurrentWebVersion(),
                $this->getDirs(),
                WebCache::TABLES,
                $this->getRequest(),
                $this->getRequestCachingPermissionProvider(),
                $this->getRequestHashProvider(),
                $this->getGit(),
                $this->getConfiguration(),
                $this->getEnvironment()->isInProduction()
            );
        }
        return $this->tablesWebCache;
    }

    public function getGatewayWebCache(): Cache
    {
        if ($this->gatewayWebCache === null) {
            $this->gatewayWebCache = new WebCache(
                $this->getCurrentWebVersion(),
                $this->getDirs(),
                WebCache::GATEWAY,
                $this->getRequest(),
                $this->getRequestCachingPermissionProvider(),
                $this->getRequestHashProvider(),
                $this->getGit(),
                $this->getConfiguration(),
                $this->getEnvironment()->isInProduction()
            );
        }
        return $this->gatewayWebCache;
    }

    public function getPassedWebCache(): Cache
    {
        if ($this->passedWebCache === null) {
            $this->passedWebCache = new WebCache(
                $this->getCurrentWebVersion(),
                $this->getDirs(),
                WebCache::PASSED_GATEWAY,
                $this->getRequest(),
                $this->getRequestCachingPermissionProvider(),
                $this->getRequestHashProvider(),
                $this->getGit(),
                $this->getConfiguration(),
                $this->getEnvironment()->isInProduction()
            );
        }
        return $this->passedWebCache;
    }

    public function getNotFoundCache(): Cache
    {
        if ($this->notFoundCache === null) {
            $this->notFoundCache = new WebCache(
                $this->getCurrentWebVersion(),
                $this->getDirs(),
                WebCache::NOT_FOUND,
                $this->getRequest(),
                $this->getRequestCachingPermissionProvider(),
                $this->getRequestHashProvider(),
                $this->getGit(),
                $this->getConfiguration(),
                $this->getEnvironment()->isInProduction()
            );
        }
        return $this->notFoundCache;
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

    public function getEmptyMenuBody(): EmptyMenuBody
    {
        if ($this->emptyMenuBody === null) {
            $this->emptyMenuBody = new EmptyMenuBody();
        }
        return $this->emptyMenuBody;
    }

    public function getDummyWebCache(): DummyWebCache
    {
        return new DummyWebCache(
            $this->getCurrentWebVersion(),
            $this->getDirs(),
            WebCache::DUMMY,
            $this->getRequest(),
            $this->getRequestCachingPermissionProvider(),
            $this->getRequestHashProvider(),
            $this->getGit(),
            $this->getConfiguration(),
            $this->getEnvironment()->isInProduction()
        );
    }

    public function getTablesRequestDetector(): TablesRequestDetector
    {
        if ($this->tablesRequestDetector === null) {
            $this->tablesRequestDetector = new TablesRequestDetector(
                $this->getRulesUrlMatcher(),
                $this->getRequest()
            );
        }
        return $this->tablesRequestDetector;
    }

}

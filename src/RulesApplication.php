<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

use DrdPlus\RulesSkeleton\Cache\CacheIdProvider;
use DrdPlus\RulesSkeleton\Cache\CacheInterface;
use DrdPlus\RulesSkeleton\Web\Menu\MenuBodyInterface;
use DrdPlus\RulesSkeleton\Web\RulesContent;
use DrdPlus\RulesSkeleton\Web\RulesHtmlDocumentPostProcessor;
use Granam\Strict\Object\StrictObject;
use Granam\WebContentBuilder\Web\Exceptions\UnknownWebFilesDir;
use Granam\WebContentBuilder\Web\HtmlContentInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class RulesApplication extends StrictObject
{
    private ServicesContainer $servicesContainer;
    private ?RulesContent $content = null;
    private ?Redirect $redirect = null;
    private ?bool $canPassIn = null;
    private ?RulesContent $notFoundContent = null;

    public function __construct(ServicesContainer $servicesContainer)
    {
        $this->servicesContainer = $servicesContainer;
    }

    public function run(): void
    {
        try {
            $this->sendCustomHeaders();
            if ($this->isRequestedWebVersionUpdate()) {
                echo $this->updateCode();
                $this->clearCache();
            } else {
                $this->persistCurrentVersion();
                echo $this->getContent()->getValue();
            }
        } catch (UnknownWebFilesDir | RouteNotFoundException | ResourceNotFoundException $invalidRoute) {
            $this->sendNotFoundHeaders();
            echo $this->getNotFoundRulesContent()->getValue();
        }
    }

    private function isRequestedWebVersionUpdate(): bool
    {
        return $this->servicesContainer->getRequest()->getValue(Request::UPDATE) === 'web';
    }

    protected function updateCode(): string
    {
        return \implode(
            "\n",
            $this->servicesContainer->getGit()->update($this->servicesContainer->getDirs()->getProjectRoot())
        );
    }

    protected function clearCache()
    {
        $this->servicesContainer->getCacheCleaner()->clearCache();
    }

    private function persistCurrentVersion(): bool
    {
        return $this->servicesContainer->getCookiesService()->setMinorVersionCookie(
            $this->servicesContainer->getCurrentWebVersion()->getCurrentMinorVersion()
        );
    }

    private function getContent(): RulesContent
    {
        if ($this->content) {
            return $this->content;
        }
        $servicesContainer = $this->servicesContainer;
        if ($servicesContainer->getTablesRequestDetector()->areTablesRequested()) {
            $rulesHtmlDocumentPostProcessor = $this->createRulesHtmlDocumentPostProcessor(
                $servicesContainer->getPassedMenuBody(),
                $servicesContainer->getTablesWebCache()
            );
            $this->content = $this->createRulesContent(
                $servicesContainer->getTablesContent(),
                $servicesContainer->getTablesWebCache(),
                RulesContent::TABLES,
                $rulesHtmlDocumentPostProcessor
            );

            return $this->content;
        }
        if ($servicesContainer->getRequest()->isRequestedPdf()
            && $servicesContainer->getRoutedWebPartsContainer()->getPdfBody()->getPdfFile()
        ) {
            $rulesHtmlDocumentPostProcessor = $this->createRulesHtmlDocumentPostProcessor(
                $servicesContainer->getEmptyMenuBody(),
                $servicesContainer->getDummyWebCache()
            );
            $this->content = $this->createRulesContent(
                $servicesContainer->getPdfContent(),
                $servicesContainer->getDummyWebCache(),
                RulesContent::PDF,
                $rulesHtmlDocumentPostProcessor
            );

            return $this->content;
        }
        if (!$this->canPassIn()) {
            $rulesHtmlDocumentPostProcessor = $this->createRulesHtmlDocumentPostProcessor(
                $servicesContainer->getGatewayMenuBody(),
                $servicesContainer->getGatewayWebCache()
            );
            $this->content = $this->createRulesContent(
                $servicesContainer->getGatewayContent(),
                $servicesContainer->getGatewayWebCache(),
                RulesContent::GATEWAY,
                $rulesHtmlDocumentPostProcessor
            );

            return $this->content;
        }
        $rulesHtmlDocumentPostProcessor = $this->createRulesHtmlDocumentPostProcessor(
            $servicesContainer->getPassedMenuBody(),
            $servicesContainer->getPassedWebCache()
        );
        $this->content = $this->createRulesContent(
            $servicesContainer->getRulesMainContent(),
            $servicesContainer->getPassedWebCache(),
            RulesContent::FULL,
            $rulesHtmlDocumentPostProcessor
        );

        return $this->content;
    }

    private function createRulesHtmlDocumentPostProcessor(MenuBodyInterface $menuBody, CacheIdProvider $cacheIdProvider): RulesHtmlDocumentPostProcessor
    {
        return new RulesHtmlDocumentPostProcessor($menuBody, $this->servicesContainer->getCurrentWebVersion(), $cacheIdProvider);
    }

    private function createRulesContent(HtmlContentInterface $content, CacheInterface $cache, string $contentType, RulesHtmlDocumentPostProcessor $rulesHtmlDocumentPostProcessor): RulesContent
    {
        return new RulesContent(
            $content,
            $cache,
            $rulesHtmlDocumentPostProcessor,
            $contentType,
            $this->getRedirect()
        );
    }

    private function getRedirect(): ?Redirect
    {
        return $this->redirect;
    }

    private function canPassIn(): bool
    {
        if ($this->canPassIn !== null) {
            return $this->canPassIn;
        }
        if (!$this->servicesContainer->getTicket()->canPassIn()) {
            if ($this->servicesContainer->getRequest()->getValueFromPost(Request::CONFIRM)) {
                $this->servicesContainer->getUsagePolicy()->confirmOwnershipOfVisitor(new \DateTime('+1 year'));
                $this->checkThatCanPassNow();
            } elseif ($this->servicesContainer->getRequest()->getValue(Request::TRIAL)) {
                $this->activateTrial($this->servicesContainer->getNow());
                $this->checkThatCanPassNow();
            }
        }

        return $this->canPassIn = $this->servicesContainer->getTicket()->canPassIn();
    }

    private function checkThatCanPassNow(): void
    {
        if (!$this->servicesContainer->getTicket()->canPassIn()) {
            throw new Exceptions\CanNotPassIn('Visitor should be able to pass in but still can not');
        }
    }

    private function activateTrial(\DateTimeImmutable $now): bool
    {
        $trialExpiration = $now->modify('+4 minutes');
        $visitorCanAccessContent = $this->servicesContainer->getUsagePolicy()->activateTrial($trialExpiration);
        if ($visitorCanAccessContent) {
            $at = $trialExpiration->getTimestamp() + 1; // one second "insurance" overlap
            $afterSeconds = $at - $now->getTimestamp();
            $this->setRedirect(new Redirect(\sprintf('/?%s=%d', Request::TRIAL_EXPIRED_AT, $at), $afterSeconds));
        }

        return $visitorCanAccessContent;
    }

    private function setRedirect(Redirect $redirect): void
    {
        $this->redirect = $redirect;
        $this->content = null; // unset Content to re-create it with new redirect
    }

    private function sendCustomHeaders(): void
    {
        if ($this->getContent()->containsTables()) {
            if ($this->servicesContainer->getRequest()->isCliRequest()) {
                return;
            }
            // anyone can show content of this page
            \header('Access-Control-Allow-Origin: *');
        } elseif ($this->getContent()->containsPdf()) {
            $pdfFile = $this->servicesContainer->getRoutedWebPartsContainer()->getPdfBody()->getPdfFile();
            $pdfFileBasename = \basename($pdfFile);
            if ($this->servicesContainer->getRequest()->isCliRequest()) {
                return;
            }
            \header('Content-type: application/pdf');
            \header('Content-Length: ' . \filesize($pdfFile));
            \header("Content-Disposition: attachment; filename=\"$pdfFileBasename\"");
        }
    }

    private function sendNotFoundHeaders(): void
    {
        if ($this->servicesContainer->getRequest()->isCliRequest()) {
            return;
        }
        http_response_code(404);
    }

    private function getNotFoundRulesContent(): RulesContent
    {
        if ($this->notFoundContent) {
            return $this->notFoundContent;
        }
        $servicesContainer = $this->servicesContainer;
        $rulesHtmlDocumentPostProcessor = $this->createRulesHtmlDocumentPostProcessor(
            $servicesContainer->getPassedMenuBody(),
            $servicesContainer->getNotFoundCache()
        );
        $this->notFoundContent = $this->createRulesContent(
            $servicesContainer->getNotFoundContent(),
            $servicesContainer->getNotFoundCache(),
            RulesContent::NOT_FOUND,
            $rulesHtmlDocumentPostProcessor
        );

        return $this->notFoundContent;
    }
}

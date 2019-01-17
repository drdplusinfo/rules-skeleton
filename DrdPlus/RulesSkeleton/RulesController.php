<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

use DrdPlus\RulesSkeleton\Web\Content;
use Granam\Strict\Object\StrictObject;

class RulesController extends StrictObject
{
    /** @var ServicesContainer */
    private $servicesContainer;
    /** @var Configuration */
    private $configuration;
    /** @var array */
    private $bodyClasses;
    /** @var Content */
    private $content;
    /** @var Redirect */
    private $redirect;
    /** @var bool */
    private $canPassIn;

    public function __construct(ServicesContainer $servicesContainer, array $bodyClasses = [])
    {
        $this->servicesContainer = $servicesContainer;
        $this->configuration = $servicesContainer->getConfiguration();
        $this->bodyClasses = $bodyClasses;
    }

    public function getBodyClasses(): array
    {
        return $this->bodyClasses;
    }

    public function addBodyClass(string $class): void
    {
        $this->bodyClasses[] = $class;
    }

    public function isMenuPositionFixed(): bool
    {
        return $this->configuration->isMenuPositionFixed();
    }

    public function isShownHomeButton(): bool
    {
        return $this->configuration->isShowHomeButton();
    }

    public function isRequestedWebVersionUpdate(): bool
    {
        return $this->servicesContainer->getRequest()->getValue(Request::UPDATE) === 'web';
    }

    public function updateWebVersion(): int
    {
        $updatedVersions = 0;
        // sadly we do not know which version has been updated, so we will update all of them
        foreach ($this->servicesContainer->getWebVersions()->getAllMinorVersions() as $version) {
            $this->servicesContainer->getCurrentWebVersion()->update($version);
            $updatedVersions++;
        }

        return $updatedVersions;
    }

    public function persistCurrentVersion(): bool
    {
        return $this->servicesContainer->getCookiesService()->setMinorVersionCookie(
            $this->servicesContainer->getCurrentWebVersion()->getCurrentMinorVersion()
        );
    }

    public function getContent(): Content
    {
        if ($this->content) {
            return $this->content;
        }
        $servicesContainer = $this->servicesContainer;
        if ($servicesContainer->getRequest()->areRequestedTables()) {
            $this->content = new Content(
                $servicesContainer->getRulesTablesWebContent(),
                $servicesContainer->getHtmlHelper(),
                $servicesContainer->getCurrentWebVersion(),
                $servicesContainer->getMenu(),
                $servicesContainer->getTablesWebCache(),
                Content::TABLES,
                $this->getRedirect()
            );

            return $this->content;
        }
        if ($servicesContainer->getRequest()->isRequestedPdf() && $servicesContainer->getPdfBody()->getPdfFile()) {
            $this->content = new Content(
                $servicesContainer->getRulesPdfWebContent(),
                $servicesContainer->getHtmlHelper(),
                $servicesContainer->getCurrentWebVersion(),
                $servicesContainer->getEmptyMenu(),
                $servicesContainer->getDummyWebCache(),
                Content::PDF,
                $this->getRedirect()
            );

            return $this->content;
        }
        if (!$this->canPassIn()) {
            $this->content = new Content(
                $servicesContainer->getRulesPassWebContent(),
                $servicesContainer->getHtmlHelper(),
                $servicesContainer->getCurrentWebVersion(),
                $servicesContainer->getMenu(),
                $servicesContainer->getPassWebCache(),
                Content::PASS,
                $this->getRedirect()
            );

            return $this->content;
        }
        $this->content = new Content(
            $servicesContainer->getRulesWebContent(),
            $servicesContainer->getHtmlHelper(),
            $servicesContainer->getCurrentWebVersion(),
            $servicesContainer->getMenu(),
            $servicesContainer->getWebCache(),
            Content::FULL,
            $this->getRedirect()
        );

        return $this->content;
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
        $canPassIn = !$this->configuration->hasProtectedAccess();
        if (!$canPassIn) {
            $usagePolicy = $this->servicesContainer->getUsagePolicy();
            $canPassIn = $usagePolicy->isVisitorBot();
            if (!$canPassIn) {
                $canPassIn = $usagePolicy->hasVisitorConfirmedOwnership();
                if (!$canPassIn) {
                    $canPassIn = $usagePolicy->isVisitorUsingValidTrial();
                    if (!$canPassIn) {
                        if ($this->servicesContainer->getRequest()->getValueFromPost(Request::CONFIRM)) {
                            /** @noinspection PhpUnhandledExceptionInspection */
                            $canPassIn = $usagePolicy->confirmOwnershipOfVisitor(new \DateTime('+1 year'));
                        }
                        if (!$canPassIn && $this->servicesContainer->getRequest()->getValue(Request::TRIAL)) {
                            $canPassIn = $this->activateTrial($this->servicesContainer->getNow());
                        }
                    }
                }
            }
        }

        return $this->canPassIn = $canPassIn;
    }

    private function activateTrial(\DateTimeImmutable $now): bool
    {
        $trialExpiration = $now->modify('+4 minutes');
        $visitorCanAccessContent = $this->servicesContainer->getUsagePolicy()->activateTrial($trialExpiration);
        if ($visitorCanAccessContent) {
            $at = $trialExpiration->getTimestamp() + 1; // one second "insurance" overlap
            $afterSeconds = $at - $now->getTimestamp();
            $this->setRedirect(
                new Redirect("/?{$this->servicesContainer->getUsagePolicy()->getTrialExpiredAtName()}={$at}", $afterSeconds)
            );
        }

        return $visitorCanAccessContent;
    }

    private function setRedirect(Redirect $redirect): void
    {
        $this->redirect = $redirect;
        $this->content = null; // unset Content to re-create it with new redirect
    }

    public function sendCustomHeaders(): void
    {
        if ($this->getContent()->containsTables()) {
            if (\PHP_SAPI === 'cli') {
                return;
            }
            // anyone can show content of this page
            \header('Access-Control-Allow-Origin: *');
        } elseif ($this->getContent()->containsPdf()) {
            $pdfFile = $this->servicesContainer->getPdfBody()->getPdfFile();
            $pdfFileBasename = \basename($pdfFile);
            if (\PHP_SAPI === 'cli') {
                return;
            }
            \header('Content-type: application/pdf');
            \header('Content-Length: ' . \filesize($pdfFile));
            \header("Content-Disposition: attachment; filename=\"$pdfFileBasename\"");
        }
    }
}
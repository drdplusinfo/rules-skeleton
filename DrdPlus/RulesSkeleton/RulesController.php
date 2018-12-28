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
            $this->servicesContainer->getWebVersions()->update($version);
            $updatedVersions++;
        }

        return $updatedVersions;
    }

    public function persistCurrentVersion(): bool
    {
        return $this->servicesContainer->getCookiesService()->setMinorVersionCookie(
            $this->servicesContainer->getWebVersions()->getCurrentMinorVersion()
        );
    }

    /**
     * @return Content
     */
    public function getContent(): Content
    {
        if ($this->content) {
            return $this->content;
        }
        $servicesContainer = $this->servicesContainer;
        if ($servicesContainer->getRequest()->areRequestedTables()) {
            $this->content = new Content(
                $servicesContainer->getHtmlHelper(),
                $servicesContainer->getWebVersions(),
                $servicesContainer->getHeadForTables(),
                $servicesContainer->getMenu(),
                $servicesContainer->getTablesBody(),
                $servicesContainer->getTablesWebCache(),
                Content::TABLES,
                $this->getRedirect()
            );

            return $this->content;
        }
        if ($servicesContainer->getRequest()->isRequestedPdf() && $servicesContainer->getPdfBody()->getPdfFile()) {
            $this->content = new Content(
                $servicesContainer->getHtmlHelper(),
                $servicesContainer->getWebVersions(),
                $servicesContainer->getEmptyHead(),
                $servicesContainer->getEmptyMenu(),
                $servicesContainer->getPdfBody(),
                $servicesContainer->getEmptyWebCache(),
                Content::PDF,
                $this->getRedirect()
            );

            return $this->content;
        }
        if (!$this->canPassIn()) {
            $this->content = new Content(
                $servicesContainer->getHtmlHelper(),
                $servicesContainer->getWebVersions(),
                $servicesContainer->getHead(),
                $servicesContainer->getMenu(),
                $servicesContainer->getPassBody(),
                $servicesContainer->getPassWebCache(),
                Content::PASS,
                $this->getRedirect()
            );

            return $this->content;
        }
        $this->content = new Content(
            $servicesContainer->getHtmlHelper(),
            $servicesContainer->getWebVersions(),
            $servicesContainer->getHead(),
            $servicesContainer->getMenu(),
            $servicesContainer->getBody(),
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

    private function activateTrial(\DateTime $now): bool
    {
        $trialExpiration = (clone $now)->modify('+4 minutes');
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
            \header('Access-Control-Allow-Origin: *', true);
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
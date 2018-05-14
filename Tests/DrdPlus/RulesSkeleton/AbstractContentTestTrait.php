<?php
namespace Tests\DrdPlus\RulesSkeleton;

use DrdPlus\FrontendSkeleton\UsagePolicy;
use Gt\Dom\HTMLDocument;

trait AbstractContentTestTrait
{
    protected function passIn(): void
    {
        $_COOKIE[$this->getCookieNameForLocalOwnershipConfirmation()] = true; // this cookie simulates confirmation of ownership
    }

    /**
     * @param bool $notCached
     * @return string
     */
    protected function getOwnershipConfirmationContent(bool $notCached = false): string
    {
        if ($notCached) {
            return $this->fetchRulesContent();
        }
        static $ownershipConfirmationContent;
        if ($ownershipConfirmationContent === null) {
            $ownershipConfirmationContent = $this->fetchRulesContent();
        }

        return $ownershipConfirmationContent;
    }

    private function fetchRulesContent(): string
    {
        \ob_start();
        /** @noinspection PhpIncludeInspection */
        include DRD_PLUS_INDEX_FILE_NAME_TO_TEST;

        return \ob_get_clean();
    }

    private function getCookieNameForLocalOwnershipConfirmation(): string
    {
        static $cookieName;
        if ($cookieName === null) {
            $cookieName = $this->getCookieNameForOwnershipConfirmation(
                \basename($this->getDirName(DRD_PLUS_INDEX_FILE_NAME_TO_TEST))
            );
        }

        return $cookieName;
    }

    private function getDirName(string $fileName): string
    {
        $dirName = $fileName;
        while (\basename($dirName) === '.' || \basename($dirName) === '..' || !\is_dir($dirName)) {
            $dirName = \dirname(
                $dirName,
                \basename($dirName) === '.' || !\is_dir($dirName)
                    ? 1
                    : 2 // ..
            );
            if ($dirName === '/') {
                throw new \RuntimeException("Could not find name of dir by $fileName");
            }
        }

        return $dirName;
    }

    protected function getCookieNameForOwnershipConfirmation(string $rulesDirBasename): string
    {
        $usagePolicy = new UsagePolicy($rulesDirBasename);
        try {
            $reflectionClass = new \ReflectionClass(UsagePolicy::class);
        } catch (\ReflectionException $reflectionException) {
            self::fail($reflectionException->getMessage());
            exit;
        }
        $getCookieName = $reflectionClass->getMethod('getOwnershipCookieName');
        $getCookieName->setAccessible(true);

        return $getCookieName->invoke($usagePolicy);
    }

    private function removeOwnerShipConfirmation(): void
    {
        unset($_COOKIE[$this->getCookieNameForLocalOwnershipConfirmation()]);
    }

    /**
     * @param string $show = ''
     * @param string $hide = ''
     * @return string
     */
    protected function getRulesContentForDev(string $show = '', string $hide = ''): string
    {
        static $rulesContentForDev = [];
        if (empty($rulesContentForDev[$show][$hide])) {
            $this->passIn();
            $_GET['mode'] = 'dev';
            if ($show !== '') {
                $_GET['show'] = $show;
            }
            if ($hide !== '') {
                $_GET['hide'] = $hide;
            }
            \ob_start();
            /** @noinspection PhpIncludeInspection */
            include DRD_PLUS_INDEX_FILE_NAME_TO_TEST;
            $rulesContentForDev[$show][$hide] = \ob_get_clean();
            unset($_GET['mode'], $_GET['show'], $_GET['hide']);
            $this->removeOwnerShipConfirmation();
            self::assertNotSame($this->getOwnershipConfirmationContent(), $rulesContentForDev[$show]);
        }

        return $rulesContentForDev[$show][$hide];
    }

    protected function getRulesForDevHtmlDocument(string $show = '', string $hide = '')
    {
        static $rulesForDevHtmlDocument = [];
        if (empty($rulesForDevHtmlDocument[$show][$hide])) {
            $rulesForDevHtmlDocument[$show][$hide] = new HTMLDocument($this->getRulesContentForDev($show, $hide));
        }

        return $rulesForDevHtmlDocument[$show][$hide];
    }

    /**
     * @return string
     */
    protected function getRulesContentForDevWithHiddenCovered(): string
    {
        return $this->getRulesContentForDev('', 'covered');
    }

    protected function getEshopFileName(): string
    {
        return $this->getDocumentRoot() . '/eshop_url.txt';
    }
}
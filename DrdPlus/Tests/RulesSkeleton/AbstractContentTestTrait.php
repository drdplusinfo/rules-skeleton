<?php
namespace DrdPlus\Tests\RulesSkeleton;

use DeviceDetector\Parser\Bot;
use DrdPlus\RulesSkeleton\Request;
use DrdPlus\RulesSkeleton\UsagePolicy;
use Gt\Dom\HTMLDocument;

/**
 * @method string getDocumentRoot
 * @method TestsConfiguration getTestsConfiguration
 * @method static assertNotSame($expected, $actual)
 * @method static fail($message)
 */
trait AbstractContentTestTrait
{
    private static $rulesContentForDev = [];
    private static $rulesForDevHtmlDocument = [];

    protected function setUp()
    {
        parent::setUp();
        $this->passIn();
    }

    protected function passIn(): bool
    {
        $_COOKIE[$this->getCookieNameForLocalOwnershipConfirmation()] = true; // this cookie simulates confirmation of ownership
        $realDocumentRoot = \realpath($this->getDocumentRoot());
        $usagePolicy = new UsagePolicy(\basename($realDocumentRoot), new Request(new Bot()));
        self::assertTrue(
            $usagePolicy->hasVisitorConfirmedOwnership(),
            "Ownership has not been confirmed by cookie '{$this->getCookieNameForLocalOwnershipConfirmation()}'"
            . " (with document root {$realDocumentRoot})"
        );

        return true;
    }

    protected function getPassDocument(bool $notCached = false): \DrdPlus\FrontendSkeleton\HtmlDocument
    {
        if ($notCached) {
            return new \DrdPlus\FrontendSkeleton\HtmlDocument($this->getPassContent($notCached));
        }
        static $passDocument;
        if ($passDocument === null) {
            $this->removeOwnerShipConfirmation();
            $passDocument = new \DrdPlus\FrontendSkeleton\HtmlDocument($this->fetchRulesContent());
        }

        return $passDocument;
    }

    /**
     * @param bool $notCached
     * @return string
     */
    protected function getPassContent(bool $notCached = false): string
    {
        if ($notCached) {
            $this->removeOwnerShipConfirmation();

            return $this->fetchRulesContent();
        }
        static $passContent;
        if ($passContent === null) {
            $this->removeOwnerShipConfirmation();
            $passContent = $this->fetchRulesContent();
        }

        return $passContent;
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

    protected function getCookieNameForOwnershipConfirmation(string $rulesDirBasename): string
    {
        $usagePolicy = new UsagePolicy($rulesDirBasename, new Request(new Bot()));
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

    private function getDirName(string $fileName): string
    {
        $dirName = $fileName;
        $upLevels = 0;
        while (\basename($dirName) === '.' || \basename($dirName) === '..' || !\is_dir($dirName)) {
            if (\basename($dirName) === '..') {
                $upLevels++;
            }
            $dirName = \dirname($dirName);
            if ($dirName === '/') {
                throw new \RuntimeException("Could not find name of dir by $fileName");
            }
        }
        for ($upLevel = 1; $upLevel <= $upLevels; $upLevel++) {
            $dirName = $this->getDirName(\dirname($dirName) /* up by a single level */);
        }

        return $dirName;
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
        if (empty(self::$rulesContentForDev[$show][$hide])) {
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
            self::$rulesContentForDev[$show][$hide] = \ob_get_clean();
            unset($_GET['mode'], $_GET['show'], $_GET['hide']);
            self::assertNotSame($this->getPassContent(), self::$rulesContentForDev[$show]);
        }

        return self::$rulesContentForDev[$show][$hide];
    }

    protected function getRulesForDevHtmlDocument(string $show = '', string $hide = ''): HTMLDocument
    {
        if (empty(self::$rulesForDevHtmlDocument[$show][$hide])) {
            self::$rulesForDevHtmlDocument[$show][$hide] = new HTMLDocument($this->getRulesContentForDev($show, $hide));
        }

        return self::$rulesForDevHtmlDocument[$show][$hide];
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

    protected function getGenericPartsRoot(): string
    {
        return \file_exists($this->getDocumentRoot() . '/parts/rules-skeleton')
            ? $this->getDocumentRoot() . '/parts/rules-skeleton'
            : $this->getVendorRoot() . '/drd-plus/rules-skeleton/parts/rules-skeleton';
    }

    protected function getVendorRoot(): string
    {
        return $this->getDocumentRoot() . '/vendor';
    }
}
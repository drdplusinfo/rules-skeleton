<?php
namespace Tests\DrdPlus\RulesSkeleton;

use DrdPlus\RulesSkeleton\UsagePolicy;
use Gt\Dom\HTMLDocument;
use PHPUnit\Framework\TestCase;

abstract class AbstractContentTest extends TestCase
{
    /** @var string */
    private static $ownershipConfirmationContent;
    /** @var string */
    private static $rulesContent;
    /** @var HTMLDocument */
    private static $rulesHtmlDocument;
    /** @var array|string[] */
    private static $rulesContentForDev = [];
    /** @var string */
    private static $rulesContentForDevWithHiddenCovered;
    /** @var string */
    private static $cookieName;

    protected function setUp()
    {
        if (!\defined('DRD_PLUS_RULES_INDEX_FILE_NAME_TO_TEST')) {
            self::markTestSkipped('Missing constant \'DRD_PLUS_RULES_INDEX_FILE_NAME_TO_TEST\'');
        }
    }

    /**
     * @return string
     */
    protected function getOwnershipConfirmationContent(): string
    {
        if (self::$ownershipConfirmationContent === null) {
            \ob_start();
            /** @noinspection PhpIncludeInspection */
            include DRD_PLUS_RULES_INDEX_FILE_NAME_TO_TEST;
            self::$ownershipConfirmationContent = \ob_get_clean();
        }

        return self::$ownershipConfirmationContent;
    }

    /**
     * @return string
     */
    protected function getRulesContent(): string
    {
        if (self::$rulesContent === null) {
            $this->confirmOwnership();
            \ob_start();
            /** @noinspection PhpIncludeInspection */
            include DRD_PLUS_RULES_INDEX_FILE_NAME_TO_TEST;
            self::$rulesContent = \ob_get_clean();
            $this->removeOwnerShipConfirmation();
            self::assertNotSame($this->getOwnershipConfirmationContent(), self::$rulesContent);
        }

        return self::$rulesContent;
    }

    private function confirmOwnership()
    {
        $_COOKIE[$this->getCookieNameForLocalOwnershipConfirmation()] = true; // this cookie simulates confirmation of ownership
    }

    private function getCookieNameForLocalOwnershipConfirmation(): string
    {
        if (self::$cookieName === null) {
            self::$cookieName = $this->getCookieNameForOwnershipConfirmation(
                \basename($this->getDirName(DRD_PLUS_RULES_INDEX_FILE_NAME_TO_TEST))
            );
        }

        return self::$cookieName;
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
        $reflectionClass = new \ReflectionClass(UsagePolicy::class);
        $getCookieName = $reflectionClass->getMethod('getCookieName');
        $getCookieName->setAccessible(true);

        return $getCookieName->invoke($usagePolicy);
    }

    private function removeOwnerShipConfirmation()
    {
        unset($_COOKIE[$this->getCookieNameForLocalOwnershipConfirmation()]);
    }

    protected function getRulesHtmlDocument(): HTMLDocument
    {
        if (self::$rulesHtmlDocument === null) {
            self::$rulesHtmlDocument = new HTMLDocument($this->getRulesContent());
        }

        return self::$rulesHtmlDocument;
    }

    /**
     * @param string $show = ''
     * @return string
     */
    protected function getRulesContentForDev(string $show = ''): string
    {
        if (!\array_key_exists($show, self::$rulesContentForDev)) {
            $this->confirmOwnership();
            $_GET['mode'] = 'dev';
            if ($show !== '') {
                $_GET['show'] = $show;
            }
            \ob_start();
            /** @noinspection PhpIncludeInspection */
            include DRD_PLUS_RULES_INDEX_FILE_NAME_TO_TEST;
            self::$rulesContentForDev[$show] = \ob_get_clean();
            $this->removeOwnerShipConfirmation();
            self::assertNotSame($this->getOwnershipConfirmationContent(), self::$rulesContentForDev[$show]);
        }

        return self::$rulesContentForDev[$show];
    }

    /**
     * @return string
     */
    protected function getRulesContentForDevWithHiddenCovered(): string
    {
        if (self::$rulesContentForDevWithHiddenCovered === null) {
            $this->confirmOwnership();
            $_GET['mode'] = 'dev';
            $_GET['hide'] = 'covered';
            \ob_start();
            /** @noinspection PhpIncludeInspection */
            include DRD_PLUS_RULES_INDEX_FILE_NAME_TO_TEST;
            self::$rulesContentForDevWithHiddenCovered = \ob_get_clean();
            $this->removeOwnerShipConfirmation();
            self::assertNotSame($this->getOwnershipConfirmationContent(), self::$rulesContentForDevWithHiddenCovered);
        }

        return self::$rulesContentForDevWithHiddenCovered;
    }

    protected function checkingSkeleton(HTMLDocument $document): bool
    {
        return \strpos($document->head->getElementsByTagName('title')->item(0)->nodeValue, 'skeleton') !== false;
    }

    protected function getDocumentRoot(): string
    {
        return \dirname(DRD_PLUS_RULES_INDEX_FILE_NAME_TO_TEST);
    }

    protected function getEshopFileName(): string
    {
        return $this->getDocumentRoot() . '/eshop_url.txt';
    }

}
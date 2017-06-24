<?php
namespace Tests\DrdPlus\RulesSkeleton;

use DrdPlus\RulesSkeleton\UsagePolicy;
use Gt\Dom\HTMLDocument;
use PHPUnit\Framework\TestCase;

abstract class AbstractContentTest extends TestCase
{
    /** @var string */
    private $ownershipConfirmationContent;
    /** @var string */
    private $rulesContent;
    /** @var HTMLDocument */
    private $rulesHtmlDocument;
    /** @var string */
    private $rulesContentForDev;
    /** @var string */
    private $cookieName;

    protected function setUp()
    {
        if (!defined('DRD_PLUS_RULES_INDEX_FILE_NAME_TO_TEST')) {
            self::markTestSkipped('Missing constant \'DRD_PLUS_RULES_INDEX_FILE_NAME_TO_TEST\'');
        }
    }

    /**
     * @return string
     */
    protected function getOwnershipConfirmationContent(): string
    {
        if ($this->ownershipConfirmationContent === null) {
            ob_start();
            /** @noinspection PhpIncludeInspection */
            include DRD_PLUS_RULES_INDEX_FILE_NAME_TO_TEST;
            $this->ownershipConfirmationContent = ob_get_clean();
        }

        return $this->ownershipConfirmationContent;
    }

    /**
     * @return string
     */
    protected function getRulesContent(): string
    {
        if ($this->rulesContent === null) {
            $this->confirmOwnership();
            ob_start();
            /** @noinspection PhpIncludeInspection */
            include DRD_PLUS_RULES_INDEX_FILE_NAME_TO_TEST;
            $this->rulesContent = ob_get_clean();
            $this->removeOwnerShipConfirmation();
            self::assertNotSame($this->getOwnershipConfirmationContent(), $this->rulesContent);
        }

        return $this->rulesContent;
    }

    private function confirmOwnership()
    {
        $_COOKIE[$this->getCookieNameForSkeletonOwnershipConfirmation()] = true; // this cookie simulates confirmation of ownership
    }

    private function getCookieNameForSkeletonOwnershipConfirmation(): string
    {
        if ($this->cookieName === null) {
            $this->cookieName = $this->getCookieNameForOwnershipConfirmation(
                basename(dirname(realpath(DRD_PLUS_RULES_INDEX_FILE_NAME_TO_TEST)))
            );
        }

        return $this->cookieName;
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
        unset($_COOKIE[$this->getCookieNameForSkeletonOwnershipConfirmation()]);
    }

    protected function getRulesHtmlDocument(): HTMLDocument
    {
        if ($this->rulesHtmlDocument === null) {
            $this->rulesHtmlDocument = new HTMLDocument($this->getRulesContent());
        }

        return $this->rulesHtmlDocument;
    }

    /**
     * @return string
     */
    protected function getRulesContentForDev(): string
    {
        if ($this->rulesContentForDev === null) {
            $this->confirmOwnership();
            $_GET['mode'] = 'dev';
            ob_start();
            /** @noinspection PhpIncludeInspection */
            include DRD_PLUS_RULES_INDEX_FILE_NAME_TO_TEST;
            $this->rulesContentForDev = ob_get_clean();
            $this->removeOwnerShipConfirmation();
            self::assertNotSame($this->getOwnershipConfirmationContent(), $this->rulesContentForDev);
        }

        return $this->rulesContentForDev;
    }

}
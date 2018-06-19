<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\RulesSkeleton;

use DrdPlus\FrontendSkeleton\Cookie;
use Granam\Strict\Object\StrictObject;

class UsagePolicy extends StrictObject
{
    /**
     * @var string
     */
    private $articleName;
    /**
     * @var Request
     */
    private $request;

    /**
     * @param string $articleName
     * @param \DrdPlus\FrontendSkeleton\Request $request
     * @throws \DrdPlus\RulesSkeleton\Exceptions\ArticleNameCanNotBeEmptyForUsagePolicy
     * @throws \DrdPlus\FrontendSkeleton\Exceptions\CookieCanNotBeSet
     */
    public function __construct(string $articleName, \DrdPlus\FrontendSkeleton\Request $request)
    {
        $articleName = trim($articleName);
        if ($articleName === '') {
            throw new Exceptions\ArticleNameCanNotBeEmptyForUsagePolicy('Name of the article to confirm ownership can not be empty');
        }
        $this->articleName = $articleName;
        $this->request = $request;
        $this->setCookie('ownershipCookieName', $this->getOwnershipCookieName(), null /* expire on session end*/);
        $this->setCookie('trialCookieName', $this->getTrialCookieName(), null /* expire on session end*/);
        $this->setCookie('trialExpiredAtName', 'trialExpiredAt', null /* expire on session end*/);
    }

    /**
     * @param string $cookieName
     * @param string $value
     * @param \DateTime|null $expiresAt
     * @return bool
     * @throws \DrdPlus\FrontendSkeleton\Exceptions\CookieCanNotBeSet
     */
    private function setCookie(string $cookieName, string $value, ?\DateTime $expiresAt): bool
    {
        return Cookie::setCookie($cookieName, $value, true /* only HTTP, not accessible via JS */, $expiresAt);
    }

    /**
     * @return bool
     */
    public function hasVisitorConfirmedOwnership(): bool
    {
        return Cookie::getCookie($this->getOwnershipCookieName()) !== null;
    }

    /**
     * @return string
     */
    private function getOwnershipCookieName(): string
    {
        return \str_replace('.', '_', 'confirmedOwnershipOf' . ucfirst($this->articleName));
    }

    /**
     * @param \DateTime $expiresAt
     * @return bool
     * @throws \RuntimeException
     */
    public function confirmOwnershipOfVisitor(\DateTime $expiresAt): bool
    {
        return $this->setCookie($this->getOwnershipCookieName(), (string)$expiresAt->getTimestamp(), $expiresAt);
    }

    public function isVisitorBot(): bool
    {
        return $this->request->isVisitorBot();
    }

    /**
     * @return bool
     */
    public function isVisitorUsingTrial(): bool
    {
        return Cookie::getCookie($this->getTrialCookieName()) !== null;
    }

    /**
     * @return string
     */
    public function getTrialCookieName(): string
    {
        return \str_replace('.', '_', 'trialOf' . ucfirst($this->articleName));
    }

    /**
     * @param \DateTime $expiresAt
     * @return bool
     * @throws \RuntimeException
     */
    public function activateTrial(\DateTime $expiresAt): bool
    {
        return $this->setCookie($this->getTrialCookieName(), (string)$expiresAt->getTimestamp(), $expiresAt);
    }

    public function trialJustExpired(): bool
    {
        // expired before 5 seconds or less
        return !empty($_GET['trialExpiredAt']) && ((int)$_GET['trialExpiredAt'] + 5) >= \time();
    }
}
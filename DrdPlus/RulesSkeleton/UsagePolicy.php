<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\RulesSkeleton;

use DrdPlus\FrontendSkeleton\Cookie;
use Granam\Strict\Object\StrictObject;

class UsagePolicy extends StrictObject
{
    public const TRIAL_EXPIRED_AT = 'trialExpiredAt';

    /** @var string */
    private $articleName;
    /** @var Request */
    private $request;

    /**
     * @param string $articleName
     * @param \DrdPlus\FrontendSkeleton\Request $request
     * @throws \DrdPlus\RulesSkeleton\Exceptions\ArticleNameCanNotBeEmptyForUsagePolicy
     * @throws \DrdPlus\RulesSkeleton\Exceptions\ArticleNameShouldBeValidName
     * @throws \DrdPlus\FrontendSkeleton\Exceptions\CookieCanNotBeSet
     */
    public function __construct(string $articleName, \DrdPlus\FrontendSkeleton\Request $request)
    {
        $articleName = \trim($articleName);
        if ($articleName === '') {
            throw new Exceptions\ArticleNameCanNotBeEmptyForUsagePolicy('Name of the article to confirm ownership can not be empty');
        }
        if (!\preg_match('~\w~', $articleName)) {
            throw new Exceptions\ArticleNameShouldBeValidName(
                "Name of the article to confirm ownership should contain some meaningful name, got '$articleName'"
            );
        }
        $this->articleName = $articleName;
        $this->request = $request;
        $this->setCookie('ownershipCookieName', $this->getOwnershipName(), null /* expire on session end*/);
        $this->setCookie('trialCookieName', $this->getTrialName(), null /* expire on session end*/);
        $this->setCookie('trialExpiredAtName', $this->getTrialExpiredAtName(), null /* expire on session end*/);
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
        return Cookie::setCookie($cookieName, $value, false /* accessible also via JS */, $expiresAt);
    }

    /**
     * @return bool
     */
    public function hasVisitorConfirmedOwnership(): bool
    {
        return Cookie::getCookie($this->getOwnershipName()) !== null;
    }

    /**
     * @return string
     */
    private function getOwnershipName(): string
    {
        return \str_replace('.', '_', 'confirmedOwnershipOf' . \ucfirst($this->articleName));
    }

    /**
     * @param \DateTime $expiresAt
     * @return bool
     * @throws \RuntimeException
     */
    public function confirmOwnershipOfVisitor(\DateTime $expiresAt): bool
    {
        return $this->setCookie($this->getOwnershipName(), (string)$expiresAt->getTimestamp(), $expiresAt);
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
        return Cookie::getCookie($this->getTrialName()) !== null;
    }

    /**
     * @return string
     */
    public function getTrialName(): string
    {
        return \str_replace('.', '_', 'trialOf' . \ucfirst($this->articleName));
    }

    public function getTrialExpiredAtName(): string
    {
        return static::TRIAL_EXPIRED_AT;
    }

    /**
     * @param \DateTime $expiresAt
     * @return bool
     * @throws \RuntimeException
     */
    public function activateTrial(\DateTime $expiresAt): bool
    {
        return $this->setCookie($this->getTrialName(), (string)$expiresAt->getTimestamp(), $expiresAt);
    }

    public function trialJustExpired(): bool
    {
        // expired before 5 seconds or less
        return !empty($_GET[static::TRIAL_EXPIRED_AT]) && ((int)$_GET[static::TRIAL_EXPIRED_AT] + 5) >= \time();
    }
}
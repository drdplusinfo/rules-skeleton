<?php
namespace DrdPlus\RulesSkeleton;

use Granam\Strict\Object\StrictObject;

class UsagePolicy extends StrictObject
{
    /**
     * @var string
     */
    private $articleNameToConfirmOwnership;

    /**
     * @param string $articleNameToConfirmOwnership
     * @throws \LogicException
     */
    public function __construct(string $articleNameToConfirmOwnership)
    {
        $articleNameToConfirmOwnership = trim($articleNameToConfirmOwnership);
        if ($articleNameToConfirmOwnership === '') {
            throw new \LogicException('Name of the article to confirm ownership can not be empty');
        }
        $this->articleNameToConfirmOwnership = $articleNameToConfirmOwnership;
    }

    /**
     * @return bool
     */
    public function hasVisitorConfirmedOwnership(): bool
    {
        return !empty($_COOKIE[$this->getCookieName()]);
    }

    /**
     * @return string
     */
    private function getCookieName(): string
    {
        return str_replace('.', '_', 'confirmedOwnershipOf' . ucfirst($this->articleNameToConfirmOwnership));
    }

    /**
     * @return bool
     * @throws \RuntimeException
     */
    public function confirmOwnershipOfVisitor(): bool
    {
        $cookieSet = \setcookie(
            $this->getCookieName(),
            '1', // value
            (new \DateTime('+ 1 year'))->getTimestamp(), // expires at
            '/', // path
            $_SERVER['SERVER_NAME'], // domain
            !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', // secure
            true // http only
        );
        if (!$cookieSet) {
            throw new \RuntimeException('Could not set acceptance cookie');
        }
        $_COOKIE[$this->getCookieName()] = '1';

        return true;
    }
}
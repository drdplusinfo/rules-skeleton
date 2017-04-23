<?php
namespace DrdPlus\RulesSkeleton;

use Granam\Strict\Object\StrictObject;
use GuzzleHttp\Cookie\SetCookie;

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
        return 'confirmedOwnershipOf' . $this->articleNameToConfirmOwnership;
    }

    /**
     * @return bool
     */
    public function confirmOwnershipOfVisitor(): bool
    {
        $cookie = new SetCookie([
            'Name' => $this->getCookieName(),
            'Value' => '1',
            'Domain' => $_SERVER['SERVER_NAME'],
            'Expires' => (new \DateTime('+ 1 year'))->format(\DATE_COOKIE),
            'Secure' => true,
            'HttpOnly' => true,
        ]);
        if ($cookie->validate() !== true) {
            trigger_error('Current cookie is not valid: ' . $cookie . ' (' . $cookie->validate() . ')', E_USER_WARNING);

            return false;
        }
        header($cookie->__toString());
        $_COOKIE[$this->getCookieName()] = '1';

        return true;
    }
}
<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\Tests\RulesSkeleton;

class TestsConfiguration extends \DrdPlus\Tests\FrontendSkeleton\TestsConfiguration
{
    public const HAS_PROTECTED_ACCESS = 'has_protected_access';

    // every setting SHOULD be strict (expecting instead of ignoring)

    /** @var bool */
    private $hasProtectedAccess = true;

    /**
     * @return bool
     */
    public function hasProtectedAccess(): bool
    {
        return $this->hasProtectedAccess;
    }

    /**
     * @param bool $hasProtectedAccess
     * @return TestsConfiguration
     */
    public function setHasProtectedAccess(bool $hasProtectedAccess): TestsConfiguration
    {
        $this->hasProtectedAccess = $hasProtectedAccess;

        return $this;
    }
}
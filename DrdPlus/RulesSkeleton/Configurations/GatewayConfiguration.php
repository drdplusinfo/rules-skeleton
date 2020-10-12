<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Configurations;

class GatewayConfiguration extends SubMenu
{
    public const PROTECTED_ACCESS = 'protected_access';

    /** @var array */
    private $values;

    public function __construct(array $settings, array $pathToMenu)
    {
        $this->guardProtectedAccessIsSet($settings, $pathToMenu);
        $this->values = $settings;
    }

    protected function guardProtectedAccessIsSet(array $settings, array $pathToMenu): void
    {
        $this->guardConfigurationSettingIsSet(static::PROTECTED_ACCESS, $settings, $pathToMenu);
        $this->guardConfigurationSettingIsBoolean(static::PROTECTED_ACCESS, $settings, $pathToMenu);
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function hasProtectedAccess(): bool
    {
        return (bool)$this->getValues()[self::PROTECTED_ACCESS];
    }
}

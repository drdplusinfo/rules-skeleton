<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Configurations;

use Granam\Strict\Object\StrictObject;

class MenuConfiguration extends StrictObject
{
    public const POSITION_FIXED = 'position_fixed';
    public const SHOW_HOME_BUTTON_ON_HOMEPAGE = 'show_home_button_on_homepage';
    public const SHOW_HOME_BUTTON_ON_ROUTES = 'show_home_button_on_routes';
    public const HOME_BUTTON_TARGET = 'home_button_target';

    /** @var array */
    private $settings;

    public function __construct(array $settings, array $pathToMenu)
    {
        $this->guardFixedMenuPositionUsageIsSet($settings, $pathToMenu);
        $this->guardShowOfHomeButtonOnHomepageIsSet($settings, $pathToMenu);
        $this->guardShowOfHomeButtonOnRoutesIsSet($settings, $pathToMenu);
        $this->guardHomeButtonTargetIsSet($settings, $pathToMenu);
        $this->settings = $settings;
    }

    protected function guardFixedMenuPositionUsageIsSet(array $settings, array $pathToMenu): void
    {
        $this->guardConfigurationSettingIsSet(static::POSITION_FIXED, $settings, $pathToMenu);
        $this->guardConfigurationSettingIsBoolean(static::POSITION_FIXED, $settings, $pathToMenu);
    }

    protected function guardConfigurationSettingIsSet(string $settingsKey, array $settings, array $pathToMenu): void
    {
        if (($settings[$settingsKey] ?? null) === null) {
            throw new Exceptions\InvalidConfiguration(
                sprintf(
                    "Expected explicitly defined configuration '%s', got nothing",
                    $this->getConfigurationPath($settingsKey, $pathToMenu)
                )
            );
        }
    }

    protected function guardConfigurationSettingIsBoolean(string $settingsKey, array $settings, array $pathToMenu): void
    {
        if (!is_bool($settings[$settingsKey])) {
            throw new Exceptions\InvalidConfiguration(
                sprintf(
                    "Expected configuration '%s' to be a boolean, got %s",
                    $this->getConfigurationPath($settingsKey, $pathToMenu),
                    var_export($settings[$settingsKey], true)
                )
            );
        }
    }

    protected function getConfigurationPath(string $configurationKey, array $pathToMenu): string
    {
        $configurationPath = $pathToMenu;
        $configurationPath[] = $configurationKey;
        return implode('.', $configurationPath);
    }

    protected function guardShowOfHomeButtonOnHomepageIsSet(array $settings, array $pathToMenu): void
    {
        $this->guardConfigurationSettingIsSet(static::SHOW_HOME_BUTTON_ON_HOMEPAGE, $settings, $pathToMenu);
        $this->guardConfigurationSettingIsBoolean(static::SHOW_HOME_BUTTON_ON_HOMEPAGE, $settings, $pathToMenu);
    }

    protected function guardShowOfHomeButtonOnRoutesIsSet(array $settings, array $pathToMenu): void
    {
        $this->guardConfigurationSettingIsSet(static::SHOW_HOME_BUTTON_ON_ROUTES, $settings, $pathToMenu);
        $this->guardConfigurationSettingIsBoolean(static::SHOW_HOME_BUTTON_ON_ROUTES, $settings, $pathToMenu);
    }

    protected function guardHomeButtonTargetIsSet(array $settings, array $pathToMenu): void
    {
        $this->guardConfigurationSettingIsSet(static::HOME_BUTTON_TARGET, $settings, $pathToMenu);
        $this->guardConfigurationSettingIsString(static::HOME_BUTTON_TARGET, $settings, $pathToMenu);
    }

    protected function guardConfigurationSettingIsString(string $settingsKey, array $settings, array $pathToMenu): void
    {
        if (!is_string($settings[$settingsKey]) || $settings[$settingsKey] === '') {
            throw new Exceptions\InvalidConfiguration(
                sprintf(
                    "Expected configuration '%s' to be a non-empty string, got %s",
                    $this->getConfigurationPath($settingsKey, $pathToMenu),
                    var_export($settings[$settingsKey], true)
                )
            );
        }
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function isPositionFixed(): bool
    {
        return (bool)$this->getSettings()[static::POSITION_FIXED];
    }

    public function isShowHomeButtonOnHomepage(): bool
    {
        return ($this->getSettings()[static::SHOW_HOME_BUTTON_ON_HOMEPAGE] ?? false);
    }

    public function isShowHomeButtonOnRoutes(): bool
    {
        return ($this->getSettings()[static::SHOW_HOME_BUTTON_ON_ROUTES] ?? false);
    }

    public function getHomeButtonTarget(): string
    {
        return $this->getSettings()[static::HOME_BUTTON_TARGET];
    }
}

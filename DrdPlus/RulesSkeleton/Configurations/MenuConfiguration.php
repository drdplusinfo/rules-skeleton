<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Configurations;

class MenuConfiguration extends SubMenu
{
    public const POSITION_FIXED = 'position_fixed';
    public const SHOW_HOME_BUTTON_ON_HOMEPAGE = 'show_home_button_on_homepage';
    public const SHOW_HOME_BUTTON_ON_ROUTES = 'show_home_button_on_routes';
    public const HOME_BUTTON_TARGET = 'home_button_target';
    public const ITEMS = 'items';

    /** @var array */
    private $values;

    public function __construct(array $settings, array $pathToMenu)
    {
        $this->guardFixedMenuPositionUsageIsSet($settings, $pathToMenu);
        $this->guardShowOfHomeButtonOnHomepageIsSet($settings, $pathToMenu);
        $this->guardShowOfHomeButtonOnRoutesIsSet($settings, $pathToMenu);
        $this->guardHomeButtonTargetIsSet($settings, $pathToMenu);
        $this->guardItemsAreArrayOrNothing($settings, $pathToMenu);
        $this->values = $settings;
    }

    protected function guardFixedMenuPositionUsageIsSet(array $settings, array $pathToMenu): void
    {
        $this->guardConfigurationSettingIsSet(static::POSITION_FIXED, $settings, $pathToMenu);
        $this->guardConfigurationSettingIsBoolean(static::POSITION_FIXED, $settings, $pathToMenu);
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

    protected function guardItemsAreArrayOrNothing(array $settings, array $pathToMenu)
    {
        if (!array_key_exists(static::ITEMS, $settings)) {
            return;
        }
        $this->guardConfigurationSettingIsObject(static::ITEMS, $settings, $pathToMenu);
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function isPositionFixed(): bool
    {
        return (bool)$this->getValues()[static::POSITION_FIXED];
    }

    public function isShowHomeButtonOnHomepage(): bool
    {
        return ($this->getValues()[static::SHOW_HOME_BUTTON_ON_HOMEPAGE] ?? false);
    }

    public function isShowHomeButtonOnRoutes(): bool
    {
        return ($this->getValues()[static::SHOW_HOME_BUTTON_ON_ROUTES] ?? false);
    }

    public function getHomeButtonTarget(): string
    {
        return $this->getValues()[static::HOME_BUTTON_TARGET];
    }

    /**
     * @return array|string[]
     */
    public function getItems(): array
    {
        return $this->getValues()[static::ITEMS] ?? [];
    }
}

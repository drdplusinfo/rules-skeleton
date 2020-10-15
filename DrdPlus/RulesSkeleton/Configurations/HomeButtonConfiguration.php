<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Configurations;

class HomeButtonConfiguration extends AbstractConfiguration
{
    public const SHOW_ON_HOMEPAGE = 'show_on_homepage';
    public const SHOW_ON_ROUTES = 'show_on_routes';
    public const TARGET = 'target';
    public const IMAGE = 'image';

    /** @var array */
    private $values;

    public function __construct(array $values, array $pathToHomeButton)
    {
        $this->guardShowOnHomePageIsSet($values, $pathToHomeButton);
        $this->guardShowOnRoutesIsSet($values, $pathToHomeButton);
        $this->values = $values;
        if ($this->showOnHomePage() || $this->showOnRoutes()) {
            $values[self::TARGET] = $values[self::TARGET] ?? '/';
            $values[self::IMAGE] = $values[self::IMAGE]
                ?? str_replace(
                    __DIR__ . '/../../..',
                    '',
                    __DIR__ . '/../../../images/generic/skeleton/drdplus-dragon-menu-2x22.png'
                );
            $this->guardTargetIsSet($values, $pathToHomeButton);
            $this->guardImageIsSet($values, $pathToHomeButton);
        }
    }

    protected function guardShowOnHomePageIsSet(array $settings, array $pathToMenu): void
    {
        $this->guardConfigurationValueIsSet(static::SHOW_ON_HOMEPAGE, $settings, $pathToMenu);
        $this->guardConfigurationValueIsBoolean(static::SHOW_ON_HOMEPAGE, $settings, $pathToMenu);
    }

    protected function guardShowOnRoutesIsSet(array $settings, array $pathToMenu): void
    {
        $this->guardConfigurationValueIsSet(static::SHOW_ON_ROUTES, $settings, $pathToMenu);
        $this->guardConfigurationValueIsBoolean(static::SHOW_ON_ROUTES, $settings, $pathToMenu);
    }

    protected function guardTargetIsSet(array $settings, array $pathToMenu): void
    {
        $this->guardConfigurationValueIsSet(static::TARGET, $settings, $pathToMenu);
        $this->guardConfigurationValueIsNonEmptyString(static::TARGET, $settings, $pathToMenu);
    }

    protected function guardImageIsSet(array $settings, array $pathToMenu): void
    {
        $this->guardConfigurationValueIsSet(static::IMAGE, $settings, $pathToMenu);
        $this->guardConfigurationValueIsNonEmptyString(static::IMAGE, $settings, $pathToMenu);
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function showOnHomePage(): bool
    {
        return (bool)$this->getValues()[self::SHOW_ON_HOMEPAGE];
    }

    public function showOnRoutes(): bool
    {
        return (bool)$this->getValues()[self::SHOW_ON_ROUTES];
    }

    public function getTarget(): string
    {
        return $this->getValues()[self::TARGET] ?? '';
    }

    public function getImage(): string
    {
        return $this->getValues()[self::IMAGE] ?? '';
    }
}

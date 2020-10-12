<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Configurations;

use Granam\Strict\Object\StrictObject;

abstract class SubMenu extends StrictObject implements ConfigurationValues
{
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

    protected function guardConfigurationSettingIsObject(string $settingsKey, array $settings, array $pathToMenu): void
    {
        if (!is_array($settings[$settingsKey])) {
            throw new Exceptions\InvalidConfiguration(
                sprintf(
                    "Expected configuration '%s' to be a non-empty array, got %s",
                    $this->getConfigurationPath($settingsKey, $pathToMenu),
                    var_export($settings[$settingsKey], true)
                )
            );
        }
        foreach ($settings[$settingsKey] as $itemKey => $itemValue) {
            if (!is_string($itemKey)) {
                throw new Exceptions\InvalidConfiguration(
                    sprintf(
                        "Expected configuration '%s' to be an array indexed only by strings, got key %s (with value '%s')",
                        $this->getConfigurationPath($settingsKey, $pathToMenu),
                        var_export($itemValue, true),
                        $itemValue
                    )
                );
            }
        }
    }
}

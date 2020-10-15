<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Configurations;

use Granam\Strict\Object\StrictObject;

abstract class AbstractConfiguration extends StrictObject implements ConfigurationValues
{
    protected function guardConfigurationValueIsSet(string $valueKey, array $values, array $pathToConfiguration): void
    {
        if (($values[$valueKey] ?? null) === null) {
            throw new Exceptions\InvalidConfiguration(
                sprintf(
                    "Expected explicitly defined configuration '%s', got nothing",
                    $this->getConfigurationPath($valueKey, $pathToConfiguration)
                )
            );
        }
    }

    protected function guardConfigurationValueIsBoolean(string $valueKey, array $values, array $pathToConfiguration): void
    {
        if (!is_bool($values[$valueKey])) {
            throw new Exceptions\InvalidConfiguration(
                sprintf(
                    "Expected configuration '%s' to be a boolean, got %s",
                    $this->getConfigurationPath($valueKey, $pathToConfiguration),
                    var_export($values[$valueKey], true)
                )
            );
        }
    }

    protected function getConfigurationPath(string $configurationKey, array $pathToConfiguration): string
    {
        $configurationPath = $pathToConfiguration;
        $configurationPath[] = $configurationKey;
        return implode('.', $configurationPath);
    }

    protected function guardConfigurationValueIsNonEmptyString(string $valueKey, array $values, array $pathToConfiguration): void
    {
        if (!is_string($values[$valueKey]) || $values[$valueKey] === '') {
            throw new Exceptions\InvalidConfiguration(
                sprintf(
                    "Expected configuration '%s' to be a non-empty string, got %s",
                    $this->getConfigurationPath($valueKey, $pathToConfiguration),
                    var_export($values[$valueKey], true)
                )
            );
        }
    }

    protected function guardConfigurationValueIsObject(string $valueKey, array $values, array $pathToConfiguration): void
    {
        if (!is_array($values[$valueKey])) {
            throw new Exceptions\InvalidConfiguration(
                sprintf(
                    "Expected configuration '%s' to be a non-empty array, got %s",
                    $this->getConfigurationPath($valueKey, $pathToConfiguration),
                    var_export($values[$valueKey], true)
                )
            );
        }
        foreach ($values[$valueKey] as $itemKey => $itemValue) {
            if (!is_string($itemKey)) {
                throw new Exceptions\InvalidConfiguration(
                    sprintf(
                        "Expected configuration '%s' to be an array indexed only by strings, got key %s (with value '%s')",
                        $this->getConfigurationPath($valueKey, $pathToConfiguration),
                        var_export($itemValue, true),
                        $itemValue
                    )
                );
            }
        }
    }

    protected function diveConfigurationStructure(string $oldKey, string $subConfigurationKey, string $newKey, array $values): array
    {
        if (array_key_exists($oldKey, $values)) {
            if (!array_key_exists(MenuConfiguration::POSITION_FIXED, $values[$subConfigurationKey])) {
                $values[$subConfigurationKey][$newKey] = $values[$oldKey];
            }
            unset($values[$oldKey]);
        }
        return $values;
    }
}

<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Configurations;

class PrefetchConfiguration extends AbstractConfiguration
{
    public const ANCHORS_REGEXP = 'anchors_regexp';

    public function __construct(array $values, array $pathToMenu)
    {
        $values = $this->addDefaultValuesToOptionalParameters($values);
        $values = $this->sanitizeValues($values);
        $this->guardAnchorsRegexpIsValidOrNothing($values, $pathToMenu);
        parent::__construct($values);
    }

    protected function addDefaultValuesToOptionalParameters(array $values): array
    {
        $values[static::ANCHORS_REGEXP] = $values[static::ANCHORS_REGEXP] ?? '';
        return $values;
    }

    protected function sanitizeValues(array $values): array
    {
        $values[static::ANCHORS_REGEXP] = trim($values[static::ANCHORS_REGEXP]);
        return $values;
    }

    protected function guardAnchorsRegexpIsValidOrNothing(array $values, array $pathToMenu): void
    {
        if ($values[static::ANCHORS_REGEXP] === '') {
            return;
        }
        $this->guardConfigurationValueIsNonEmptyString(static::ANCHORS_REGEXP, $values, $pathToMenu);
        $this->guardConfigurationValueIsValidRegexp(static::ANCHORS_REGEXP, $values, $pathToMenu);
    }

    public function getAnchorsRegexp(): string
    {
        return $this->getValues()[self::ANCHORS_REGEXP];
    }
}

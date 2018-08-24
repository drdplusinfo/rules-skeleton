<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

/**
 * @method static Configuration createFromYml(Dirs $dirs)
 * @method Dirs getDirs
 */
class Configuration extends \DrdPlus\FrontendSkeleton\Configuration
{
    public const FREE_ACCESS = 'free_access';
    public const HIDE_HOME_BUTTON = 'hide_home_button';
    public const ESHOP_URL = 'eshop_url';

    public function __construct(Dirs $dirs, array $settings)
    {
        $this->guardValidEshopUrl($settings);
        parent::__construct($dirs, $settings);
    }

    /**
     * @param array $settings
     * @throws \DrdPlus\RulesSkeleton\Exceptions\InvalidEshopUrl
     */
    protected function guardValidEshopUrl(array $settings): void
    {
        if (!\filter_var($settings[self::WEB][self::ESHOP_URL] ?? '', FILTER_VALIDATE_URL)) {
            throw new Exceptions\InvalidEshopUrl(
                'Given e-shop URL is not valid, expected some URL in configuration '
                . static::WEB . ': ' . static::ESHOP_URL . ', got ' . ($settings[self::WEB][self::ESHOP_URL] ?? 'nothing')
            );
        }
    }

    public function hasFreeAccess(): bool
    {
        return (bool)$this->getSettings()[self::WEB][self::FREE_ACCESS];
    }

    public function shouldHideHomeButton(): bool
    {
        return (bool)$this->getSettings()[self::WEB][self::HIDE_HOME_BUTTON];
    }

    public function getEshopUrl(): string
    {
        return $this->getSettings()[self::WEB][self::ESHOP_URL];
    }
}
<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Configurations;

use Granam\Strict\Object\StrictObject;
use Granam\YamlReader\YamlFileReader;

class Configuration extends StrictObject implements ProjectUrlConfiguration
{
    public const CONFIG_LOCAL_YML = 'config.local.yml';
    public const CONFIG_DISTRIBUTION_YML = 'config.distribution.yml';

    public static function canCreateFromYml(Dirs $dirs): bool
    {
        return is_file($dirs->getProjectRoot() . '/' . static::CONFIG_DISTRIBUTION_YML)
            && is_readable($dirs->getProjectRoot() . '/' . static::CONFIG_DISTRIBUTION_YML);
    }

    public static function createFromYml(Dirs $dirs): Configuration
    {
        $globalConfig = new YamlFileReader($dirs->getProjectRoot() . '/' . static::CONFIG_DISTRIBUTION_YML);
        $config = $globalConfig->getValues();
        $localConfigFile = $dirs->getProjectRoot() . '/' . static::CONFIG_LOCAL_YML;
        if (\file_exists($localConfigFile)) {
            $localConfig = new YamlFileReader($dirs->getProjectRoot() . '/' . static::CONFIG_LOCAL_YML);
            $config = \array_replace_recursive($config, $localConfig->getValues());
        }

        return new static($dirs, $config);
    }

    // moved from web and deprecated by old way
    public const MENU_POSITION_FIXED = 'menu_position_fixed';
    public const SHOW_HOME_BUTTON = 'show_home_button';
    public const SHOW_HOME_BUTTON_ON_HOMEPAGE = 'show_home_button_on_homepage';
    public const SHOW_HOME_BUTTON_ON_ROUTES = 'show_home_button_on_routes';
    public const HOME_BUTTON_TARGET = 'home_button_target';

    // web
    public const WEB = 'web';
    public const NAME = 'name';
    public const TITLE_SMILEY = 'title_smiley';
    public const PROTECTED_ACCESS = 'protected_access';
    public const ESHOP_URL = 'eshop_url';
    public const FAVICON = 'favicon';
    public const DEFAULT_PUBLIC_TO_LOCAL_URL_PART_REGEXP = 'default_public_to_local_url_part_regexp';
    public const DEFAULT_PUBLIC_TO_LOCAL_URL_PART_REPLACEMENT = 'default_public_to_local_url_part_replacement';
    public const MENU = 'menu';
    // google
    public const GOOGLE = 'google';
    public const ANALYTICS_ID = 'analytics_id';
    // application
    public const APPLICATION = 'application';
    public const YAML_FILE_WITH_ROUTES = 'yaml_file_with_routes';
    public const DEFAULT_YAML_FILE_WITH_ROUTES = 'default_yaml_file_with_routes';

    /** @var Dirs */
    private $dirs;
    /** @var MenuConfiguration */
    private $menuConfiguration;
    /** @var array */
    private $settings;

    /**
     * @param Dirs $dirs
     * @param array $settings
     */
    public function __construct(Dirs $dirs, array $settings)
    {
        $this->dirs = $dirs;
        $this->menuConfiguration = $this->createMenuConfiguration($settings);

        $this->guardValidGoogleAnalyticsId($settings);
        $this->guardNonEmptyWebName($settings);
        $this->guardSetTitleSmiley($settings);
        $this->guardValidEshopUrl($settings);
        $this->guardSetProtectedAccess($settings);
        $this->guardValidFaviconUrl($settings);
        $this->settings = $settings;
    }

    // MENU

    protected function createMenuConfiguration(array $settings): MenuConfiguration
    {
        $this->guardFixedMenuPositionIsNotSetByOldWay($settings);
        $this->guardShowHomeButtonIsNotSetByOldWay($settings);
        $this->guardShowHomeButtonOnHomepageIsNotSetByOldWay($settings);
        $this->guardShowHomeButtonOnRoutesIsNotSetByOldWay($settings);
        $this->guardHomeButtonTargetIsNotSetByOldWay($settings);

        $this->guardMenuConfigurationExists($settings);
        return new MenuConfiguration($settings[static::WEB][static::MENU], [static::WEB, static::MENU]);
    }

    protected function guardMenuConfigurationExists(array $settings)
    {
        if (!is_array($settings[static::WEB][static::MENU] ?? null)) {
            throw new Exceptions\InvalidConfiguration(
                sprintf("Missing configuration '%s'", implode('.', [static::WEB, static::MENU]))
            );
        }
    }

    /**
     * @param array $settings
     * @throws \DrdPlus\RulesSkeleton\Configurations\Exceptions\ConfigurationDirectiveDeprecated
     */
    protected function guardFixedMenuPositionIsNotSetByOldWay(array $settings): void
    {
        if (array_key_exists(static::MENU_POSITION_FIXED, $settings[static::WEB])) {
            $this->throwDeprecatedConfigurationUsage(static::MENU_POSITION_FIXED, [static::WEB, static::MENU, MenuConfiguration::POSITION_FIXED]);
        }
    }

    protected function throwDeprecatedConfigurationUsage(string $oldKey, array $newPath): void
    {
        throw new Exceptions\ConfigurationDirectiveDeprecated(
            sprintf('%s is deprecated, use %s instead', implode('.', [static::WEB, $oldKey]), implode('.', $newPath))
        );
    }

    protected function guardShowHomeButtonIsNotSetByOldWay(array $settings): void
    {
        if (array_key_exists(self::SHOW_HOME_BUTTON, $settings[static::WEB])) {
            $this->throwDeprecatedHomeButtonUsage();
        }
    }

    protected function throwDeprecatedHomeButtonUsage()
    {
        throw new Exceptions\ConfigurationDirectiveDeprecated(
            sprintf(
                '%s is deprecated, use %s and %s instead',
                implode('.', [static::WEB, static::SHOW_HOME_BUTTON]),
                implode('.', [static::WEB, static::MENU, MenuConfiguration::SHOW_HOME_BUTTON_ON_HOMEPAGE]),
                implode('.', [static::WEB, static::MENU, MenuConfiguration::SHOW_HOME_BUTTON_ON_ROUTES])
            )
        );
    }

    protected function guardShowHomeButtonOnHomepageIsNotSetByOldWay(array $settings)
    {
        if (array_key_exists(self::SHOW_HOME_BUTTON_ON_HOMEPAGE, $settings[static::WEB])) {
            $this->throwDeprecatedHomeButtonOnHomepageUsage();
        }
    }

    protected function throwDeprecatedHomeButtonOnHomepageUsage()
    {
        $this->throwDeprecatedConfigurationUsage(
            static::SHOW_HOME_BUTTON_ON_HOMEPAGE,
            [static::WEB, static::MENU, MenuConfiguration::SHOW_HOME_BUTTON_ON_HOMEPAGE]
        );
    }

    protected function guardShowHomeButtonOnRoutesIsNotSetByOldWay(array $settings)
    {
        if (array_key_exists(self::SHOW_HOME_BUTTON_ON_ROUTES, $settings[static::WEB])) {
            $this->throwDeprecatedHomeButtonOnRoutesUsage();
        }
    }

    protected function throwDeprecatedHomeButtonOnRoutesUsage()
    {
        $this->throwDeprecatedConfigurationUsage(
            static::SHOW_HOME_BUTTON_ON_ROUTES,
            [static::WEB, static::MENU, MenuConfiguration::SHOW_HOME_BUTTON_ON_ROUTES]
        );
    }

    protected function guardHomeButtonTargetIsNotSetByOldWay(array $settings)
    {
        if (array_key_exists(self::HOME_BUTTON_TARGET, $settings[static::WEB])) {
            $this->throwDeprecatedHomeButtonTargetUsage();
        }
    }

    protected function throwDeprecatedHomeButtonTargetUsage()
    {
        $this->throwDeprecatedConfigurationUsage(
            static::HOME_BUTTON_TARGET,
            [static::WEB, static::MENU, MenuConfiguration::HOME_BUTTON_TARGET]
        );
    }

    // WEB

    /**
     * @param array $settings
     * @throws \DrdPlus\RulesSkeleton\Configurations\Exceptions\InvalidGoogleAnalyticsId
     */
    protected function guardValidGoogleAnalyticsId(array $settings): void
    {
        if (!\preg_match('~^UA-121206931-\d+$~', $settings[static::GOOGLE][static::ANALYTICS_ID] ?? '')) {
            throw new Exceptions\InvalidGoogleAnalyticsId(
                sprintf(
                    'Expected something like UA-121206931-1 in configuration %s.%s, got %s',
                    static::GOOGLE,
                    static::ANALYTICS_ID,
                    $settings[static::GOOGLE][static::ANALYTICS_ID] ?? 'nothing'
                )
            );
        }
    }

    /**
     * @param array $settings
     * @throws \DrdPlus\RulesSkeleton\Configurations\Exceptions\InvalidConfiguration
     */
    protected function guardNonEmptyWebName(array $settings): void
    {
        if (($settings[static::WEB][static::NAME] ?? '') === '') {
            throw new Exceptions\InvalidConfiguration(
                sprintf(
                    'Expected some web name in configuration %s.%s',
                    static::WEB,
                    static::NAME
                )
            );
        }
    }

    /**
     * @param array $settings
     * @throws \DrdPlus\RulesSkeleton\Configurations\Exceptions\InvalidConfiguration
     */
    protected function guardSetTitleSmiley(array $settings): void
    {
        if (!\array_key_exists(static::TITLE_SMILEY, $settings[static::WEB])) {
            throw new Exceptions\InvalidConfiguration(
                sprintf(
                    'Title smiley should be set in configuration %s.%s, even if just an empty string',
                    static::WEB,
                    static::TITLE_SMILEY
                )
            );
        }
    }

    /**
     * @param array $settings
     * @throws \DrdPlus\RulesSkeleton\Configurations\Exceptions\InvalidEshopUrl
     */
    protected function guardValidEshopUrl(array $settings): void
    {
        if (!empty($settings[static::WEB][static::PROTECTED_ACCESS])
            && !\filter_var($settings[static::WEB][static::ESHOP_URL] ?? '', FILTER_VALIDATE_URL)
        ) {
            throw new Exceptions\InvalidEshopUrl(
                sprintf(
                    'Given e-shop URL is not valid, expected some URL in configuration %s.%s, got %s',
                    static::WEB,
                    static::ESHOP_URL,
                    $settings[static::WEB][static::ESHOP_URL] ?? 'nothing'
                )
            );
        }
    }

    protected function guardSetProtectedAccess(array $settings): void
    {
        if (($settings[static::WEB][static::PROTECTED_ACCESS] ?? null) === null) {
            throw new Exceptions\InvalidConfiguration(
                sprintf(
                    'Configuration if web has protected access is missing in configuration %s.%s',
                    static::WEB,
                    static::PROTECTED_ACCESS
                )
            );
        }
    }

    protected function guardValidFaviconUrl(array $settings): void
    {
        $favicon = $settings[static::WEB][static::FAVICON] ?? null;
        if ($favicon === null) {
            return;
        }
        if (!\filter_var($favicon, \ FILTER_VALIDATE_URL)
            && !\file_exists($this->getDirs()->getProjectRoot() . '/' . \ltrim($favicon, '/'))
        ) {
            throw new Exceptions\GivenFaviconHasNotBeenFound("Favicon $favicon is not an URL neither readable file");
        }
    }

    public function getMenuConfiguration(): MenuConfiguration
    {
        return $this->menuConfiguration;
    }

    public function getDirs(): Dirs
    {
        return $this->dirs;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getGoogleAnalyticsId(): string
    {
        return $this->getSettings()[static::GOOGLE][static::ANALYTICS_ID];
    }

    /**
     * @deprecated
     */
    public function isMenuPositionFixed()
    {
        throw new Exceptions\DeprecatedConfigurationUsage(
            sprintf(
                "'%s::%s' is deprecaed, use similar from '%s' instead",
                static::class,
                __FUNCTION__,
                MenuConfiguration::class
            )
        );
    }

    /**
     * @deprecated
     */
    public function isShowHomeButton()
    {
        $this->throwDeprecatedHomeButtonUsage();
    }

    /**
     * @deprecated
     */
    public function isShowHomeButtonOnHomepage()
    {
        $this->throwDeprecatedHomeButtonOnHomepageUsage();
    }

    /**
     * @deprecated
     */
    public function isShowHomeButtonOnRoutes()
    {
        $this->throwDeprecatedHomeButtonOnRoutesUsage();
    }

    /**
     * @deprecated
     */
    public function getHomeButtonTarget()
    {
        $this->throwDeprecatedHomeButtonTargetUsage();
    }

    public function getWebName(): string
    {
        return $this->getSettings()[static::WEB][static::NAME];
    }

    public function getTitleSmiley(): string
    {
        return (string)$this->getSettings()[static::WEB][static::TITLE_SMILEY];
    }

    public function hasProtectedAccess(): bool
    {
        return (bool)$this->getSettings()[self::WEB][self::PROTECTED_ACCESS];
    }

    public function getEshopUrl(): string
    {
        return $this->getSettings()[self::WEB][self::ESHOP_URL] ?? '';
    }

    public function getFavicon(): string
    {
        return $this->getSettings()[static::WEB][static::FAVICON] ?? '';
    }

    public function getYamlFileWithRoutes(): string
    {
        return $this->getSettings()[static::APPLICATION][static::YAML_FILE_WITH_ROUTES] ?? '';
    }

    public function getDefaultYamlFileWithRoutes(): string
    {
        return $this->getSettings()[static::APPLICATION][static::DEFAULT_YAML_FILE_WITH_ROUTES] ?? 'routes.yml';
    }

    public function getPublicUrlPartRegexp(): string
    {
        return $this->getSettings()[static::WEB][static::DEFAULT_PUBLIC_TO_LOCAL_URL_PART_REGEXP]
            ?? '~https?://((?:[^.]+[.])*)drdplus[.]info~';
    }

    public function getPublicToLocalUrlReplacement(): string
    {
        return $this->getSettings()[static::WEB][static::DEFAULT_PUBLIC_TO_LOCAL_URL_PART_REPLACEMENT]
            ?? 'http://$1drdplus.loc';
    }

}
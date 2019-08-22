<?php declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton\Partials;

use DeviceDetector\Parser\Bot;
use DrdPlus\RulesSkeleton\Cache;
use DrdPlus\RulesSkeleton\Configuration;
use DrdPlus\RulesSkeleton\CookiesService;
use DrdPlus\RulesSkeleton\Dirs;
use DrdPlus\RulesSkeleton\Environment;
use DrdPlus\RulesSkeleton\Request;
use DrdPlus\RulesSkeleton\RulesApplication;
use DrdPlus\RulesSkeleton\CurrentWebVersion;

trait ClassesTrait
{
    /**
     * @return string|CurrentWebVersion
     */
    protected function getCurrentWebVersionClass(): string
    {
        return CurrentWebVersion::class;
    }

    /**
     * @return string|Cache
     */
    protected function getCacheClass(): string
    {
        return Cache::class;
    }

    /**
     * @return string|Configuration
     */
    protected function getConfigurationClass(): string
    {
        return Configuration::class;
    }

    /**
     * @return string|Request
     */
    protected function getRequestClass(): string
    {
        return Request::class;
    }

    /**
     * @return string|CookiesService
     */
    protected function getCookiesServiceClass()
    {
        return CookiesService::class;
    }

    /**
     * @return string|RulesApplication
     */
    protected function getRulesApplicationClass(): string
    {
        return RulesApplication::class;
    }

    /**
     * @return string|Dirs
     */
    protected function getDirsClass(): string
    {
        return Dirs::class;
    }

    /**
     * @return string|Bot
     */
    protected function getBotClass(): string
    {
        return Bot::class;
    }

    /**
     * @return string|Environment
     */
    protected function getEnvironmentClass(): string
    {
        return Environment::class;
    }
}
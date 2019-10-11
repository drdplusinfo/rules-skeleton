<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

use Granam\Strict\Object\StrictObject;

class RouterCache extends StrictObject
{
    /**
     * @var Cache
     */
    private $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function getRouterCacheDir(): string
    {
        $routedCacheDir = $this->cache->getCacheDir() . '/router';
        if (!$this->cache->isInProduction()) {
            $routedCacheDir = uniqid($routedCacheDir . '/', true);
        }
        return $routedCacheDir;
    }
}

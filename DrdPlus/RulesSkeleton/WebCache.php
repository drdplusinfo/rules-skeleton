<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

use Granam\Git\Git;

class WebCache extends Cache
{
    public function __construct(
        CurrentWebVersions $webVersions,
        Dirs $dirs,
        Request $request,
        Git $git,
        bool $isInProduction,
        string $cachePrefix = null
    )
    {
        parent::__construct($webVersions, $dirs, $request, $git, $isInProduction, $cachePrefix ?? 'page-' . \md5($dirs->getCacheRoot()));
    }
}
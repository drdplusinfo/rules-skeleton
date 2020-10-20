<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

use DrdPlus\RulesSkeleton\Configurations\Configuration;
use DrdPlus\RulesSkeleton\Configurations\Dirs;
use Granam\Git\Git;

class WebCache extends Cache
{
    public const TABLES = 'tables';
    public const GATEWAY = 'gateway';
    public const PASSED_GATEWAY = 'passed_gateway';
    public const NOT_FOUND = 'not_found';
    public const DUMMY = 'dummy';
    public const ROUTER = 'router';

    public function __construct(
        CurrentWebVersion $currentWebVersion,
        Dirs $dirs,
        string $cacheSubDir,
        Request $request,
        ContentIrrelevantRequestAliases $contentIrrelevantRequestAliases,
        ContentIrrelevantParametersFilter $contentIrrelevantParametersFilter,
        Git $git,
        Configuration $configuration,
        bool $isInProduction
    )
    {
        parent::__construct(
            $currentWebVersion,
            $dirs->getProjectRoot(),
            $dirs->getCacheRoot() . '/web/' . $cacheSubDir,
            $request,
            $contentIrrelevantRequestAliases,
            $contentIrrelevantParametersFilter,
            $git,
            $configuration,
            $isInProduction
        );
    }

}

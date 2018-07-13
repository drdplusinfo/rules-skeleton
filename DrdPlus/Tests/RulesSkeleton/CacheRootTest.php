<?php
namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\FrontendSkeleton\CacheRoot;

class CacheRootTest extends \DrdPlus\Tests\FrontendSkeleton\CacheRootTest
{
    protected static function getSutClass(string $sutTestClass = null, string $regexp = '~\\\Tests(.+)Test$~'): string
    {
        return CacheRoot::class;
    }

}
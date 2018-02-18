<?php
namespace DrdPlus\RulesSkeleton;

class PassCache extends Cache
{
    protected function getCachePrefix(): string
    {
        return 'pass';
    }

}
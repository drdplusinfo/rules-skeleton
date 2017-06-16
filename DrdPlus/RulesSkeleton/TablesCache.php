<?php
namespace DrdPlus\RulesSkeleton;

class TablesCache extends Cache
{
    protected function getCachePrefix(): string
    {
        return 'tables';
    }

}
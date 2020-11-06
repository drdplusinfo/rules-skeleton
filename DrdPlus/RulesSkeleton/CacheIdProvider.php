<?php

namespace DrdPlus\RulesSkeleton;

interface CacheIdProvider
{
    public function getCacheId(): string;
}
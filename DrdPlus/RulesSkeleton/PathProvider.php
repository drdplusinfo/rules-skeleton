<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

class PathProvider
{
    /**
     * @var RulesUrlMatcher
     */
    private $urlMatcher;
    /**
     * @var string
     */
    private $url;

    public function __construct(RulesUrlMatcher $urlMatcher, string $url)
    {
        $this->urlMatcher = $urlMatcher;
        $this->url = $url;
    }

    public function getPath(): string
    {
        $match = $this->urlMatcher->match($this->url);
        return $match->getPath();
    }

}
<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

use Granam\Strict\Object\StrictObject;

class RouteMatchingPathProvider extends StrictObject
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

    /**
     * @return string
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @throws \Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function getMatchingPath(): string
    {
        $match = $this->urlMatcher->match($this->url);
        return $match->getPath();
    }

}

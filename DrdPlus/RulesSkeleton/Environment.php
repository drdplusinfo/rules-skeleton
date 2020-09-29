<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton;

use Granam\Strict\Object\StrictObject;

class Environment extends StrictObject
{
    /** @var string */
    private $phpSapi;
    /** @var string|null */
    private $projectEnvironment;
    /** @var string|null */
    private $remoteAddress;
    /** @var bool */
    private $inForcedProductionMode;

    public static function createFromGlobals(): Environment
    {
        return new static(
            \PHP_SAPI,
            $_ENV['PROJECT_ENVIRONMENT'] ?? null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            !empty($_GET['mode']) && \strpos(\trim($_GET['mode']), 'prod') === 0
        );
    }

    public function __construct(
        string $phpSapi,
        ?string $projectEnvironment,
        ?string $remoteAddress,
        bool $inForcedProductionMode
    )
    {
        $this->phpSapi = $phpSapi;
        $this->projectEnvironment = $projectEnvironment;
        $this->remoteAddress = $remoteAddress;
        $this->inForcedProductionMode = $inForcedProductionMode;
    }

    public function isCliRequest(): bool
    {
        return $this->getPhpSapi() === 'cli';
    }

    public function getPhpSapi(): string
    {
        return $this->phpSapi;
    }

    public function isOnDevEnvironment(): bool
    {
        return $this->projectEnvironment && stripos($this->projectEnvironment, 'dev') === 0;
    }

    public function isOnLocalhost(): bool
    {
        return $this->remoteAddress === '127.0.0.1';
    }

    public function isInProduction(): bool
    {
        return $this->inForcedProductionMode
            || (!$this->isOnDevEnvironment() && (!$this->isCliRequest() && !$this->isOnLocalhost()));
    }
}
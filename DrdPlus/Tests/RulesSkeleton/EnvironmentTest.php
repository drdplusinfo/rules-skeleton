<?php declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\RulesSkeleton\Environment;
use PHPUnit\Framework\TestCase;

class EnvironmentTest extends TestCase
{
    /**
     * @test
     */
    public function I_can_detect_cli_request()
    {
        $environment = new Environment('stone', null, null);
        self::assertFalse($environment->isCliRequest());
        $environment = new Environment('cli', null, null);
        self::assertTrue($environment->isCliRequest());
    }

    /**
     * @test
     */
    public function I_can_get_php_sapi(): void
    {
        $environment = new Environment('stone', null, null);
        self::assertSame('stone', $environment->getPhpSapi());
    }

    /**
     * @test
     * @dataProvider provideValuesForDevelopmentDetection
     * @param string $phpSapi
     * @param string|null $projectEnvironment
     * @param bool $expectedAsDev
     */
    public function I_can_control_development_environment_by_env_variable(string $phpSapi, ?string $projectEnvironment, bool $expectedAsDev)
    {
        $environmentWithoutProject = new Environment($phpSapi, $projectEnvironment, null);
        self::assertSame($expectedAsDev, $environmentWithoutProject->isOnDevEnvironment());
    }

    public function provideValuesForDevelopmentDetection(): array
    {
        return [
            'project environment as NULL' => ['foo', null, false],
            'project environment as strange string' => ['foo', 'unknown', false],
            'project environment as shortest dev name' => ['foo', 'dev', true],
            'project environment as long dev name' => ['foo', 'development', true],
            'project environment as uppercase short dev name' => ['foo', 'DEV', true],
            'project environment as capitalized long dev name' => ['foo', 'Development', true],
        ];
    }

    /**
     * @test
     */
    public function I_can_detect_localhost()
    {
        $environmentWithoutRemoteAddress = new Environment('foo', null, null);
        self::assertFalse($environmentWithoutRemoteAddress->isOnLocalhost(), 'Localhost should not be detected');
        $environmentWithRemoteAddress = new Environment('foo', null, '999.999.999.999');
        self::assertFalse($environmentWithRemoteAddress->isOnLocalhost());
        $environmentWithLocalAddress = new Environment('foo', null, '127.0.0.1');
        self::assertTrue($environmentWithLocalAddress->isOnLocalhost());
    }
}

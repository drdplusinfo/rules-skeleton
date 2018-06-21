<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\Tests\RulesSkeleton;

/**
 * @method string|TestsConfiguration getSutClass
 */
class TestsConfigurationTest extends \DrdPlus\Tests\FrontendSkeleton\TestsConfigurationTest
{
    /**
     * @param string $publicUrl
     * @return \DrdPlus\Tests\FrontendSkeleton\TestsConfiguration|TestsConfiguration
     */
    protected function createSut(string $publicUrl = 'https://example.com'): \DrdPlus\Tests\FrontendSkeleton\TestsConfiguration
    {
        $sutClass = $this->getSutClass();

        return new $sutClass($publicUrl);
    }

    protected function getNonExistingSettersToSkip(): array
    {
        return ['setPublicUrl']; // this has to set via constructor
    }

    /**
     * @test
     */
    public function I_can_set_and_get_public_url(): void
    {
        $testsConfiguration = $this->createSut('https://example.com');
        self::assertSame('https://example.com', $testsConfiguration->getPublicUrl());
    }

    /**
     * @test
     * @expectedException \DrdPlus\Tests\RulesSkeleton\Exceptions\InvalidPublicUrl
     * @expectedExceptionMessageRegExp ~not valid~
     */
    public function I_can_not_create_it_with_invalid_public_url(): void
    {
        $this->createSut('example.com'); // missing protocol
    }

    /**
     * @test
     * @expectedException \DrdPlus\Tests\RulesSkeleton\Exceptions\PublicUrlShouldUseHttps
     * @expectedExceptionMessageRegExp ~HTTPS~
     */
    public function I_can_not_create_it_with_public_url_without_https(): void
    {
        $this->createSut('http://example.com');
    }
}
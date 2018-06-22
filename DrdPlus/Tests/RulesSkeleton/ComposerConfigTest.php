<?php
namespace DrdPlus\Tests\RulesSkeleton;

/**
 * @method TestsConfiguration getTestsConfiguration
 */
class ComposerConfigTest extends \DrdPlus\Tests\FrontendSkeleton\ComposerConfigTest
{
    use AbstractContentTestTrait;

    /**
     * @test
     */
    public function Has_licence_matching_to_access(): void
    {
        $expectedLicence = $this->isSkeletonChecked() || !$this->getTestsConfiguration()->hasProtectedAccess() ? 'MIT' : 'proprietary';
        self::assertSame(
            $expectedLicence,
            static::$composerConfig['license'],
            "Expected licence '$expectedLicence' as content is " . ($this->getTestsConfiguration()->hasProtectedAccess() ? 'not public' : 'public')
        );
    }

    /**
     * @test
     */
    public function Frontend_cache_is_warmed_up_after_libraries_installation(): void
    {
        $postInstallScripts = static::$composerConfig['scripts']['post-install-cmd'] ?? [];
        self::assertNotEmpty($postInstallScripts, 'Missing post-install-cmd scripts');
        $cacheWarmUpScript = 'wget ' . $this->getTestsConfiguration()->getPublicUrl()
            . ($this->getTestsConfiguration()->hasProtectedAccess() ? ' --post-data="trial=1"' : '')
            . ' --background --output-document=- --output-file=/dev/null >> /dev/null';
        self::assertContains(
            $cacheWarmUpScript,
            $postInstallScripts,
            'Missing script to warm up frontend cache, there are configs '
            . \preg_replace('~^Array\n\((.+)\)~', '$1', \var_export($postInstallScripts, true))

        );
    }
}
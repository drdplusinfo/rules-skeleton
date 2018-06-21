<?php
namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\Tests\FrontendSkeleton\AbstractContentTest;

/**
 * @method TestsConfiguration getTestsConfiguration
 */
class ComposerConfigTest extends AbstractContentTest
{
    protected static $composerConfig;

    protected function setUp(): void
    {
        parent::setUp();
        if (static::$composerConfig === null) {
            $composerFilePath = $this->getDocumentRoot() . '/composer.json';
            self::assertFileExists($composerFilePath, 'composer.json has not been found in document root');
            $content = \file_get_contents($composerFilePath);
            self::assertNotEmpty($content, "Nothing has been fetched from $composerFilePath, is readable?");
            static::$composerConfig = \json_decode($content, true /*as array */);
            self::assertNotEmpty(static::$composerConfig, 'Can not decode composer.json content');
        }
    }

    /**
     * @test
     */
    public function Project_is_using_php_with_nullable_type_hints(): void
    {
        $requiredPhpVersion = static::$composerConfig['require']['php'];
        self::assertGreaterThan(0, \preg_match('~(?<version>\d.+)$~', $requiredPhpVersion, $matches));
        $minimalPhpVersion = $matches['version'];
        self::assertGreaterThanOrEqual(
            0,
            \version_compare($minimalPhpVersion, '7.1'), "Required PHP version should be equal or greater to 7.1, get $requiredPhpVersion"
        );
    }

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
    public function Assets_have_checked_versions(): void
    {
        $postInstallScripts = static::$composerConfig['scripts']['post-install-cmd'] ?? [];
        self::assertNotEmpty($postInstallScripts, 'Missing post-install-cmd scripts');
        $postUpdateScripts = static::$composerConfig['scripts']['post-update-cmd'] ?? [];
        self::assertNotEmpty($postUpdateScripts, 'Missing post-update-cmd scripts');
        foreach ([$postInstallScripts, $postUpdateScripts] as $scripts) {
            self::assertContains('php ./vendor/bin/assets --css --dir=css', $scripts);
        }
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
            "Missing script to warm up frontend cache, there are configs \n" . \implode("\n", $postInstallScripts)
        );
    }
}
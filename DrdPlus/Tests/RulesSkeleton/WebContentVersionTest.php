<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\RulesSkeleton\CookiesService;
use DrdPlus\Tests\RulesSkeleton\Partials\AbstractContentTest;

class WebContentVersionTest extends AbstractContentTest
{

    /**
     * @test
     */
    public function Every_version_like_branch_has_detailed_version_tags(): void
    {
        $webVersions = $this->createWebVersions();
        $tags = $this->runCommand('git tag | grep -P "([[:digit:]]+[.]){2}[[:alnum:]]+([.][[:digit:]]+)?" --only-matching');
        self::assertNotEmpty(
            $tags,
            'Some patch-version tags expected for versions: '
            . \implode(',', $webVersions->getAllStableMinorVersions())
        );
        foreach ($webVersions->getAllStableMinorVersions() as $stableMinorVersion) {
            $stableVersionTags = [];
            foreach ($tags as $tag) {
                if (\strpos($tag, $stableMinorVersion) === 0) {
                    $stableVersionTags[] = $tag;
                }
            }
            self::assertNotEmpty($stableVersionTags, "No tags found for $stableMinorVersion, got only " . \print_r($tags, true));
        }
    }

    /**
     * @test
     * @backupGlobals enabled
     */
    public function Current_version_is_written_into_cookie(): void
    {
        unset($_COOKIE[CookiesService::VERSION]);
        $this->fetchNonCachedContent(null, false /* keep changed globals */);
        self::assertArrayHasKey(CookiesService::VERSION, $_COOKIE, "Missing '" . CookiesService::VERSION . "' in cookie");
        self::assertNotEmpty($_COOKIE[CookiesService::VERSION]);
    }
}
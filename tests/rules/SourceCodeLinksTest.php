<?php
namespace PPH;

use PHPUnit\Framework\TestCase;

class SourceCodeLinksTest extends TestCase
{
    protected function setUp()
    {
        if (!defined('DRD_PLUS_RULES_DIR_TO_TEST')) {
            self::markTestSkipped('Missing constant \'DRD_PLUS_RULES_DIR_TO_TEST\'');
        }
    }

    /**
     * @test
     */
    public function I_can_follow_linked_sources()
    {
        $sourceUrls = $this->getSourceUrls();
        self::assertNotEmpty($sourceUrls);
        foreach ($sourceUrls as $sourceUrl) {
            $localFile = $this->toLocalPath($sourceUrl);
            $toLocalFile = '';
            foreach (explode('/', $localFile) as $filePart) {
                if ($filePart === '') {
                    continue;
                }
                if (!file_exists($toLocalFile . '/' . $filePart)) {
                    self::fail(
                        "Dir or file '$filePart' does not exists in dir '$toLocalFile' (was looking for $localFile linked by $sourceUrl)"
                    );
                }
                $toLocalFile .= '/' . $filePart;
            }
            self::assertFileExists($localFile, preg_replace('~^.+\.\./~', '', $localFile));
        }
    }

    /**
     * @return array|string[]
     */
    private function getSourceUrls()
    {
        $sourceUrls = [];
        foreach (new \DirectoryIterator(DRD_PLUS_RULES_DIR_TO_TEST) as $file) {
            if ($file->isDot()) {
                continue;
            }
            if ($file->isDir()) {
                continue;
            }
            if ($file->getExtension() !== 'html') {
                continue;
            }
            $content = file_get_contents($file->getPathname());
            foreach ($this->parseSourceUrls($content) as $sourceUrl) {
                $sourceUrls[] = $sourceUrl;
            }
        }

        return $sourceUrls;
    }

    /**
     * @param string $html
     * @return array|string[]
     */
    private function parseSourceUrls($html)
    {
        preg_match_all('~data-source-code="(?<links>[^"]+)"~', $html, $matches);

        return $matches['links'];
    }

    /**
     * @param string $link like
     *     https://github.com/jaroslavtyc/drd-plus-professions/blob/master/DrdPlus/Professions/Priest.php
     * @return string
     */
    private function toLocalPath($link)
    {
        $withoutWebRoot = str_replace('https://github.com/jaroslavtyc/', '', $link);
        $withoutGithubSpecifics = preg_replace('~(?<type>blob|tree)/master/~', '', $withoutWebRoot);
        $withLocalSubDirs = preg_replace_callback(
            '~^(?<root>drd)(?:-(?<subRoot>plus))?-(?<projectName>[^/]+)~',
            function (array $matches) {
                return $matches['root'] . '/' . ($matches['subRoot'] ? $matches['subRoot'] . '/' : '') . $matches['projectName'];
            },
            $withoutGithubSpecifics
        );
        $localProjectsRootDir = '/home/jaroslav/Dropbox/Projects';

        $localPath = $localProjectsRootDir . '/' . $withLocalSubDirs;
        if (file_exists($localPath) && preg_match('~(?<type>blob|tree)/master/~', $withoutWebRoot, $matches)) {
            if (is_file($localPath)) {
                self::assertSame('blob', $matches['type'], "File $localPath should be linked as blob, not " . $matches['type']);
            } else if (is_dir($localPath)) {
                self::assertSame('tree', $matches['type'], "Dir $localPath should be linked as tree, not " . $matches['type']);
            }
        }

        return $localPath;
    }
}
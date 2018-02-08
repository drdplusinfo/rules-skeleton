<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\RulesSkeleton;

use Granam\Strict\Object\StrictObject;

abstract class AbstractPublicFiles extends StrictObject implements \IteratorAggregate
{
    protected function addHashesToFileNames(array $fileNames, string $rootDir): array
    {
        $fileNames = \array_combine($fileNames, $fileNames); // index by file names
        $rootDir = \str_replace('\\', '/', $rootDir);
        $rootDir = \rtrim($rootDir, '/');

        return \array_map(function (string $fileName) use ($rootDir) {
            return $fileName . '?version=' . \urlencode($this->getHash($fileName, $rootDir));
        }, $fileNames);
    }

    private function getHash(string $fileName, string $rootDir): string
    {
        $fileName = \str_replace('\\', '/', $fileName);
        $fileName = \ltrim($fileName, '/');
        $absoluteFileName = $rootDir . '/' . $fileName;

        return \md5_file($absoluteFileName) ?: (string)\time();/* fallback */
    }
}
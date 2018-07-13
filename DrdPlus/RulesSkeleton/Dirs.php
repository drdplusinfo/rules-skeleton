<?php
declare(strict_types=1);
/** be strict for parameter types,
 * https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\RulesSkeleton;

class Dirs extends \DrdPlus\FrontendSkeleton\Dirs
{
    public function __construct(
        string $documentRoot = null,
        string $webRoot = null,
        string $vendorRoot = null,
        string $partsRoot = null,
        string $genericPartsRoot = null,
        string $dirForVersions = null
    )
    {
        $documentRoot = $documentRoot ?? (PHP_SAPI !== 'cli' ? \rtrim(\dirname($_SERVER['SCRIPT_FILENAME']), '\/') : \getcwd());
        parent::__construct(
            $documentRoot,
            $webRoot ?? $documentRoot . '/web/passed', // pass.php will change it to /web/pass if access is not allowed yet
            $vendorRoot,
            $partsRoot,
            $genericPartsRoot ?? __DIR__ . '/../../parts/rules-skeleton',
            $dirForVersions
        );
    }

    public function setWebRoot(string $webRoot): Dirs
    {
        $this->webRoot = $webRoot;

        return $this;
    }
}
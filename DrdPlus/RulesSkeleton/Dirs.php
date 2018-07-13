<?php
declare(strict_types=1);
/** be strict for parameter types,
 * https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\RulesSkeleton;

class Dirs extends \DrdPlus\FrontendSkeleton\Dirs
{
    public function __construct(string $documentRoot = null)
    {
        parent::__construct($documentRoot);
    }

    protected function populateSubRoots(string $documentRoot): void
    {
        parent::populateSubRoots($documentRoot);
        $this->setWebRoot($this->getDocumentRoot() . '/web/passed');
        $this->genericPartsRoot = __DIR__ . '/../../parts/rules-skeleton';
    }

    public function setWebRoot(string $webRoot): Dirs
    {
        $this->webRoot = $webRoot;

        return $this;
    }
}
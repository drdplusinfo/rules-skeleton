<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web;

use DrdPlus\RulesSkeleton\Dirs;
use DrdPlus\RulesSkeleton\Partials\CurrentMinorVersionProvider;

class WebFiles extends \Granam\WebContentBuilder\Web\WebFiles
{
    public function __construct(Dirs $dirs, CurrentMinorVersionProvider $currentVersionProvider)
    {
        parent::__construct($dirs->getVersionWebRoot($currentVersionProvider->getCurrentMinorVersion()));
    }
}
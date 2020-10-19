<?php declare(strict_types=1);

namespace DrdPlus\RulesSkeleton\Web\Menu;

use DrdPlus\RulesSkeleton\Configurations\MenuConfiguration;
use DrdPlus\RulesSkeleton\HomepageDetector;
use Granam\Strict\Object\StrictObject;
use Granam\String\StringInterface;

class Menu extends StrictObject implements StringInterface
{
    /** @var MenuConfiguration */
    private $menuConfiguration;
    /** @var HomepageDetector */
    private $homepageDetector;

    public function __construct(MenuConfiguration $menuConfiguration, HomepageDetector $homepageDetector)
    {
        $this->menuConfiguration = $menuConfiguration;
        $this->homepageDetector = $homepageDetector;
    }

    public function __toString()
    {
        return $this->getValue();
    }

    public function getValue(): string
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        $menuConfiguration = $this->menuConfiguration;
        /** @noinspection PhpUnusedLocalVariableInspection */
        $homepageDetector = $this->homepageDetector;
        ob_start();
        include __DIR__ . '/content/menu.php';
        return ob_get_clean();
    }

    protected function getMenuConfiguration(): MenuConfiguration
    {
        return $this->menuConfiguration;
    }
}
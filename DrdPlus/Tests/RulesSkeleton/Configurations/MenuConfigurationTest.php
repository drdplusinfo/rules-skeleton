<?php declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton\Configurations;

use DrdPlus\RulesSkeleton\Configurations\Exceptions\InvalidConfiguration;
use DrdPlus\RulesSkeleton\Configurations\MenuConfiguration;
use DrdPlus\Tests\RulesSkeleton\Partials\AbstractContentTest;

class MenuConfigurationTest extends AbstractContentTest
{

    protected static $validMenuConfiguration = [
        MenuConfiguration::POSITION_FIXED => true,
        MenuConfiguration::SHOW_HOME_BUTTON_ON_HOMEPAGE => true,
        MenuConfiguration::SHOW_HOME_BUTTON_ON_ROUTES => true,
        MenuConfiguration::HOME_BUTTON_TARGET => 'hit!',
    ];

    /**
     * @test
     */
    public function I_can_ommit_menu_items_at_all()
    {
        $values = static::$validMenuConfiguration;
        self::assertArrayNotHasKey(MenuConfiguration::ITEMS, $values);
        $menuConfiguration = new MenuConfiguration($values, ['foo', 'bar']);
        self::assertSame([], $menuConfiguration->getItems());
    }

    /**
     * @test
     */
    public function I_can_use_empty_menu_items()
    {
        $values = static::$validMenuConfiguration;
        $values[MenuConfiguration::ITEMS] = [];
        $menuConfiguration = new MenuConfiguration($values, ['foo', 'bar']);
        self::assertSame([], $menuConfiguration->getItems());
    }

    /**
     * @test
     */
    public function I_am_stopped_on_non_string_menu_item_keys()
    {
        $values = static::$validMenuConfiguration;
        $values[MenuConfiguration::ITEMS] = '';
        $this->expectException(InvalidConfiguration::class);
        $this->expectExceptionMessageMatches("~'foo[.]bar[.]items'.+''~");
        new MenuConfiguration($values, ['foo', 'bar']);
    }

    /**
     * @test
     */
    public function I_can_set_menu_items()
    {
        $values = static::$validMenuConfiguration;
        $values[MenuConfiguration::ITEMS] = [
            'gate' => 'To tomorrow',
        ];
        $menuConfiguration = new MenuConfiguration($values, ['foo', 'bar']);
        self::assertSame($values[MenuConfiguration::ITEMS], $menuConfiguration->getItems());
    }
}

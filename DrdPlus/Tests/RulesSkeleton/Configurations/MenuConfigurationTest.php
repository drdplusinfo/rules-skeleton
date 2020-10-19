<?php declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton\Configurations;

use DrdPlus\RulesSkeleton\Configurations\Configuration;
use DrdPlus\RulesSkeleton\Configurations\Exceptions\InvalidConfiguration;
use DrdPlus\RulesSkeleton\Configurations\HomeButtonConfiguration;
use DrdPlus\RulesSkeleton\Configurations\MenuConfiguration;
use DrdPlus\Tests\RulesSkeleton\Partials\AbstractContentTest;

class MenuConfigurationTest extends AbstractContentTest
{

    protected static $validMenuConfiguration = [
        MenuConfiguration::POSITION_FIXED => true,
        MenuConfiguration::HOME_BUTTON => [
            HomeButtonConfiguration::SHOW_ON_HOMEPAGE => true,
            HomeButtonConfiguration::SHOW_ON_ROUTES => true,
            HomeButtonConfiguration::TARGET => 'hit!',
        ],
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
    public function I_am_stopped_on_missing_home_button_configuration_part()
    {
        $values = static::$validMenuConfiguration;
        $this->expectException(InvalidConfiguration::class);
        $this->expectExceptionMessageMatches("~'foo[.]bar[.]home_button'.+~");
        unset($values[MenuConfiguration::HOME_BUTTON]);
        new MenuConfiguration($values, ['foo', 'bar']);
    }

    /**
     * @test
     */
    public function Missing_home_button_configuration_section_is_created()
    {
        $values = static::$validMenuConfiguration;
        $values[Configuration::SHOW_HOME_BUTTON_ON_HOMEPAGE] = true;
        unset($values[MenuConfiguration::HOME_BUTTON]);

        $this->expectException(InvalidConfiguration::class);
        $this->expectExceptionMessageMatches("~Expected explicitly defined configuration 'foo.bar.home_button.show_on_routes'.+~");

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

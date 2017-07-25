<?php
namespace Tests\DrdPlus\RulesSkeleton;

use Gt\Dom\Element;
use Gt\Dom\HTMLDocument;

class LicenceConfirmationTest extends AbstractContentTest
{
    /**
     * @test
     */
    public function I_have_to_confirm_owning_of_a_licence_first()
    {
        $html = new HTMLDocument($this->getOwnershipConfirmationContent());
        $forms = $html->getElementsByTagName('form');
        self::assertCount(2, $forms);
        foreach ($forms as $index => $form) {
            switch ($index) {
                case 0:
                    $this->I_can_buy_licence($form);
                    break;
                case 1:
                    $this->I_can_continue_after_confirmation_of_owning($form);
            }
        }
    }

    private function I_can_buy_licence(Element $buyForm)
    {
        self::assertStringStartsWith('http://obchod.altar.cz', $buyForm->getAttribute('action'));
        self::assertRegExp(
            '~^' . preg_quote('http://obchod.altar.cz/', '~') . '\w+~',
            $buyForm->getAttribute('action'),
            'Missing direct link to current article in e-shop, (put it into eshop_url.txt file)'
        );
        self::assertTrue(in_array($buyForm->getAttribute('method'), ['' /* get as default */, 'get'], true));
        self::assertEmpty($buyForm->getAttribute('onsubmit'), 'No confirmation should be required to access e-shop');
    }

    private function I_can_continue_after_confirmation_of_owning(Element $confirmForm)
    {
        self::assertSame('post', $confirmForm->getAttribute('method'));
        self::assertStringStartsWith('return window.confirm', $confirmForm->getAttribute('onsubmit'));
    }
}
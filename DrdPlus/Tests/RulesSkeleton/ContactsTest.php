<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\Tests\FrontendSkeleton\AbstractContentTest;
use Gt\Dom\Element;

/**
 * @method TestsConfiguration getTestsConfiguration
 */
class ContactsTest extends AbstractContentTest
{
    use AbstractContentTestTrait;

    /**
     * @test
     */
    public function Proper_email_is_used_in_debug_contacts(): void
    {
        $debugContacts = include __DIR__ . '/../../../parts/debug_contacts.php';
        self::assertRegExp(
            '~[^[:alnum:]]info@drdplus[.]info[^[:alnum:]]~',
            $debugContacts,
            'Email to info@drdplus.info has not been found in debug contacts template'
        );
    }

    /**
     * @test
     */
    public function Proper_facebook_link_is_used_in_debug_contacts(): void
    {
        $debugContacts = include __DIR__ . '/../../../parts/debug_contacts.php';
        self::assertRegExp(
            '~[^[:alnum:]]https://www[.]facebook[.]com/drdplus[.]info[^[:alnum:]]~',
            $debugContacts,
            'Link to facebook.com/drdplus.info has not been found in debug contacts template'
        );
    }

    /**
     * @test
     */
    public function Proper_rpg_forum_link_is_used_in_debug_contacts(): void
    {
        $debugContacts = include __DIR__ . '/../../../parts/debug_contacts.php';
        self::assertRegExp(
            '~[^[:alnum:]]https://rpgforum[.]cz/forum/viewtopic[.]php[?]f=238&t=14870[^[:alnum:]]~',
            $debugContacts,
            'Link to RPG forum has not been found in debug contacts template'
        );
    }

    /**
     * @test
     */
    public function I_can_use_link_to_drdplus_info_email(): void
    {
        $debugContacts = $this->getDebugContactsElement();
        if (!$this->getTestsConfiguration()->hasDebugContacts()) {
            self::assertNull($debugContacts, 'Debug contacts have not been expected');

            return;
        }
        $this->guardDebugContactsAreNotEmpty($debugContacts);
        $anchors = $debugContacts->getElementsByTagName('a');
        self::assertNotEmpty($anchors, 'No anchors found in debug contacts');
        $mailTo = null;
        foreach ($anchors as $anchor) {
            $href = (string)$anchor->getAttribute('href');
            if (!$href || \strpos($href, 'mailto:') !== 0) {
                continue;
            }
            $mailTo = $href;
        }
        self::assertNotEmpty($mailTo, 'Missing mailto: in debug contacts ' . $debugContacts->innerHTML);
        self::assertSame('mailto:info@drdplus.info', $mailTo);
    }

    private function getDebugContactsElement(): ?Element
    {
        return $this->getHtmlDocument()->getElementById('debug_contacts');
    }

    private function guardDebugContactsAreNotEmpty(Element $debugContacts): void
    {
        self::assertNotEmpty($debugContacts, 'Debug contacts has not been found by ID debug_contacts (debugContacts)');
        self::assertNotEmpty($debugContacts->textContent, 'Debug contacts are empty');
    }

}
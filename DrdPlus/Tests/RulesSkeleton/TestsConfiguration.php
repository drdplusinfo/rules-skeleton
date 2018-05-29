<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\Tests\RulesSkeleton;

class TestsConfiguration extends \DrdPlus\Tests\FrontendSkeleton\TestsConfiguration
{
    public const HAS_PROTECTED_ACCESS = 'has_protected_access';
    public const CAN_BE_BOUGHT_ON_ESHOP = 'can_be_bought_on_eshop';
    public const HAS_CHARACTER_SHEET = 'has_character_sheet';
    public const HAS_LINKS_TO_JOURNALS = 'has_links_to_journals';
    public const HAS_LINK_TO_SINGLE_JOURNAL = 'has_link_to_single_journal';

    // every setting SHOULD be strict (expecting instead of ignoring)

    /** @var bool */
    private $hasProtectedAccess = true;
    /** @var bool */
    private $canBeBoughtOnEshop = true;
    /** @var bool */
    private $hasCharacterSheet = true;
    /** @var bool */
    private $hasLinksToJournals = true;
    /** @var bool */
    private $hasLinkToSingleJournal = true;
    /** @var bool */
    private $hasDebugContacts = true;
    /** @var bool */
    private $hasIntroduction = true;
    /** @var array|string[] */
    private $blockNamesToExpectedContent = ['just-some-block' => <<<HTML
<div class="block-just-some-block">
    First part of some block
</div>

<div class="block-just-some-block">
    Second part of some block
</div>

<div class="block-just-some-block">
    Last part of some block
</div>
HTML
    ];

    /**
     * @return bool
     */
    public function hasProtectedAccess(): bool
    {
        return $this->hasProtectedAccess;
    }

    /**
     * @param bool $hasProtectedAccess
     * @return TestsConfiguration
     */
    public function setHasProtectedAccess(bool $hasProtectedAccess): TestsConfiguration
    {
        $this->hasProtectedAccess = $hasProtectedAccess;

        return $this;
    }

    /**
     * @return bool
     */
    public function canBeBoughtOnEshop(): bool
    {
        return $this->canBeBoughtOnEshop;
    }

    /**
     * @param bool $canBeBoughtOnEshop
     * @return TestsConfiguration
     */
    public function setCanBeBoughtOnEshop(bool $canBeBoughtOnEshop): TestsConfiguration
    {
        $this->canBeBoughtOnEshop = $canBeBoughtOnEshop;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasCharacterSheet(): bool
    {
        return $this->hasCharacterSheet;
    }

    /**
     * @param bool $hasCharacterSheet
     * @return TestsConfiguration
     */
    public function setHasCharacterSheet(bool $hasCharacterSheet): TestsConfiguration
    {
        $this->hasCharacterSheet = $hasCharacterSheet;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasLinksToJournals(): bool
    {
        return $this->hasLinksToJournals;
    }

    /**
     * @param bool $hasLinksToJournals
     * @return TestsConfiguration
     */
    public function setHasLinksToJournals(bool $hasLinksToJournals): TestsConfiguration
    {
        $this->hasLinksToJournals = $hasLinksToJournals;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasLinkToSingleJournal(): bool
    {
        return $this->hasLinkToSingleJournal;
    }

    /**
     * @param bool $hasLinkToSingleJournal
     * @return TestsConfiguration
     */
    public function setHasLinkToSingleJournal(bool $hasLinkToSingleJournal): TestsConfiguration
    {
        $this->hasLinkToSingleJournal = $hasLinkToSingleJournal;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasDebugContacts(): bool
    {
        return $this->hasDebugContacts;
    }

    /**
     * @param bool $hasDebugContacts
     * @return TestsConfiguration
     */
    public function setHasDebugContacts(bool $hasDebugContacts): TestsConfiguration
    {
        $this->hasDebugContacts = $hasDebugContacts;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasIntroduction(): bool
    {
        return $this->hasIntroduction;
    }

    /**
     * @param bool $hasIntroduction
     * @return TestsConfiguration
     */
    public function setHasIntroduction(bool $hasIntroduction): TestsConfiguration
    {
        $this->hasIntroduction = $hasIntroduction;

        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getBlockNamesToExpectedContent(): array
    {
        return $this->blockNamesToExpectedContent;
    }

    /**
     * @param array $blockNamesToExpectedContent
     * @return TestsConfiguration
     */
    public function setBlockNamesToExpectedContent(array $blockNamesToExpectedContent): TestsConfiguration
    {
        $this->blockNamesToExpectedContent = $blockNamesToExpectedContent;

        return $this;
    }
}
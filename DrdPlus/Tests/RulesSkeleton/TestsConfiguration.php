<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\Tests\RulesSkeleton;

use DrdPlus\Tests\RulesSkeleton\Partials\TestsConfigurationReader;

class TestsConfiguration extends \DrdPlus\Tests\FrontendSkeleton\TestsConfiguration implements TestsConfigurationReader
{
    public const LICENCE_BY_ACCESS = '*by access*';
    public const LICENCE_MIT = 'MIT';
    public const LICENCE_PROPRIETARY = 'proprietary';

    // every setting SHOULD be strict (expecting instead of ignoring)

    /** @var string */
    private $publicUrl;
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
    /** @var bool */
    private $hasAuthors = true;
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
    /** @var string */
    private $expectedLicence = '*by access*';

    /**
     * @param string $publicUrl
     * @throws \DrdPlus\Tests\RulesSkeleton\Exceptions\InvalidPublicUrl
     * @throws \DrdPlus\Tests\RulesSkeleton\Exceptions\PublicUrlShouldUseHttps
     */
    public function __construct(string $publicUrl)
    {
        if (!\filter_var($publicUrl, FILTER_VALIDATE_URL)) {
            throw new Exceptions\InvalidPublicUrl("Given public URL is not valid: '$publicUrl'");
        }
        if (\strpos($publicUrl, 'https://') !== 0) {
            throw new Exceptions\PublicUrlShouldUseHttps("Given public URL should use HTTPS: '$publicUrl'");
        }
        $this->publicUrl = $publicUrl;
    }

    /**
     * @return string
     */
    public function getPublicUrl(): string
    {
        return $this->publicUrl;
    }

    /**
     * @return bool
     */
    public function hasProtectedAccess(): bool
    {
        return $this->hasProtectedAccess;
    }

    /**
     * @return TestsConfiguration
     */
    public function disableHasProtectedAccess(): TestsConfiguration
    {
        $this->hasProtectedAccess = false;

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
     * @return TestsConfiguration
     */
    public function disableCanBeBoughtOnEshop(): TestsConfiguration
    {
        $this->canBeBoughtOnEshop = false;

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
     * @return TestsConfiguration
     */
    public function disableHasCharacterSheet(): TestsConfiguration
    {
        $this->hasCharacterSheet = false;

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
     * @return TestsConfiguration
     */
    public function disableHasLinksToJournals(): TestsConfiguration
    {
        $this->hasLinksToJournals = false;

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
     * @return TestsConfiguration
     */
    public function disableHasLinkToSingleJournal(): TestsConfiguration
    {
        $this->hasLinkToSingleJournal = false;

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
     * @return TestsConfiguration
     */
    public function disableHasDebugContacts(): TestsConfiguration
    {
        $this->hasDebugContacts = false;

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
     * @return TestsConfiguration
     */
    public function disableHasIntroduction(): TestsConfiguration
    {
        $this->hasIntroduction = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasAuthors(): bool
    {
        return $this->hasAuthors;
    }

    /**
     * @return TestsConfiguration
     */
    public function disableHasAuthors(): TestsConfiguration
    {
        $this->hasAuthors = false;

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

    /**
     * @return string
     */
    public function getExpectedLicence(): string
    {
        if ($this->expectedLicence !== self::LICENCE_BY_ACCESS) {
            return $this->expectedLicence;
        }

        return $this->hasProtectedAccess()
            ? self::LICENCE_PROPRIETARY
            : self::LICENCE_MIT;
    }

    /**
     * @param string $expectedLicence
     * @return TestsConfiguration
     */
    public function setExpectedLicence(string $expectedLicence): TestsConfiguration
    {
        $this->expectedLicence = $expectedLicence;

        return $this;
    }
}
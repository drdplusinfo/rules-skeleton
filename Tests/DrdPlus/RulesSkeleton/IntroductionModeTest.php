<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace Tests\DrdPlus\RulesSkeleton;

use Gt\Dom\Element;
use Gt\Dom\HTMLDocument;

class IntroductionModeTest extends AbstractContentTest
{

    /**
     * @test
     */
    public function I_can_get_introduction_only()
    {
        $contents = ['standard' => $this->getRulesContent('introduction'), 'dev' => $this->getRulesContentForDev('introduction')];
        foreach ($contents as $mode => $content) {
            $html = new HTMLDocument($content);
            self::assertGreaterThan(0, $html->children->count());
            $bodies = $html->getElementsByTagName('body');
            self::assertGreaterThan(0, $bodies->length);
            /** @var Element $body */
            foreach ($bodies as $body) {
                self::assertGreaterThan(0, $body->children->length, 'No introduction found');
                foreach ($body->children as $child) {
                    self::assertTrue(
                        $child->classList->contains('introduction')
                        || $child->classList->contains('background-image')
                        || $child->classList->contains('quote')
                        || $child->nodeName === 'img',
                        "Only an element with classes 'introduction', 'background-image' and 'quote' or the <img> element is expected in {$mode} mode, got : " . $child->outerHTML
                    );
                }
            }
            self::assertSame(
                0,
                $html->body->getElementsByClassName('generic')->count(),
                "Class 'generic' would be already hidden id mode='$mode' and show='introduction'."
            );
            if ($mode === 'standard') {
                self::assertGreaterThan(
                    0,
                    $html->body->getElementsByTagName('img')->count(),
                    "Expected some image in mode='$mode' and show='introduction'"
                );
            } else {
                self::assertSame(
                    0,
                    $html->body->getElementsByTagName('img')->count(),
                    "No image expected in mode='$mode' and show='introduction'"
                );
            }
            self::assertGreaterThan(
                0,
                $html->getElementsByClassName('background-image')->count(),
                "Background image should not be removed in mode='$mode' and show='introduction'"
            );
        }
    }

    /**
     * @test
     */
    public function Every_introduction_is_direct_child_of_body()
    {
        $content = $this->getRulesContent('introduction');
        $html = new HTMLDocument($content);
        self::assertGreaterThan(0, $html->children->count());
        $bodies = $html->getElementsByTagName('body');
        self::assertGreaterThan(0, $bodies->length);
        /** @var Element $body */
        foreach ($bodies as $body) {
            self::assertGreaterThan(0, $body->children->length, 'No introduction found');
            foreach ($body->children as $child) {
                $this->guardNoChildIntroduction($child);
            }
        }
    }

    public function guardNoChildIntroduction(Element $child)
    {
        foreach ($child->children as $grandChild) {
            self::assertFalse(
                $grandChild->classList->contains('introduction'),
                'A grand-child should NOT have "introduction" class: ' . $grandChild->innerHTML
            );
            $this->guardNoChildIntroduction($grandChild);
        }
    }
}
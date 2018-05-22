<?php
declare(strict_types=1);
/** be strict for parameter types, https://www.quora.com/Are-strict_types-in-PHP-7-not-a-bad-idea */

namespace DrdPlus\Tests\RulesSkeleton;

class HtmlHelperTest extends \DrdPlus\Tests\FrontendSkeleton\HtmlHelperTest
{
    protected function getSomeExpectedTableIds(): array
    {
        return ['IAmSoAlone', 'JustSomeTable'];
    }

}
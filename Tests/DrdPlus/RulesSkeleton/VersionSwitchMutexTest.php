<?php
namespace Tests\DrdPlus\RulesSkeleton;

use DrdPlus\RulesSkeleton\VersionSwitchMutex;
use PHPUnit\Framework\TestCase;

class VersionSwitchMutexTest extends TestCase
{
    /**
     * @test
     */
    public function I_can_get_and_release_lock(): void
    {
        $mutex = new VersionSwitchMutex();
        self::assertTrue($mutex->lock(), 'Can not get lock via mutex');
        self::assertTrue($mutex->unlock(), 'Can not unlock mutex');
        self::assertFalse($mutex->unlock(), 'Second unlock in a row should NOT be successful');
    }

    /**
     * @test
     */
    public function I_can_not_lock_twice(): void
    {
        $mutex = new VersionSwitchMutex();
        $mutex->lock();
        $message = \exec(<<<'PHP'
php -r 'include "vendor/autoload.php";
try {
    (new DrdPlus\RulesSkeleton\VersionMutex)->lock(0 /* no wait */);
} catch(\DrdPlus\RulesSkeleton\Exceptions\CanNotLockVersionMutex $exception) {
    echo $exception->getMessage();
    exit(0);
}
exit(1);'
PHP
        , $output,
        $returnCode
);

        self::assertSame(0, $returnCode, $message);
    }
}

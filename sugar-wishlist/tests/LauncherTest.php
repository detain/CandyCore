<?php

declare(strict_types=1);

namespace SugarCraft\Wishlist\Tests;

use SugarCraft\Wishlist\Endpoint;
use SugarCraft\Wishlist\Launcher;
use PHPUnit\Framework\TestCase;

final class LauncherTest extends TestCase
{
    public function testDispatchPassesCorrectArgvToExecutor(): void
    {
        $captured = null;
        $launcher = new Launcher(function (string $bin, array $args) use (&$captured): void {
            $captured = ['bin' => $bin, 'args' => $args];
        });
        $e = new Endpoint(name: 'prod', host: 'prod.test', port: 2222, user: 'deploy');
        $launcher->dispatch($e, '/usr/bin/ssh');

        $this->assertSame('/usr/bin/ssh', $captured['bin']);
        $this->assertSame(
            ['-p', '2222', 'deploy@prod.test'],
            $captured['args'],
        );
    }

    public function testDispatchUsesProvidedSshBinary(): void
    {
        $captured = null;
        $launcher = new Launcher(function (string $bin, array $args) use (&$captured): void {
            $captured = $bin;
        });
        $e = new Endpoint(name: 'a', host: 'a.test');
        $launcher->dispatch($e, '/usr/local/bin/ssh');
        $this->assertSame('/usr/local/bin/ssh', $captured);
    }
}

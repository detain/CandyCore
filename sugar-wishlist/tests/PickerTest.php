<?php

declare(strict_types=1);

namespace SugarCraft\Wishlist\Tests;

use SugarCraft\Wishlist\Endpoint;
use SugarCraft\Wishlist\Picker;
use PHPUnit\Framework\TestCase;

/**
 * Picker exercised against in-memory streams. We override
 * setRawMode() in tests to skip the `stty` call (which is a no-op
 * for non-tty streams anyway, but defensively suppressed so the
 * test stays clean even on hosts without `stty`).
 */
final class PickerTest extends TestCase
{
    /**
     * @return array{0:resource,1:resource,2:Picker}
     */
    private function makePicker(string $keys): array
    {
        $in = fopen('php://memory', 'w+');
        $out = fopen('php://memory', 'w+');
        $this->assertNotFalse($in);
        $this->assertNotFalse($out);
        fwrite($in, $keys);
        rewind($in);

        $p = new class($in, $out) extends Picker {
            protected function setRawMode(bool $on): void { /* noop in tests */ }
        };
        return [$in, $out, $p];
    }

    private function endpoints(): array
    {
        return [
            new Endpoint(name: 'production', host: 'prod.example.com'),
            new Endpoint(name: 'staging',    host: 'stage.example.com'),
            new Endpoint(name: 'dev',        host: 'dev.example.com'),
        ];
    }

    public function testEnterPicksFirstByDefault(): void
    {
        [, , $p] = $this->makePicker("\r");
        $picked = $p->pick($this->endpoints());
        $this->assertNotNull($picked);
        $this->assertSame('production', $picked->name);
    }

    public function testJMovesDown(): void
    {
        [, , $p] = $this->makePicker("jj\r");
        $picked = $p->pick($this->endpoints());
        $this->assertSame('dev', $picked->name);
    }

    public function testKMovesUp(): void
    {
        [, , $p] = $this->makePicker("jjjk\r");
        $picked = $p->pick($this->endpoints());
        $this->assertSame('staging', $picked->name);
    }

    public function testEscReturnsNull(): void
    {
        [, , $p] = $this->makePicker("\x1b");
        $this->assertNull($p->pick($this->endpoints()));
    }

    public function testCtrlCReturnsNull(): void
    {
        [, , $p] = $this->makePicker("\x03");
        $this->assertNull($p->pick($this->endpoints()));
    }

    public function testFilterNarrowsList(): void
    {
        // Type "stag", then press Enter.
        [, , $p] = $this->makePicker("stag\r");
        $picked = $p->pick($this->endpoints());
        $this->assertNotNull($picked);
        $this->assertSame('staging', $picked->name);
    }

    public function testFilterBackspaceUnFilters(): void
    {
        // Type "dev", select dev, then backspace twice and pick first match.
        [, , $p] = $this->makePicker("dev\x7f\x7f\x7f\r");
        $picked = $p->pick($this->endpoints());
        $this->assertSame('production', $picked->name);
    }

    public function testEmptyListReturnsNull(): void
    {
        [, , $p] = $this->makePicker("\r");
        $this->assertNull($p->pick([]));
    }

    public function testArrowDownIsEquivalentToJ(): void
    {
        // Two ↓ then Enter. The CSI sequence is ESC [ B.
        [, , $p] = $this->makePicker("\x1b[B\x1b[B\r");
        $picked = $p->pick($this->endpoints());
        $this->assertSame('dev', $picked->name);
    }
}

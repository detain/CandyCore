<?php

declare(strict_types=1);

namespace SugarCraft\Crush\Tests\Support;

use PHPUnit\Framework\TestCase;
use SugarCraft\Crush\Support\HookContextFiles;
use SugarCraft\Crush\Support\ToolIpcFiles;

/**
 * The RETAINED hook-overflow store (P7.S1, ruling R-2), tested where it lives.
 *
 * Three properties are load-bearing and each is pinned here: the write is private
 * (0600) and atomic (temp + rename, never a half-written file the consumer reads
 * as whole), the file SURVIVES the hourly IPC sweep (that is the whole difference
 * between this store and {@see ToolIpcFiles} — a retained overflow must still be
 * readable by the model on a later turn), and {@see HookContextFiles::bound()}
 * never returns more than the byte cap it is given.
 */
final class HookContextFilesTest extends TestCase
{
    /**
     * @var list<string> files this test created, removed in tearDown
     */
    private array $created = [];

    protected function tearDown(): void
    {
        foreach ($this->created as $file) {
            @unlink($file);
        }

        $this->created = [];
    }

    public function testDirIsDedicatedAndOutsideTheSweepGlob(): void
    {
        $dir = HookContextFiles::dir();

        $this->assertDirectoryExists($dir);
        // The dedicated sub-directory is the first half of the sweep-safety proof:
        // glob('*/sc_runtime_tool_*') never descends into a nested directory.
        $this->assertSame(sys_get_temp_dir() . '/' . HookContextFiles::DIR_NAME, $dir);
    }

    public function testWriteIsPrivateZeroSixZeroZeroAndAtomic(): void
    {
        $path = HookContextFiles::write(str_repeat('Z', 4096));
        $this->created[] = $path;

        clearstatcache();
        $this->assertFileExists($path);
        // 0600 — no group/other bits for the whole life of the file (umask at
        // create, not chmod-after, so there is no world-readable window).
        $this->assertSame(0, fileperms($path) & 0o077, 'a retained overflow is not owner-private');

        // Atomicity: the final name exists and no `.partial` sibling lingers.
        $this->assertFileDoesNotExist($path . '.partial');
        $this->assertSame(4096, strlen((string) file_get_contents($path)));
    }

    public function testRetainedFileSurvivesTheHourlyIpcSweep(): void
    {
        // The pre-code STEP ZERO proof made concrete: with the sweep's age cutoff
        // forced to zero, a file in the bare temp dir matching one of the three
        // IPC prefixes would be removed immediately, yet our overflow — same age,
        // different directory segment — must be left standing. This is exactly the
        // silent data-loss window the ruling forbade, pinned against a future
        // widening of the sweep predicate.
        $path = HookContextFiles::write('must not be reaped before the model reads it');
        $this->created[] = $path;

        $removed = ToolIpcFiles::sweep(sys_get_temp_dir(), 0);

        $this->assertIsInt($removed);
        $this->assertFileExists($path, 'the IPC sweep reached a retained hook-overflow file');
        $this->assertSame(
            'must not be reaped before the model reads it',
            file_get_contents($path),
        );
    }

    public function testBoundReturnsShortTextUnchangedAndWritesNothing(): void
    {
        $short = 'a model-visible note well under the cap';

        $this->assertSame($short, HookContextFiles::bound($short, 10_000));
    }

    public function testBoundCapsToTheByteLimitAndNamesTheRetainedFile(): void
    {
        $text = str_repeat('Q', 20_000);

        $bounded = HookContextFiles::bound($text, 10_000);

        $this->assertLessThanOrEqual(10_000, strlen($bounded), 'bound() blew past its cap');
        $this->assertNotSame($text, $bounded);
        $this->assertStringContainsString('truncated: ', $bounded);
        $this->assertStringContainsString('of 20000 bytes shown', $bounded);

        $matched = preg_match('/retained at (\S+)\]/', $bounded, $m);
        $this->assertSame(1, $matched, 'the bounded marker did not name a retained path');
        $this->created[] = $m[1];

        // The retained file carries the ENTIRE original text, not the preview.
        $this->assertSame(20_000, strlen((string) file_get_contents($m[1])));
    }

    public function testBoundReservesAtLeastOnePreviewByteUnderATinyCap(): void
    {
        // A cap smaller than the marker reserve must still yield a non-empty,
        // in-cap string rather than a zero/negative-length mb_strcut — the
        // max(1, …) guard copied from the TruncatesOutput idiom, pinned so it
        // cannot be "optimised" away into the $maxBytes <= 0 no-cap trap.
        $bounded = HookContextFiles::bound(str_repeat('W', 5_000), 128);

        $this->assertLessThanOrEqual(128, strlen($bounded));
        $this->assertNotSame('', $bounded);
    }
}

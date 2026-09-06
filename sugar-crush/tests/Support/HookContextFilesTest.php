<?php

declare(strict_types=1);

namespace SugarCraft\Crush\Tests\Support;

use PHPUnit\Framework\TestCase;
use SugarCraft\Crush\Support\HookContextFiles;
use SugarCraft\Crush\Support\ToolIpcFiles;

/**
 * The RETAINED hook-overflow store (P7.S1, ruling R-2), tested where it lives.
 *
 * Three properties are load-bearing. Two are pinned behaviourally here: the write
 * is private (0600) and leaves no intermediate behind, and the file SURVIVES the
 * hourly IPC sweep (that is the whole difference between this store and {@see
 * ToolIpcFiles} — a retained overflow must still be readable by the model on a
 * later turn). The third, {@see HookContextFiles::bound()} never returning more
 * than the byte cap it is given, is pinned below.
 *
 * ATOMICITY IS NOT BEHAVIOURALLY CLAIMED BY THIS SUITE. `write()`'s temp-then-
 * rename discipline (`src/Support/HookContextFiles.php`) — never leaving a
 * half-written file the consumer reads as whole — is pinned at CODE REVIEW, not
 * here. Reddening on a loss of atomicity would require a same-box crash mid-write
 * probe, which is banned as flaky in this deterministic suite; a post-success
 * absence check cannot distinguish atomic from direct write, so the tests below
 * deliberately make no atomicity promise they could not keep.
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

    /**
     * Pins ONLY what a post-success observation can honestly see: `write()` creates
     * the file OWNER-PRIVATE (exactly 0600, no group/other bits) and leaves NO
     * intermediate behind — nothing sharing the final path as a prefix survives a
     * successful write.
     *
     * NOT claimed here (and not claimable without a crash probe): ATOMICITY. The
     * temp-then-rename discipline in `src/Support/HookContextFiles.php` that makes
     * a consumer unable to observe a half-written overflow is a property pinned at
     * CODE REVIEW. Pinning it behaviourally would require crashing the writer
     * mid-write on the same box, which is banned as flaky in this deterministic
     * suite; a `.partial`-absence check passes identically whether production wrote
     * atomically or directly, so this test makes no atomicity promise it could not
     * keep.
     */
    public function testWriteIsPrivateAndLeavesNoPartialBehind(): void
    {
        $path = HookContextFiles::write(str_repeat('Z', 4096));
        $this->created[] = $path;

        clearstatcache();
        $this->assertFileExists($path);
        // 0600 exactly — no group/other bits for the whole life of the file (umask
        // at create, not chmod-after, so there is no world-readable window). An
        // exact mode check, not a `& 0o077` zero test that would also pass on e.g.
        // 0o640.
        $this->assertSame(0o600, fileperms($path) & 0o777, 'a retained overflow is not exactly owner-private');

        // The final path holds the whole text, and NOTHING sharing it as a name
        // prefix with a dotted suffix (e.g. a `.partial` intermediate) survives a
        // successful write. Note the `.*` (not `*`) pattern: PHP's glob `*` matches
        // the empty string, so `glob($path . '*')` returns the retained file itself
        // and could never be `[]`; `.*` is the honest form of the reviewer's
        // "nothing like ctx-….txt.anything remains" intent. This pins the "no
        // residue behind" half of the write discipline; see the docblock for why
        // the atomicity half is code-review-only.
        $this->assertSame(4096, strlen((string) file_get_contents($path)));
        $this->assertFileDoesNotExist($path . '.partial', 'the write left its .partial intermediate in place');
        $this->assertSame([], glob($path . '.*'), 'the write left a dotted-suffix intermediate behind');
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

        // POSITIVE CONTROL for the sweep call below: plant a file the sweep MUST
        // reap, in the bare temp dir under one of the three real IPC prefixes. With
        // no decoy a fully inert sweep (a broken glob, a renamed prefix list) leaves
        // this test GREEN while the retained file stands for the wrong reason — "the
        // reaper is dead", not "the reaper cannot reach us". The decoy proves the
        // reaper actually fired on this very invocation.
        $decoy = sys_get_temp_dir() . '/' . ToolIpcFiles::RUNTIME_PREFIX . bin2hex(random_bytes(4)) . '.json';
        $previous = umask(0o077);

        try {
            file_put_contents($decoy, 'decoy ipc payload owed to a dead owner');
        } finally {
            umask($previous);
        }

        try {
            // This exercises the REAL sweep against the SHARED temp dir by design:
            // only a real `HookContextFiles::dir()` proves the two stores are
            // disjoint, which is the whole point. A cutoff of 0 could therefore reap
            // another LIVE sugar-crush process's in-flight IPC payloads — so suites
            // are run serially in this repo. `ToolIpcFilesTest` keeps the sandboxed
            // positive-control pin for the reap behaviour in isolation.
            $removed = ToolIpcFiles::sweep(sys_get_temp_dir(), 0);
        } finally {
            @unlink($decoy);
        }

        $this->assertIsInt($removed);
        $this->assertGreaterThanOrEqual(1, $removed, 'the sweep reaped nothing — the decoy proves it never fired');
        $this->assertFileDoesNotExist($decoy, 'the sweep left a same-prefix IPC decoy in the bare temp dir');
        $this->assertFileExists($path, 'the IPC sweep reached a retained hook-overflow file');
        $this->assertSame(
            'must not be reaped before the model reads it',
            file_get_contents($path),
        );
    }

    public function testBoundReturnsShortTextUnchangedAndWritesNothing(): void
    {
        $short = 'a model-visible note well under the cap';
        $before = glob(HookContextFiles::dir() . '/*') ?: [];
        sort($before);

        $result = HookContextFiles::bound($short, 10_000);

        $this->assertSame($short, $result);

        // The "WritesNothing" half of the method name is a real assertion, not a
        // caption: the directory listing must be byte-identical before and after, so
        // an at-or-under-cap path that quietly spilled to disk would go red here.
        $after = glob(HookContextFiles::dir() . '/*') ?: [];
        sort($after);
        $this->assertSame($before, $after, 'bound() wrote a retained file for text at or under the cap');
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

<?php

declare(strict_types=1);

namespace SugarCraft\Crush\Support;

/**
 * The RETAINED overflow file a hook's model-visible `additionalContext` spills
 * its full text into when that text is longer than the context cap.
 *
 * WHY A NEW CLASS RATHER THAN NEW METHODS ON {@see ToolIpcFiles}. `ToolIpcFiles`
 * is the fork/IPC hand-off store: every one of its files is written by a child,
 * read once by the parent, and `discard()`-ed on collection, and its hourly
 * `sweep()` is the reaper for the ones whose owner died. A hook overflow has the
 * opposite lifecycle (supervisor ruling R-2): the model is expected to READ the
 * retained file later, possibly after several provider round-trips, so it must
 * NOT be discarded-after-read and must NOT be caught by that sweep. Sharing a
 * class with `ToolIpcFiles` would mean either widening the sweep's exclusion list
 * (narrowing an existing safety construct — §1.10) or living inside a store whose
 * whole contract is "gone on read". A separate class with its own directory keeps
 * the two lifetimes provably disjoint. It borrows only the WRITE discipline:
 * `umask(0o077)` around the create so the bytes are 0600 for their whole life
 * (`chmod`-after leaves a world-readable window), and write-then-`rename` so a
 * process that dies mid-write cannot leave a truncated file the consumer reads as
 * whole.
 *
 * LIFETIME. A retained file lives until the tool-result that names it leaves the
 * session — it is never auto-deleted by this class. That is deliberate: deleting
 * it on read (R-2) would strip the model's ability to re-read it on a later turn
 * that quotes the overflow path. The trade is that these files accumulate for the
 * session's life; they are bounded by the number of overflowing hooks that fire
 * (rare — a hook must both permit a call AND emit more than the cap), not by
 * every tool call, and each is at most the size of one hook's stdout.
 *
 * SWEEP-SAFETY PROOF (pre-code mandatory check, step brief §Hard constraints 6).
 * `ToolIpcFiles::sweep()` (and therefore `sweepOnce()`, wired at
 * {@see \SugarCraft\Crush\Cli\Bootstrap::backend()} / `::backendFor()`) selects
 * files ONLY with these three literal-prefix globs inside `sys_get_temp_dir()`:
 *   - `sys_get_temp_dir() . '/sc_runtime_tool_*'`
 *   - `sys_get_temp_dir() . '/sc_chat_tool_*'`
 *   - `sys_get_temp_dir() . '/crush-hook-payload-*'`
 * PHP `glob()` treats `*` as NOT crossing a `/` boundary, so a path that contains
 * the extra directory segment `'/sc-hook-ctx/'` can never be named by any of
 * those three patterns — the sweep cannot reach a file it does not glob,
 * irrespective of mtime/age. `ToolIpcFiles::discard()` unlinks only the exact
 * `$file` and `$file . '.partial'` it is handed, and every caller passes its own
 * `reserve()`-recorded IPC path; this class's paths never enter `reserve()`'s
 * ledger (`$reserved`), so neither `discard()` nor `strandedReservations()`'s
 * partial-suffix sweep can name them either. Therefore a `HookContextFiles`
 * overflow written here is provably NOT deleted by the hourly IPC sweep, by
 * `discard()`, or by the partial-suffix cleanup before its consumer reads it.
 * `PROCEED`, not `HALT` — a clean retained-lifetime boundary exists in-tree.
 */
final class HookContextFiles
{
    /**
     * The dedicated sub-directory these overflow files live in, under
     * `sys_get_temp_dir()`. Kept distinct from the bare temp directory that
     * {@see ToolIpcFiles::sweep()} globs, which is exactly why the sweep cannot
     * see a file written here (see the class docblock proof).
     */
    public const DIR_NAME = 'sc-hook-ctx';

    /**
     * Written first, `rename`-d into place — the same atomicity reason as
     * {@see ToolIpcFiles::PARTIAL_SUFFIX}: a consumer must never observe a
     * partially written overflow.
     */
    private const PARTIAL_SUFFIX = '.partial';

    /**
     * Bytes of the cap reserved for the spillover marker itself, so the string
     * {@see bound()} returns never exceeds the cap after the marker is appended.
     * Sized to out-hold the longest marker this class emits (a temp-dir path plus
     * the "N bytes retained" sentence) with room to spare.
     */
    private const MARKER_RESERVE_BYTES = 512;

    /**
     * Absolute path of the retained-overflow directory, created `0700` on demand.
     *
     * Exposed (rather than private) so the retention test can name the exact
     * directory the {@see ToolIpcFiles::sweep()} predicate must not reach, and so
     * a future session-teardown pass has one place to reclaim from.
     */
    public static function dir(): string
    {
        $dir = sys_get_temp_dir() . '/' . self::DIR_NAME;

        if (!is_dir($dir)) {
            $previous = umask(0o077);

            try {
                @mkdir($dir, 0o700, true);
            } finally {
                umask($previous);
            }
        }

        return $dir;
    }

    /**
     * Bound one model-visible context string to $cap BYTES, spilling the full
     * text into a retained file when it is longer.
     *
     * Returns a string whose length in BYTES never exceeds $cap:
     *   - at or under the cap: the input unchanged, nothing written;
     *   - over the cap: a `mb_strcut` byte-boundary HEAD of the text (the
     *     TruncatesOutput idiom, so the cut cannot split a UTF-8 codepoint on
     *     text that happens to be valid UTF-8, and cannot cut a multi-byte
     *     sequence in half into mojibake either), then a marker naming the
     *     retained file and the full byte count. The retained file holds the
     *     ENTIRE original text.
     *
     * $cap IS A REQUIRED ARGUMENT with no "no cap" sentinel, deliberately —
     * {@see \SugarCraft\Crush\Tools\Concerns\TruncatesOutput} records twice that
     * a `$maxBytes <= 0` "disabled" sentinel silently un-bound payloads meant to
     * be capped (Glob returned 1,091,833 bytes against a 65,536 cap). The head
     * room is `max(1, …)`-guarded for the same reason {@see
     * \SugarCraft\Crush\Tools\BuiltIn\Glob} guards its reserve: a cap smaller
     * than the marker reserve still yields at least one byte of preview rather
     * than a zero- or negative-length `mb_strcut`.
     */
    public static function bound(string $text, int $cap): string
    {
        if (strlen($text) <= $cap) {
            return $text;
        }

        $total = strlen($text);
        $path = null;

        try {
            $path = self::write($text);
        } catch (\RuntimeException) {
            // A retained file that cannot be written (read-only or full `/tmp`)
            // must not take the hook path down with it: a hook is OBSERVABILITY,
            // not the answer. Fall back to a preview-only marker that says the
            // remainder could not be retained rather than naming a path that
            // does not exist.
            $marker = sprintf(
                ' … [hook additional context truncated: %d of %d bytes shown; the full '
                . 'output could not be retained (overflow directory unwritable)]',
                0,
                $total,
            );
        }

        if ($path !== null) {
            $marker = sprintf(
                ' … [hook additional context truncated: %d of %d bytes shown; the full '
                . 'output is retained at %s]',
                0,
                $total,
                $path,
            );
        }

        // The preview room is the cap minus the marker's own footprint, floored
        // at one byte (see the `$maxBytes <= 0` note above) — using the constant
        // reserve rather than this specific marker's length keeps the returned
        // string under $cap even on a pathologically long temp directory.
        $room = max(1, $cap - self::MARKER_RESERVE_BYTES);
        $preview = mb_strcut($text, 0, $room, 'UTF-8');

        // Re-stamp the actual shown-byte count into the marker now that the
        // preview length is known.
        $marker = preg_replace_callback(
            '/truncated: \d+ of/',
            static fn (array $m): string => 'truncated: ' . strlen($preview) . ' of',
            $marker,
            1,
        );

        $result = $preview . $marker;

        // FINAL CAP GUARANTEE. For any $cap at or above MARKER_RESERVE_BYTES the
        // reserve already keeps `preview . marker` under $cap, so this is a no-op
        // on the only path production takes (the cap is always
        // HookResult::MAX_ADDITIONAL_CONTEXT_BYTES = 10,000). For a degenerate
        // $cap below the reserve the marker cannot fit — rather than return a
        // string that violates its own documented postcondition, clamp to $cap. The
        // full text is already retained in the file written above, so a clamped
        // tail costs nothing but the (unreachable) in-marker path for a tiny cap.
        if (strlen($result) > $cap) {
            $result = mb_strcut($result, 0, $cap, 'UTF-8');
        }

        return $result;
    }

    /**
     * Write the full overflow text, privately (0600) and atomically, and return
     * its absolute path. The file is RETAINED — nothing here unlinks it; that is
     * the whole point of R-2 (see the class docblock).
     */
    public static function write(string $text): string
    {
        $dir = self::dir();
        $file = $dir . '/ctx-' . bin2hex(random_bytes(8)) . '.txt';
        $partial = $file . self::PARTIAL_SUFFIX;

        $previous = umask(0o077);

        try {
            if (@file_put_contents($partial, $text) === false) {
                // The preview must still be actionable even if the retained file
                // cannot be written (a read-only or full `/tmp`); report the
                // failure in the marker the caller builds rather than throwing
                // out of a hook path that is OBSERVABILITY, not the answer.
                throw new \RuntimeException('unable to write hook overflow partial: ' . $partial);
            }
        } finally {
            umask($previous);
        }

        if (!@rename($partial, $file)) {
            @unlink($partial);

            throw new \RuntimeException('unable to finalise hook overflow file: ' . $file);
        }

        return $file;
    }
}

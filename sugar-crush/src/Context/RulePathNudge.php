<?php

declare(strict_types=1);

namespace SugarCraft\Crush\Context;

use InvalidArgumentException;
use SugarCraft\Crush\Context\Triggers\PathTrigger;

/**
 * Session-lifetime tracker that turns a rule's `paths:` frontmatter into a live
 * auto-scoping signal (prompt_plan.md §1.10 escalation item (4), P6.S5b).
 *
 * Until now `paths:` was parsed into a {@see PathTrigger} by
 * {@see Rule::buildTriggers()} and read by nobody: {@see Runtime}'s splice
 * rendered every rule into every session, so a rule scoped to `src/**`
 * instructed a session that never touched `src/`. This class is the half that
 * decides WHEN a scoped rule reaches the model, and `Runtime`'s splice now
 * defers exactly the rules this class claims, through the one shared predicate
 * {@see isPathScoped()} — one named gate, so the two can never disagree about
 * which rules are path-scoped and a rule is either spliced or nudged, never
 * both, never neither.
 *
 * SHAPE: the user ruled "byte-bounded whole bodies with deferral plus a pointer
 * tail", and named {@see \SugarCraft\Crush\Support\HookContextFiles} as the
 * ancestor — bounded model-visible bytes with a pointer that says where the
 * whole lives. The transplant keeps the two halves of that shape and drops the
 * one thing that makes a rule different from a log line: a truncated rule is a
 * half-instructed model, which {@see RuleLoader}'s own doc-block refuses whole,
 * so an entry here is INDIVISIBLE. A rule that fits the remaining budget is
 * delivered complete; one that does not is delivered as exactly one pointer
 * line, `Rule 'x' deferred: budget. Read /abs/path` — and never as the first
 * N bytes of its body. Nothing silently vanishes: every matched rule either
 * arrives whole, arrives pointed at, or is counted in the trailing
 * "announce on a later call" note.
 *
 * BUDGETS ARE PRICED HONESTLY AGAINST THE SHIPPED CALL SITES, and the honest
 * answer is that deferral is the COMMON case, not the edge. Grep and Glob spend
 * `intdiv(65_536, 8)` = 8,192 bytes on a nudge; Read spends
 * `intdiv(1_048_576, 8)` = 131,072; Edit and Write pass no budget at all and so
 * get this class's own ceiling. A rule file may hold
 * {@see RuleLoader::MAX_FILE_BYTES} = 65,536 raw bytes, which after
 * `PromptFence::escape()` is more still, so a large rule can NEVER fit a Grep or
 * Glob call at any entry count. That is by design: the budget protects the
 * common case (a short rule the model should simply have) and the pointer serves
 * the long tail (a rule it can go and Read).
 *
 * WHY THIS CEILING BOUNDS EVEN THE POINTER. The envelope arithmetic in
 * {@see maxBytes()} has to be a true statement, and a pointer line carries two
 * unbounded pieces of repository text — the rule's `name:` and its absolute
 * path — so {@see pointer()} holds that line to {@see MAX_POINTER_BYTES} by
 * construction: the path survives whole in every realistic case, the name is a
 * label and clips first, and a path so long it cannot fit alone clips the line
 * with a visible marker rather than breaking the ceiling. A clipped pointer is
 * degraded prose; a clipped BODY would be a different rule.
 *
 * The tracker holds no filesystem handle and walks nothing: the rules arrive as
 * the list {@see RuleLoader::load()} already produced for the session, the same
 * set the splice iterates, and matching is pure string work against compiled
 * globs. It is deliberately invisible to the read-sink census over `src/` for
 * the reason {@see PathTrigger} gives.
 *
 * This is a SugarCraft architecture type, not a port — charmbracelet/crush has
 * no rule-nudge symbol, so the repo's "Mirrors charmbracelet/…" convention does
 * not apply. Its shape is borrowed from
 * {@see \SugarCraft\Crush\Skills\SkillPathNudge}, which it deliberately does NOT
 * share a class with: one tracker per subject keeps a skill announcement and a
 * rule delivery from inheriting each other's budget.
 */
final class RulePathNudge
{
    /**
     * Rule paths already surfaced this session, keyed for O(1) re-touch checks.
     *
     * Keyed by {@see Rule::$path} and not by {@see Rule::$name}: `name:` is
     * display text taken from frontmatter, so two files in two tiers can carry
     * the same one, and `RuleLoader::load()` de-duplicates by `realpath()`. The
     * path is the identity that is provably unique in the list this tracker is
     * handed, and an identity collision here would retire a rule for the whole
     * session without ever naming it.
     *
     * @var array<string, true>
     */
    private array $announced = [];

    /**
     * The rules this channel can announce: path-scoped, non-blank-bodied.
     *
     * Resolved once in the constructor rather than re-derived per tool call,
     * and filtered by exactly the same predicate {@see hasPending()} and
     * {@see forPaths()} consult — the E72 lesson from the sibling skills tracker
     * is that a candidate one filter admits and the other refuses leaves
     * `hasPending()` true forever, so the steady state of a long session stops
     * short-circuiting.
     *
     * @var list<Rule>
     */
    private readonly array $candidates;

    /**
     * Opens every nudge. Named once so {@see maxBytes()} prices it.
     */
    private const HEADER = "<system-reminder>\n"
        . "These rules are scoped to paths you just touched. Follow them:\n";

    /** Closes every nudge. */
    private const FOOTER = "\n</system-reminder>";

    /**
     * The per-entry pricing unit of {@see maxBytes()}: the bytes that formula
     * bills for one line slot — a rule's name line plus its whole escaped body.
     *
     * It is NOT a gate on delivery: {@see forPaths()} prices each entry against
     * the REMAINING room — the caller budget capped at the ceiling below — so a
     * body priced over this constant ships whole while room lasts (~4,097 bytes
     * for a first entry), and the pointer branch triggers on room exhaustion,
     * never on this number. The gate rather than clip holds — the gate is the
     * room the loop checks, not this constant.
     */
    private const MAX_ENTRY_BYTES = 2048;

    /**
     * The most bytes ONE pointer line may occupy.
     *
     * Must stay at or under {@see MAX_ENTRY_BYTES}, because {@see maxBytes()}
     * prices every line of a nudge at the larger of the two. Asserted rather
     * than assumed in
     * {@see \SugarCraft\Crush\Tests\Context\RulePathNudgeTest}.
     */
    private const MAX_POINTER_BYTES = 1024;

    /**
     * The most lines — bodies and pointers TOGETHER — ONE nudge may carry.
     *
     * A count bound is required in addition to the byte bound for the reason
     * {@see \SugarCraft\Crush\Skills\SkillPathNudge} gives for its own: a single
     * `paths: ['**\/*.php']` rule matches every Read in a PHP tree, so without
     * a count ceiling the total grows linearly with the number of rules that
     * happen to claim the area.
     */
    private const MAX_ENTRIES = 2;

    /**
     * The two literal halves of a pointer line, split so {@see pointer()} can
     * budget the name and the path against each other instead of guessing at a
     * `sprintf()` width.
     */
    private const POINTER_HEAD = "Rule '";

    private const POINTER_TAIL = "' deferred: budget. Read ";

    /**
     * Ends a name — or, only for a path too long to fit alone, a whole pointer
     * line — that did not fit {@see MAX_POINTER_BYTES}.
     */
    private const CLIP_MARKER = ' [clipped]';

    /**
     * Counts the matched rules this call left unannounced.
     *
     * Worded "on a later call" because that is what happens: an unemitted rule
     * spends no mark, so the next matching tool call delivers it — unless the
     * rule body is simply too big for this channel, in which case the pointer
     * line is what arrives, on whichever call has room for a pointer.
     */
    private const DEFERRED_NOTE = '... [%d further path-scoped rule(s) matched; they announce on a later call.]';

    /**
     * The share of its own output cap a capped tool spends on the rule channel:
     * one eighth, the same share the skills channel takes.
     *
     * A separate constant rather than
     * {@see \SugarCraft\Crush\Skills\SkillPathNudge::CALLER_BUDGET_DIVISOR} on
     * purpose: the two channels have different ceilings, so the dial each of
     * them can afford is a different decision, and importing the skills dial
     * would move rule budgets whenever somebody retunes skill budgets. The
     * guard test prices each channel against the shipped caps independently.
     */
    public const CALLER_BUDGET_DIVISOR = 8;

    /**
     * @param list<Rule> $rules The session's loaded rule list, in loader order.
     *
     * @throws InvalidArgumentException if the list holds a non-Rule element.
     */
    public function __construct(array $rules)
    {
        $candidates = [];
        foreach ($rules as $rule) {
            if (!$rule instanceof Rule) {
                throw new InvalidArgumentException(sprintf(
                    'RulePathNudge expects a list of Rule instances, %s given.',
                    get_debug_type($rule),
                ));
            }

            if (!self::isPathScoped($rule) || trim($rule->body) === '') {
                continue;
            }

            $candidates[] = $rule;
        }

        $this->candidates = $candidates;
    }

    /**
     * Build a tracker over the rules the session already loaded.
     *
     * @param list<Rule> $rules
     */
    public static function new(array $rules): self
    {
        return new self($rules);
    }

    /**
     * THE ONE GATE: does this rule carry a `paths:` trigger?
     *
     * `Runtime::systemPromptSections()` asks it to decide what to SKIP, and this
     * class asks it to decide what to announce, so the two halves of P6.S5b read
     * one predicate. The `instanceof` is load-bearing in the direction the
     * golden law pins: the question is "does this rule carry a PATH trigger",
     * never "does this rule carry a trigger". A rule with a `description:`
     * already carries an {@see \SugarCraft\Crush\Context\Triggers\IntentTrigger}
     * — including the fixture rule behind the frozen 8,278-byte golden system
     * prompt — so keying on any trigger would drop a standing rule out of
     * `<user-rules>` and move those bytes.
     */
    public static function isPathScoped(Rule $rule): bool
    {
        foreach ($rule->triggers as $trigger) {
            if ($trigger instanceof PathTrigger) {
                return true;
            }
        }

        return false;
    }

    /**
     * Nudge text for a single touched path, or null when nothing new matches.
     *
     * $budget as in {@see forPaths()}.
     */
    public function forPath(string $path, ?int $budget = null): ?string
    {
        return $this->forPaths([$path], $budget);
    }

    /**
     * Nudge text for a batch of touched paths (Glob and Grep resolve many at
     * once), or null when no path-scoped rule newly matches.
     *
     * Never longer than {@see maxBytes()}, whatever the rule set holds, and
     * never longer than $budget when one is given. Null for $budget means "the
     * caller has no output cap to spend inside" — which is what Edit and Write
     * pass, mirroring the skills channel — and the class ceiling still applies.
     *
     * A budget too small for even one entry returns null and MARKS NOTHING, so a
     * rule is never retired for the session by a call that never named it.
     *
     * @param list<string> $paths
     */
    public function forPaths(array $paths, ?int $budget = null): ?string
    {
        if ($paths === [] || !$this->hasPending()) {
            return null;
        }

        // Collected before anything is marked, because how many matched rules
        // did not fit is part of the result and cannot be known mid-loop.
        $pending = [];
        foreach ($this->candidates as $rule) {
            if (isset($this->announced[$rule->path]) || isset($pending[$rule->path])) {
                continue;
            }

            if (!self::matchesAny($rule, $paths)) {
                continue;
            }

            $pending[$rule->path] = $rule;
        }

        if ($pending === []) {
            return null;
        }

        $room = $budget === null ? self::maxBytes() : min($budget, self::maxBytes());
        // Priced at its worst case (PHP_INT_MAX digits), for the reason the
        // skills tracker prices it there: the count that fills `%d` is decided
        // by where this loop stops. +1 for the newline implode() puts before it.
        $noteReserve = strlen(sprintf(self::DEFERRED_NOTE, PHP_INT_MAX)) + 1;

        $used = strlen(self::HEADER) + strlen(self::FOOTER);
        $total = count($pending);
        $seen = 0;
        $lines = [];
        $emitted = [];

        foreach ($pending as $path => $rule) {
            ++$seen;
            if (count($lines) >= self::MAX_ENTRIES) {
                break;
            }

            $entry = self::entry($rule);
            $cost = strlen($entry) + ($lines === [] ? 0 : 1);
            // The last pending rule cannot leave a remainder, so it is the one
            // entry that does not have to pay for the note it would introduce.
            $reserve = $seen === $total ? 0 : $noteReserve;
            if ($used + $cost + $reserve <= $room) {
                $used += $cost;
                $lines[] = $entry;
                $emitted[] = $path;

                continue;
            }

            // The body does not fit, so the body is NOT delivered — the ruling's
            // indivisibility, and the whole reason this branch exists. What
            // arrives instead is the pointer, and it pays for itself the same
            // way the body would have.
            $pointer = self::pointer($rule);
            $cost = strlen($pointer) + ($lines === [] ? 0 : 1);
            if ($used + $cost + $reserve > $room) {
                break;
            }

            $used += $cost;
            $lines[] = $pointer;
            $emitted[] = $path;
        }

        if ($lines === []) {
            return null;
        }

        foreach ($emitted as $path) {
            // Marked HERE and not during collection: only a line the model
            // actually receives may spend the one-shot mark. A DEFERRED rule is
            // marked too, because the ruling counts a pointer as delivery — the
            // model was told the rule exists and where to read it.
            $this->announced[$path] = true;
        }

        $deferred = $total - count($lines);
        if ($deferred > 0) {
            $lines[] = sprintf(self::DEFERRED_NOTE, $deferred);
        }

        return self::HEADER . implode("\n", $lines) . self::FOOTER;
    }

    /**
     * The most bytes {@see forPaths()} can ever return.
     *
     * Priced from the parts rather than hardcoded, so changing any of them moves
     * this with it. Each of the {@see MAX_ENTRIES} line slots is priced at
     * {@see MAX_ENTRY_BYTES} — pointers tighter, at {@see MAX_POINTER_BYTES} —
     * and it is the room test on the running total that holds the ceiling.
     */
    public static function maxBytes(): int
    {
        return strlen(self::HEADER)
            + self::MAX_ENTRIES * (self::MAX_ENTRY_BYTES + 1)
            + strlen(sprintf(self::DEFERRED_NOTE, PHP_INT_MAX))
            + strlen(self::FOOTER);
    }

    /**
     * The smallest tool output cap whose {@see CALLER_BUDGET_DIVISOR} share can
     * still hold a whole worst-case nudge — margin exactly 1.0x.
     *
     * The number the shipped-cap guard is read against, derived rather than
     * remembered, exactly as
     * {@see \SugarCraft\Crush\Skills\SkillPathNudge::smallestUnclippedCallerCap()}
     * is for the skills channel.
     */
    public static function smallestUnclippedCallerCap(): int
    {
        return self::maxBytes() * self::CALLER_BUDGET_DIVISOR;
    }

    /**
     * Rule paths already surfaced this session, in announcement order.
     *
     * `strval` over the raw keys because PHP coerces a decimal-integer string
     * key to `int` on insertion, and a path is arbitrary repository text: a
     * rule file literally named `123.md` under a directory called `0` could
     * otherwise come back out of the merge on the far side of a fork as an int.
     *
     * @return list<string>
     */
    public function announcedPaths(): array
    {
        return array_map(strval(...), array_keys($this->announced));
    }

    /**
     * Union $paths into the announced set, so the announce-once rule survives a
     * fork.
     *
     * A tool run inside one of {@see \SugarCraft\Crush\Runtime}'s forked tool
     * children announces into the CHILD's copy of this tracker; without this the
     * mark dies with the child and the same rule is delivered twice. A union,
     * never a replacement: concurrent children report overlapping sets in no
     * defined order. See {@see \SugarCraft\Crush\Tools\CarriesSessionState}.
     *
     * @param list<string|int> $paths
     */
    public function markAnnouncedPaths(array $paths): void
    {
        foreach ($paths as $path) {
            if (is_string($path) || is_int($path)) {
                $path = (string) $path;
                if ($path !== '') {
                    $this->announced[$path] = true;
                }
            }
        }
    }

    /**
     * True while at least one announceable path-scoped rule is still unannounced.
     *
     * Guards the match loop so the steady state of a long session costs one array
     * walk instead of a glob match per rule per touched path — which for a Glob
     * handing over a whole match list is paths x patterns per call, forever.
     * The filter here MUST stay identical to the one {@see forPaths()} collects
     * with, or the guard never fires again (E72).
     */
    private function hasPending(): bool
    {
        foreach ($this->candidates as $rule) {
            if (!isset($this->announced[$rule->path])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Does this rule's `paths:` trigger admit any of the touched paths?
     *
     * Matched against exactly what the tool resolved — an absolute path — the
     * same operand {@see \SugarCraft\Crush\Skills\SkillPathNudge} is fed, because
     * {@see PathTrigger} is anchored and root-blind and a repo-relative glob
     * would otherwise answer about a string nobody wrote. A rule author writing a
     * directory-prefixed glob means the leading-`**`-tolerant dialect
     * {@see \SugarCraft\Crush\Util\PathGlob} documents; the P6.S5a ruling fixed
     * the dialect, not the spelling of any shipped rule.
     */
    private static function matchesAny(Rule $rule, array $paths): bool
    {
        foreach ($rule->triggers as $trigger) {
            if (!$trigger instanceof PathTrigger) {
                continue;
            }

            foreach ($paths as $path) {
                if ($trigger->matches($path)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * One delivered entry: the rule's name line and its WHOLE escaped body.
     *
     * There is no clip here and there must never be one; the caller checks this
     * fits before emitting it, and checks the pointer otherwise. Every byte of
     * it comes from the repository, so every byte of it goes through
     * {@see PromptFence::escape()} — the roster includes `system-reminder`, and
     * a rule body that could open its own reminder would be the platform's own
     * voice forged out of a cloned checkout.
     */
    private static function entry(Rule $rule): string
    {
        return '- ' . PromptFence::escape($rule->name) . "\n" . PromptFence::escape($rule->body);
    }

    /**
     * One pointer line, held to {@see MAX_POINTER_BYTES} by construction.
     *
     * The path is the payload — a pointer whose path is cut cannot be followed —
     * so the name takes the clip when the two together overflow, and the whole
     * line is clipped only in the case where the path alone cannot fit, which
     * needs an absolute path over a kilobyte.
     */
    private static function pointer(Rule $rule): string
    {
        $name = PromptFence::escape($rule->name);
        $path = PromptFence::escape($rule->path);
        $line = self::POINTER_HEAD . $name . self::POINTER_TAIL . $path;

        if (strlen($line) <= self::MAX_POINTER_BYTES) {
            return $line;
        }

        $pathRoom = self::MAX_POINTER_BYTES
            - strlen(self::POINTER_HEAD)
            - strlen(self::POINTER_TAIL)
            - strlen($path);

        if ($pathRoom <= 0) {
            return mb_strcut($line, 0, self::MAX_POINTER_BYTES - strlen(self::CLIP_MARKER), 'UTF-8')
                . self::CLIP_MARKER;
        }

        if (strlen($name) > $pathRoom) {
            $name = mb_strcut($name, 0, max(0, $pathRoom - strlen(self::CLIP_MARKER)), 'UTF-8')
                . self::CLIP_MARKER;
        }

        return self::POINTER_HEAD . $name . self::POINTER_TAIL . $path;
    }
}

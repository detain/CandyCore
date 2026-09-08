<?php

declare(strict_types=1);

namespace SugarCraft\Crush\Tests\Providers;

use PHPUnit\Framework\TestCase;
use SugarCraft\Crush\Providers\CacheBreakpoints;

/**
 * P10.S2 — CacheBreakpoints wipe-then-reapply.
 *
 * Each of the plan's four load-bearing constraints (§4.15, all four measured
 * upstream failures) owns at least one test here, and every test asserts
 * VALUES — exact counts, exact mark positions, exact block arrays — never
 * shapes. §1.11: none of these pass on a decorative implementation; the
 * step-brief's deletion experiments are reported per test.
 *
 * The fixtures are Anthropic wire shapes copied from what the shipped
 * providers already build — {@see VertexProvider::formatAnthropicMessages()}
 * turns and {@see VertexProvider::formatAnthropicTools()} tool definitions —
 * not invented payload dialects.
 */
final class CacheBreakpointsTest extends TestCase
{
    /**
     * Constraint: the default mark plan. crush §5.8's four marks — last tool +
     * last system + last two messages — land on EXACTLY the right blocks, not
     * merely "four marks somewhere".
     */
    public function testDefaultMarkPlanIsLastToolPlusLastSystemPlusLastTwoMessages(): void
    {
        $breakpoints = new CacheBreakpoints();
        $tools = [
            ['name' => 'read', 'description' => 'r', 'input_schema' => []],
            ['name' => 'edit', 'description' => 'e', 'input_schema' => []],
        ];
        $messages = [
            ['role' => 'system', 'content' => [['type' => 'text', 'text' => 'SYS']]],
            ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'first']]],
            ['role' => 'assistant', 'content' => [['type' => 'text', 'text' => 'second']]],
            ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'third']]],
        ];

        $out = $breakpoints->apply($messages, $tools);

        // The FIRST tool is not the marked one; the LAST tool is.
        self::assertArrayNotHasKey('cache_control', $out['tools'][0]);
        self::assertSame(['type' => 'ephemeral'], $out['tools'][1]['cache_control']);

        // System marked on its last (only) block; the FIRST message left alone.
        self::assertSame(
            ['type' => 'text', 'text' => 'SYS', 'cache_control' => ['type' => 'ephemeral']],
            $out['messages'][0]['content'][0],
        );
        self::assertArrayNotHasKey('cache_control', $out['messages'][1]['content'][0]);

        // Last two messages marked, on their exact blocks.
        self::assertSame(
            ['type' => 'text', 'text' => 'second', 'cache_control' => ['type' => 'ephemeral']],
            $out['messages'][2]['content'][0],
        );
        self::assertSame(
            ['type' => 'text', 'text' => 'third', 'cache_control' => ['type' => 'ephemeral']],
            $out['messages'][3]['content'][0],
        );

        self::assertSame(4, $this->census($out)['total']);
    }

    /**
     * CONSTRAINT 1 — THE WIPE. Anthropic caps cache_control at 4 per request;
     * a 20-step agentic loop feeding apply() output back into apply() must
     * never accumulate a fifth breakpoint. Pinned as the exact per-step count.
     */
    public function testTwentySimulatedStepsNeverLetTheBreakpointCountCrossTheApiCap(): void
    {
        $breakpoints = new CacheBreakpoints();
        $tools = [
            ['name' => 'read', 'description' => 'r', 'input_schema' => []],
            ['name' => 'edit', 'description' => 'e', 'input_schema' => []],
        ];
        $messages = [
            ['role' => 'system', 'content' => 'SYS'],
            ['role' => 'user', 'content' => 'go'],
        ];

        $perStep = [];
        for ($step = 0; $step < 20; $step++) {
            $messages[] = [
                'role' => 'assistant',
                'content' => [
                    ['type' => 'text', 'text' => "thinking $step"],
                    ['type' => 'tool_use', 'id' => "call-$step", 'name' => 'read', 'input' => []],
                ],
            ];
            $messages[] = [
                'role' => 'user',
                'content' => [['type' => 'tool_result', 'tool_use_id' => "call-$step", 'content' => "result $step"]],
            ];

            $out = $breakpoints->apply($messages, $tools);
            $messages = $out['messages'];
            $tools = $out['tools'];
            $perStep[] = $this->census($out)['total'];
        }

        self::assertSame(array_fill(0, 20, 4), $perStep);
        self::assertLessThanOrEqual(4, max($perStep));
    }

    /**
     * CONSTRAINT 2 — automatic caching consumes one of the four slots. With a
     * request already carrying a non-ephemeral (Bedrock cachePoint dialect)
     * breakpoint, this class adds AT MOST three explicit marks — and the
     * automatic mark survives byte-for-byte, because it is not ours to wipe.
     */
    public function testAnAutomaticCacheBreakpointConsumesOneOfTheFourSlots(): void
    {
        $breakpoints = new CacheBreakpoints();
        $tools = [['name' => 'read', 'description' => 'r', 'input_schema' => []]];
        $messages = [
            [
                'role' => 'system',
                'content' => [
                    ['type' => 'text', 'text' => 'A'],
                    ['type' => 'text', 'text' => 'B', 'cache_control' => ['type' => 'default']],
                ],
            ],
            ['role' => 'user', 'content' => 'q'],
            ['role' => 'assistant', 'content' => 'a'],
            ['role' => 'user', 'content' => 'b'],
        ];

        $out = $breakpoints->apply($messages, $tools);
        $census = $this->census($out);

        self::assertSame(3, $census['ephemeral']);
        self::assertSame(1, $census['automatic']);
        self::assertSame(4, $census['total']);

        // The automatic mark is exactly the block the gateway wrote, untouched:
        // its breakpoint slid to the nearest FREE block (text 'A'), leaving 'B'
        // (already owned) alone.
        self::assertSame(
            ['type' => 'text', 'text' => 'B', 'cache_control' => ['type' => 'default']],
            $out['messages'][0]['content'][1],
        );
        self::assertSame(
            ['type' => 'text', 'text' => 'A', 'cache_control' => ['type' => 'ephemeral']],
            $out['messages'][0]['content'][0],
        );
    }

    /**
     * Constraint 2, deeper edge: two automatic marks shrink the explicit
     * budget to two — the system mark and the chain head only.
     */
    public function testTwoAutomaticBreakpointsShrinkTheExplicitBudgetToTwo(): void
    {
        $breakpoints = new CacheBreakpoints();
        $auto = ['type' => 'default'];
        $tools = [['name' => 'read', 'description' => 'r', 'input_schema' => [], 'cache_control' => $auto]];
        $messages = [
            ['role' => 'system', 'content' => [['type' => 'text', 'text' => 'S', 'cache_control' => $auto]]],
            ['role' => 'user', 'content' => 'q'],
            ['role' => 'assistant', 'content' => 'a'],
            ['role' => 'user', 'content' => 'b'],
        ];

        $out = $breakpoints->apply($messages, $tools);
        $census = $this->census($out);

        self::assertSame(2, $census['ephemeral']);
        self::assertSame(2, $census['automatic']);
        self::assertSame(4, $census['total']);

        // Budget of two leaves only the two chain positions: the marked system
        // block is already owned by the automatic mark (it slides nowhere —
        // single-block region — so no slot is spent there), and no slot remains
        // for the tool.
        self::assertSame([2, 3], $this->markedPositions($out['messages']));
        self::assertSame($auto, $out['tools'][0]['cache_control']);
    }

    /**
     * Constraint 2, pathological: four automatic marks leave zero explicit
     * budget — and every automatic mark is still standing. A budget of minus
     * two (five marks) is the mutation this test kills.
     */
    public function testFourAutomaticBreakpointsLeaveNoExplicitMarksAndStayIntact(): void
    {
        $breakpoints = new CacheBreakpoints();
        $auto = ['type' => 'default'];
        $messages = [
            ['role' => 'system', 'content' => [['type' => 'text', 'text' => 'S', 'cache_control' => $auto]]],
            ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'u', 'cache_control' => $auto]]],
            ['role' => 'assistant', 'content' => [['type' => 'text', 'text' => 'a', 'cache_control' => $auto]]],
            ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'b', 'cache_control' => $auto]]],
        ];

        $out = $breakpoints->apply($messages, []);
        $census = $this->census($out);

        self::assertSame(0, $census['ephemeral']);
        self::assertSame(4, $census['automatic']);
        self::assertSame(['S', 'u', 'a', 'b'], [
            $out['messages'][0]['content'][0]['text'],
            $out['messages'][1]['content'][0]['text'],
            $out['messages'][2]['content'][0]['text'],
            $out['messages'][3]['content'][0]['text'],
        ]);
        foreach ($out['messages'] as $i => $turn) {
            self::assertSame($auto, $turn['content'][0]['cache_control'], "automatic mark $i");
        }
    }

    /**
     * CONSTRAINT 3 — a breakpoint on a varying block is never a hit, so the
     * blocks this class marks in the stable prefix must carry identical bytes
     * across consecutive requests built from the same fixture prefix. Pinned
     * two ways: the system-region mark's serialised bytes across a grown
     * transcript, and byte-identical output for byte-identical input (no
     * clock, no randomness).
     */
    public function testMarkedBlockBytesAreIdenticalAcrossConsecutiveRequestsBuiltFromTheSamePrefix(): void
    {
        $breakpoints = new CacheBreakpoints();
        $tools = [['name' => 'read', 'description' => 'r', 'input_schema' => []]];
        $prefix = [
            ['role' => 'system', 'content' => [['type' => 'text', 'text' => 'SYS'], ['type' => 'text', 'text' => 'RULES']]],
            ['role' => 'user', 'content' => 'q1'],
        ];

        $first = $breakpoints->apply($prefix, $tools);
        $again = $breakpoints->apply($prefix, $tools);
        self::assertSame(json_encode($first), json_encode($again), 'same input, different bytes');

        $grown = $first['messages'];
        $grown[] = ['role' => 'assistant', 'content' => 'a1'];
        $grown[] = ['role' => 'user', 'content' => 'q2'];
        $second = $breakpoints->apply($grown, $tools);

        // Positive control: the first request really did mark the system tail.
        self::assertSame(
            ['type' => 'ephemeral'],
            $first['messages'][0]['content'][1]['cache_control'] ?? [],
        );

        // The marked prefix block survives into the next request byte-identical.
        self::assertSame(
            json_encode($first['messages'][0]['content'][1]),
            json_encode($second['messages'][0]['content'][1]),
        );
        self::assertSame(
            json_encode($first['tools'][0]),
            json_encode($second['tools'][0]),
        );
    }

    /**
     * CONSTRAINT 4 — 20-block lookback. One assistant turn of 41 blocks sits
     * above INTERMEDIATE_SPACING of history: intermediate marks must break it
     * into ≤15-block windows, the tool mark yields to repair slots (REPAIR
     * PRIORITY), and the totals land exactly. Positions derived from the wire
     * fixture: system block 0, setup block 1, long turn blocks 2..42.
     */
    public function testATurnLongerThanTheLookbackWindowCarriesIntermediateMarksEveryFifteenBlocks(): void
    {
        $breakpoints = new CacheBreakpoints();
        $tools = [
            ['name' => 'read', 'description' => 'r', 'input_schema' => []],
            ['name' => 'edit', 'description' => 'e', 'input_schema' => []],
        ];
        $longTurn = array_map(
            static fn (int $i): array => ['type' => 'tool_use', 'id' => "u$i", 'name' => 'bash', 'input' => ['c' => $i]],
            range(0, 40),
        );
        $messages = [
            ['role' => 'system', 'content' => 'SYS'],
            ['role' => 'user', 'content' => 'setup'],
            ['role' => 'assistant', 'content' => $longTurn],
        ];

        $out = $breakpoints->apply($messages, $tools);

        self::assertSame([0, 12, 27, 42], $this->markedPositions($out['messages']));
        self::assertSame(0, $this->census(['tools' => $out['tools'], 'messages' => []])['ephemeral'], 'the tool mark yields');

        // Every consecutive gap, anchored at the region start, stays within the
        // spacing, and every ~15-block window of the long turn holds a mark.
        $previous = 0;
        foreach ([0, 12, 27, 42] as $position) {
            self::assertLessThanOrEqual(15, $position - $previous, "gap into block $position");
            $previous = $position;
        }
        self::assertGreaterThanOrEqual(1, count(array_intersect($this->markedPositions($out['messages']), [12, 27, 42])));
        foreach ([[2, 16], [17, 31], [32, 46]] as [$from, $to]) {
            $window = array_values(array_filter(
                $this->markedPositions($out['messages']),
                static fn (int $p): bool => $p >= $from && $p <= $to,
            ));
            self::assertNotEmpty($window, "long-turn window $from..$to holds no breakpoint");
        }

        self::assertSame(4, $this->census($out)['total']);
    }

    /**
     * Constraint 4, polarity pair: the SAME transcript with a short last turn
     * keeps the tool mark — proving the yield is caused by the long turn, not
     * a blanket refusal to mark tools.
     */
    public function testAShortTurnKeepsTheToolMarkThatTheLongTurnSurrenders(): void
    {
        $breakpoints = new CacheBreakpoints();
        $tools = [['name' => 'read', 'description' => 'r', 'input_schema' => []]];
        $messages = [
            ['role' => 'system', 'content' => 'SYS'],
            ['role' => 'user', 'content' => 'setup'],
            ['role' => 'assistant', 'content' => [['type' => 'text', 'text' => 'short']]],
        ];

        $out = $breakpoints->apply($messages, $tools);

        self::assertSame(['type' => 'ephemeral'], $out['tools'][0]['cache_control']);
        self::assertSame([0, 1, 2], $this->markedPositions($out['messages']));
        self::assertSame(4, $this->census($out)['total']);
    }

    /**
     * Constraint 1's reach: the wipe clears stale ephemeral marks at MESSAGE
     * level (the crush/opencode message-level spelling) and TOOL level, not
     * just block level, then re-marks only where this class places marks.
     */
    public function testWipeReachesMessageLevelAndToolLevelStaleMarks(): void
    {
        $breakpoints = new CacheBreakpoints();
        $ephemeral = ['type' => 'ephemeral'];
        $tools = [
            ['name' => 'read', 'description' => 'r', 'input_schema' => [], 'cache_control' => $ephemeral],
            ['name' => 'edit', 'description' => 'e', 'input_schema' => []],
        ];
        $messages = [
            ['role' => 'system', 'content' => 'SYS'],
            ['role' => 'user', 'content' => 'q', 'cache_control' => $ephemeral],
            ['role' => 'assistant', 'content' => [['type' => 'text', 'text' => 'a', 'cache_control' => $ephemeral]]],
        ];

        $out = $breakpoints->apply($messages, $tools);

        self::assertArrayNotHasKey('cache_control', $out['messages'][1]);
        self::assertArrayNotHasKey('cache_control', $out['tools'][0]);
        self::assertSame($ephemeral, $out['tools'][1]['cache_control']);
        self::assertSame($ephemeral, $out['messages'][0]['content'][0]['cache_control']);
        self::assertSame($ephemeral, $out['messages'][2]['content'][0]['cache_control']);
        self::assertSame(4, $this->census($out)['total']);
    }

    /**
     * Boundary parsing: string content cannot carry a block-level marker, so it
     * normalises to exactly one text block — and that block then takes the
     * system mark. Pinned as an exact array, not a shape check.
     */
    public function testStringContentNormalisesToASingleMarkedTextBlock(): void
    {
        $breakpoints = new CacheBreakpoints();
        $out = $breakpoints->apply([['role' => 'system', 'content' => 'SYS']], []);

        self::assertSame(
            [['role' => 'system', 'content' => [['type' => 'text', 'text' => 'SYS', 'cache_control' => ['type' => 'ephemeral']]]]],
            $out['messages'],
        );
        self::assertSame([], $out['tools']);
    }

    /**
     * apply(apply(x)) === apply(x): the brief does not require idempotence but
     * it holds by construction (the wipe re-derives from scratch), so it is
     * pinned — on the short transcript and the 41-block long turn alike.
     */
    public function testApplyIsIdempotentOnShortAndLongTranscripts(): void
    {
        $breakpoints = new CacheBreakpoints();
        $tools = [['name' => 'read', 'description' => 'r', 'input_schema' => []]];

        $short = [
            ['role' => 'system', 'content' => 'SYS'],
            ['role' => 'user', 'content' => 'q'],
        ];
        $once = $breakpoints->apply($short, $tools);
        self::assertSame(json_encode($once), json_encode($breakpoints->apply($once['messages'], $once['tools'])));

        $long = [
            ['role' => 'system', 'content' => 'SYS'],
            ['role' => 'assistant', 'content' => array_map(
                static fn (int $i): array => ['type' => 'tool_use', 'id' => "u$i", 'name' => 'bash', 'input' => []],
                range(0, 44),
            )],
        ];
        $once = $breakpoints->apply($long, $tools);
        self::assertSame(json_encode($once), json_encode($breakpoints->apply($once['messages'], $once['tools'])));
    }

    /**
     * Both polarities of emptiness: nothing in, nothing marked, keys present;
     * tools-only still gets its one legitimate last-tool breakpoint.
     */
    public function testEmptyTranscriptMarksNothingWhileToolsOnlyMarksExactlyTheLastTool(): void
    {
        $breakpoints = new CacheBreakpoints();

        self::assertSame(['tools' => [], 'messages' => []], $breakpoints->apply([], []));

        $tools = [
            ['name' => 'read', 'description' => 'r', 'input_schema' => []],
            ['name' => 'edit', 'description' => 'e', 'input_schema' => []],
        ];
        $out = $breakpoints->apply([], $tools);

        self::assertArrayNotHasKey('cache_control', $out['tools'][0]);
        self::assertSame(['type' => 'ephemeral'], $out['tools'][1]['cache_control']);
        self::assertSame([], $out['messages']);
    }

    /**
     * Fail-fast at the boundary: a turn without a role 400s the wire later with
     * no useful detail, so it throws HERE, naming the bad key.
     */
    public function testATurnWithoutARoleThrowsNamingTheTurn(): void
    {
        $breakpoints = new CacheBreakpoints();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/non-empty string "role"; the turn at key 2/');

        $breakpoints->apply([
            ['role' => 'user', 'content' => 'ok'],
            ['role' => 'user', 'content' => 'ok'],
            ['content' => 'no role'],
        ], []);
    }

    /**
     * Fail-fast, second shape: content that is neither string nor array is a
     * bug upstream of us, not a state to paper over.
     */
    public function testNonStringNonArrayContentThrows(): void
    {
        $breakpoints = new CacheBreakpoints();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/message content must be a string or an array of blocks/');

        $breakpoints->apply([['role' => 'user', 'content' => 42]], []);
    }

    /**
     * Fail-fast, third shape: a non-array tool definition. The benign polarity
     * (array tools pass) is asserted by every other test in this file.
     */
    public function testANonArrayToolThrows(): void
    {
        $breakpoints = new CacheBreakpoints();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/every tool must be an array definition/');

        $breakpoints->apply([], ['read']);
    }

    // -------------------------------------------------------------------------
    // Measurement helpers (over the OUTPUT, not the implementation)
    // -------------------------------------------------------------------------

    /**
     * @param array{tools?: array<array-key, array<string, mixed>>, messages?: array<array-key, array<string, mixed>>} $out
     * @return array{ephemeral: int, automatic: int, total: int}
     */
    private function census(array $out): array
    {
        $marks = [];

        foreach (($out['tools'] ?? []) as $tool) {
            if (isset($tool['cache_control'])) {
                $marks[] = $tool['cache_control'];
            }
        }

        foreach (($out['messages'] ?? []) as $turn) {
            if (isset($turn['cache_control'])) {
                $marks[] = $turn['cache_control'];
            }

            foreach ($turn['content'] ?? [] as $block) {
                if (is_array($block) && isset($block['cache_control'])) {
                    $marks[] = $block['cache_control'];
                }
            }
        }

        $ephemeral = 0;
        foreach ($marks as $mark) {
            if (($mark['type'] ?? null) === 'ephemeral') {
                $ephemeral++;
            }
        }

        return ['ephemeral' => $ephemeral, 'automatic' => count($marks) - $ephemeral, 'total' => count($marks)];
    }

    /**
     * Flattened content-block indexes of every ephemeral-marked block, in wire
     * order — the coordinate system §4.15's lookback window counts in.
     *
     * @param array<array-key, array<string, mixed>> $messages
     * @return list<int>
     */
    private function markedPositions(array $messages): array
    {
        $positions = [];
        $index = -1;

        foreach ($messages as $turn) {
            foreach ($turn['content'] ?? [] as $block) {
                $index++;
                if (is_array($block) && ($block['cache_control']['type'] ?? null) === 'ephemeral') {
                    $positions[] = $index;
                }
            }
        }

        return $positions;
    }
}

<?php

declare(strict_types=1);

namespace SugarCraft\Crush\Tests\Chat;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler as GuzzleMockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use SugarCraft\Core\AsyncCmd;
use SugarCraft\Core\KeyType;
use SugarCraft\Core\Msg\KeyMsg;
use SugarCraft\Crush\Backend;
use SugarCraft\Crush\Backend\EngineBackend;
use SugarCraft\Crush\Backend\ReportsContextWindow;
use SugarCraft\Crush\Chat;
use SugarCraft\Crush\Hooks\HookContext;
use SugarCraft\Crush\Hooks\HookEvent;
use SugarCraft\Crush\Hooks\HookInterface;
use SugarCraft\Crush\Hooks\HookManager;
use SugarCraft\Crush\Hooks\HookRegistry;
use SugarCraft\Crush\Hooks\HookResult;
use SugarCraft\Crush\Message;
use SugarCraft\Crush\Providers\CustomProvider;
use SugarCraft\Crush\Role;

/**
 * P7.S2 done-when: the two turn-lifecycle hook events are dispatched from
 * `Chat::submit()`'s tail and their verdicts reach the MODEL-VISIBLE turn,
 * proven in the REQUEST THE PROVIDER RECEIVES.
 *
 * WHY A TWO-STEP DRIVE, AND WHY THE SECOND STEP IS A REAL PROVIDER BEHIND A
 * MOCKED TRANSPORT. Chat's own turn completion goes through
 * {@see EngineBackend::completeAsync()}, which forks under pcntl — a provider
 * double installed in the parent would have its `$requests` written in the
 * child and every assertion here would read an empty array and pass by
 * accident. So each test drives Chat to the turn boundary (which is where the
 * hooks fire and the history is committed), then hands the EXACT captured
 * history to a real `EngineBackend` over a real {@see CustomProvider} whose
 * Guzzle client is backed by a MockHandler with history middleware. That last
 * hop is the whole point: the assertion lands on the serialized HTTP body the
 * model would get — `"role":"system"` and the note's bytes in a specific slot —
 * not on a DTO one layer short of the wire.
 *
 * The alternative reading ("assert `Role::System` in the history Chat handed
 * its backend") is also done, in the same test, because the two prove different
 * things: the history assertion proves Chat INSERTED the note ahead of the
 * user's line; the body assertion proves it SURVIVED to the wire.
 *
 * Nothing here touches the prompt assembler: the notes ride HISTORY, which is
 * why wiring these events moves no golden ({@see testTheFixturesGoldensAreUntouchedByAHookedTurn}).
 */
final class SessionStartHookWireTest extends TestCase
{
    private const SESSION_NOTE = 'P7S2-SESSION-NOTE the deployment target for this session is production';

    private const PROMPT_NOTE = 'P7S2-PROMPT-NOTE this repository uses bun test';

    private const BLOCK_REASON = 'P7S2-BLOCK-REASON prompts are not accepted while the change freeze is on';

    /**
     * A note attached to a DENY, which must NEVER reach the model. Decision E's
     * load-bearing half is that a blocked hook's note is discarded outright, and
     * a deny carrying one is the only way to tell "discarded" apart from
     * "never looked at".
     */
    private const NOTE_ON_A_DENY = 'P7S2-MUST-BE-DISCARDED';

    // =========================================================================
    // SessionStart → provider payload
    // =========================================================================

    public function testSessionStartNoteReachesTheProviderPayloadAsASystemMessageBeforeTheFirstPrompt(): void
    {
        $recorder = new HookWireRecorder(1_000_000);
        [$turn, , $cmd] = $this->submit($this->chatWith($recorder, [
            $this->noteHook('ss', HookEvent::SessionStart, self::SESSION_NOTE),
        ]), 'first prompt of the run');

        // 1. Chat put the note in the turn batch, ahead of the user's line.
        $this->assertNotNull($cmd, 'the turn dispatched');
        $this->assertSame(Role::System, $turn->history[0]->role, 'the note is the first thing in the turn');
        $this->assertSame(self::SESSION_NOTE, $turn->history[0]->content);
        $this->assertSame(Role::User, $turn->history[1]->role);
        $this->assertSame('first prompt of the run', $turn->history[1]->content);

        // 2. The same history, serialized by a real provider, carries it.
        $body = $this->providerBodyFor($turn->history);
        $messages = json_decode($body, true)['messages'];
        $noteAt = $this->indexOfMessageCarrying($messages, self::SESSION_NOTE);
        $promptAt = $this->indexOfMessageCarrying($messages, 'first prompt of the run');

        $this->assertGreaterThan(-1, $noteAt, 'the SessionStart note is in the HTTP body the provider was handed');
        $this->assertSame('system', $messages[$noteAt]['role'], 'on the wire it is role:system, not text bolted onto the user turn');
        $this->assertLessThan($promptAt, $noteAt, 'and it sits BEFORE the first user message — Anthropic\'s "start of conversation, before the first prompt"');
    }

    // =========================================================================
    // UserPromptSubmit → provider payload
    // =========================================================================

    public function testUserPromptSubmitNoteRidesTheSameTurnAsTheSubmittedPrompt(): void
    {
        $recorder = new HookWireRecorder(1_000_000);
        [$turn, , $cmd] = $this->submit($this->chatWith($recorder, [
            $this->noteHook('ups', HookEvent::UserPromptSubmit, self::PROMPT_NOTE),
        ]), 'ship it');

        $this->assertNotNull($cmd);
        $this->assertSame(self::PROMPT_NOTE, $turn->history[0]->content);
        $this->assertSame(Role::System, $turn->history[0]->role);
        $this->assertSame(Role::User, $turn->history[1]->role);

        $body = $this->providerBodyFor($turn->history);
        $messages = json_decode($body, true)['messages'];

        $this->assertGreaterThanOrEqual(0, $this->indexOfMessageCarrying($messages, self::PROMPT_NOTE));
        $this->assertStringContainsString('ship it', $body);
        $this->assertSame('system', $messages[$this->indexOfMessageCarrying($messages, self::PROMPT_NOTE)]['role']);
    }

    public function testBothTurnNotesRideOneTurnInAnthropicsOrderAheadOfThePrompt(): void
    {
        $recorder = new HookWireRecorder(1_000_000);
        [$turn, , $cmd] = $this->submit($this->chatWith($recorder, [
            // Registered prompt-hook-first on purpose: the FIRE order is
            // gate-first (a blocked prompt must not start a session hook), while
            // the INSERT order is the §4.12 table's — session note ahead.
            $this->noteHook('ups', HookEvent::UserPromptSubmit, self::PROMPT_NOTE),
            $this->noteHook('ss', HookEvent::SessionStart, self::SESSION_NOTE),
        ]), 'ordered please');

        $this->assertNotNull($cmd);
        $this->assertSame(
            [self::SESSION_NOTE, self::PROMPT_NOTE, 'ordered please'],
            array_map(static fn(Message $m): string => $m->content, $turn->history),
            'SessionStart note, then the prompt note, then the user line — nothing else in the batch',
        );
        $this->assertSame(
            [Role::System, Role::System, Role::User],
            array_map(static fn(Message $m): Role => $m->role, $turn->history),
        );
    }

    // =========================================================================
    // Verdicts
    // =========================================================================

    public function testUserPromptSubmitBlockKeepsThePromptOffTheWireAndSurfacesTheReason(): void
    {
        $recorder = new HookWireRecorder(1_000_000);
        [$turn, , $cmd] = $this->submit($this->chatWith($recorder, [
            $this->verdictHook('deny-submit', HookEvent::UserPromptSubmit, HookResult::deny(self::BLOCK_REASON, self::NOTE_ON_A_DENY)),
        ]), 'the prompt that must not run');

        // discardsOnBlock(): no turn was dispatched and the backend never saw it.
        $this->assertNull($cmd, 'a blocked prompt does not dispatch a turn');
        $this->assertSame(0, $recorder->calls(), 'the backend was never asked');
        $this->assertCount(1, $turn->history, 'only the refusal notice was appended');
        $this->assertSame(Role::System, $turn->history[0]->role);
        $this->assertStringContainsString(self::BLOCK_REASON, $turn->history[0]->content, 'the reason is on the user-visible surface');
        $this->assertStringContainsString('not sent', $turn->history[0]->content);
        $this->assertStringNotContainsString(
            self::NOTE_ON_A_DENY,
            $turn->history[0]->content,
            'a blocked hook contributes no context, only its reason',
        );

        // The prompt text is absent from what a provider would receive for the
        // resulting history — asserted on the wire, not on the DTO.
        $body = $this->providerBodyFor($turn->history);
        $this->assertStringNotContainsString('the prompt that must not run', $body);
        $this->assertStringNotContainsString(self::NOTE_ON_A_DENY, $body);
    }

    public function testSessionStartBlockDiscardsTheNoteButStillDispatchesTheTurn(): void
    {
        $recorder = new HookWireRecorder(1_000_000);
        [$turn, , $cmd] = $this->submit($this->chatWith($recorder, [
            $this->verdictHook('deny-session', HookEvent::SessionStart, HookResult::deny('session notes are frozen', self::NOTE_ON_A_DENY)),
        ]), 'a prompt that still runs');

        $this->assertNotNull($cmd, 'SessionStart blocking does not block the session');
        $turn = $this->settle($turn, $cmd);
        $this->assertSame(1, $recorder->calls(), 'the turn reached the backend');
        $contents = array_map(static fn(Message $m): string => $m->content, $turn->history);
        $this->assertNotContains(self::NOTE_ON_A_DENY, $contents, 'the blocked hook\'s note is discarded, not smuggled in');
        $this->assertContains('a prompt that still runs', $contents);

        $notice = $turn->history[0];
        $this->assertSame(Role::System, $notice->role);
        $this->assertStringContainsString('session notes are frozen', $notice->content, 'the operator reason IS surfaced');
        $this->assertStringContainsString('discarded', $notice->content);

        $body = $this->providerBodyFor($turn->history);
        $this->assertStringNotContainsString(self::NOTE_ON_A_DENY, $body, 'and it never reaches the wire');
        $this->assertStringContainsString('a prompt that still runs', $body);
    }

    public function testAnUnansweredAskFromATurnHookFailsClosed(): void
    {
        // submit() has no UI to answer a turn hook's question and no queue that
        // could hold the turn for one, so ASK must not read as permission — the
        // same fail-closed line preToolUse() draws for a tool call.
        $recorder = new HookWireRecorder(1_000_000);
        [$turn, , $cmd] = $this->submit($this->chatWith($recorder, [
            $this->verdictHook('ask-submit', HookEvent::UserPromptSubmit, HookResult::ask('shall I continue?')),
        ]), 'a prompt behind an unanswered question');

        $this->assertNull($cmd, 'the turn did not run on an unanswered ASK');
        $this->assertSame(0, $recorder->calls());
        $this->assertStringContainsString(
            'Permission required:',
            $turn->history[0]->content,
            'and the user is told a decision was needed, not that a hook denied it',
        );
    }

    // =========================================================================
    // The no-op pin, and the once-per-session gate
    // =========================================================================

    public function testAChatWithNoHookConfiguredSendsAByteIdenticalPayload(): void
    {
        $prompt = 'the same prompt every time';

        // (1) No HookManager at all — the state every existing test and embedder
        //     that never wires hooks is already in.
        $plain = new Chat(backend: new HookWireRecorder(1_000_000));
        $baseline = $this->providerBodyFor($this->submit($plain, $prompt)[0]->history);

        // (2) A wired HookManager holding an EMPTY chain — the state a real
        //     session with no hooks.yaml is in. Must be indistinguishable.
        $emptyChain = $this->providerBodyFor($this->submit(
            $this->chatWith(new HookWireRecorder(1_000_000), [], new HookManager(new HookRegistry())),
            $prompt,
        )[0]->history);

        $this->assertSame($baseline, $emptyChain, 'an empty chain is indistinguishable on the wire from no hooks at all');

        // (3) And the comparison above is CAPABLE of seeing a note — without this
        //     third arm the byte-identity assertion would pass against a payload
        //     nothing could ever change, which is the vacuous shape 16.2 warns of.
        $withNote = $this->providerBodyFor($this->submit($this->chatWith(new HookWireRecorder(1_000_000), [
            $this->noteHook('ss', HookEvent::SessionStart, self::SESSION_NOTE),
        ]), $prompt)[0]->history);

        $this->assertNotSame($baseline, $withNote, 'the two bodies CAN differ, so their equality above means something');
        $this->assertStringContainsString(self::SESSION_NOTE, $withNote);
        $this->assertStringNotContainsString(self::SESSION_NOTE, $emptyChain);
    }

    public function testAChainThatProducesNoStdoutAddsNoSystemMessage(): void
    {
        // The other half of the no-op contract: hooks ARE wired and DO run, but
        // the empty additionalContext must not become an empty system message in
        // the turn — the applyPostToolUse rule applied to turn events.
        $recorder = new HookWireRecorder(1_000_000);
        [$turn, , $cmd] = $this->submit($this->chatWith($recorder, [
            $this->verdictHook('silent', HookEvent::SessionStart, HookResult::allow()),
            $this->verdictHook('silent-p', HookEvent::UserPromptSubmit, HookResult::allow()),
        ]), 'quiet turn');

        $this->assertNotNull($cmd);
        $this->assertCount(1, $turn->history, 'the user message and nothing else');
        $this->assertSame(Role::User, $turn->history[0]->role);
    }

    public function testSessionStartFiresOnceAndUserPromptSubmitFiresEveryPrompt(): void
    {
        $recorder = new HookWireRecorder(1_000_000);
        $chat = $this->chatWith($recorder, [
            $this->noteHook('ss', HookEvent::SessionStart, self::SESSION_NOTE),
            $this->noteHook('ups', HookEvent::UserPromptSubmit, self::PROMPT_NOTE),
        ]);

        [$chat, , $first] = $this->submit($chat, 'one');
        $this->assertNotNull($first);
        $chat = $this->settle($chat, $first);

        [$chat, , $second] = $this->submit($chat, 'two');
        $this->assertNotNull($second);
        $chat = $this->settle($chat, $second);

        $wire = $this->providerBodyFor($chat->history);

        // count($history) === 0 is the ONLY SessionStart gate, so exactly one
        // note across the whole session; UserPromptSubmit is per prompt, so two.
        $this->assertSame(1, substr_count($wire, self::SESSION_NOTE), 'SessionStart fired once, not once per prompt');
        $this->assertSame(2, substr_count($wire, self::PROMPT_NOTE), 'UserPromptSubmit fired on both prompts');
    }

    /**
     * The goldens this step must not move.
     *
     * A hook note that reached the PROMPT ASSEMBLER instead of history would
     * change the system prompt bytes on disk and this would be the test that
     * said so — which is why it asserts the two files' digests rather than
     * trusting that "system messages ride history" is true by construction.
     */
    public function testTheFixturesGoldensAreUntouchedByAHookedTurn(): void
    {
        $fixtures = dirname(__DIR__) . '/fixtures/prompt/';

        // Sizes first: a truncated read would hash-match nothing useful.
        $this->assertSame(7829, filesize($fixtures . 'golden-system-prompt.txt'));
        $this->assertSame(1060, filesize($fixtures . 'golden-agent-prompt.txt'));
        $this->assertSame('f09f37366a1925565dcc7725f659ff41', md5_file($fixtures . 'golden-system-prompt.txt'));
        $this->assertSame('ef0326dd38535aaa2f1d715919bff26e', md5_file($fixtures . 'golden-agent-prompt.txt'));

        // And a hooked turn changes neither.
        $recorder = new HookWireRecorder(1_000_000);
        $this->submit($this->chatWith($recorder, [
            $this->noteHook('ss', HookEvent::SessionStart, self::SESSION_NOTE),
        ]), 'a turn with a session note attached');

        $this->assertSame('f09f37366a1925565dcc7725f659ff41', md5_file($fixtures . 'golden-system-prompt.txt'));
        $this->assertSame('ef0326dd38535aaa2f1d715919bff26e', md5_file($fixtures . 'golden-agent-prompt.txt'));
    }

    // =========================================================================
    // Drive helpers
    // =========================================================================

    /**
     * @param list<HookInterface> $hooks
     */
    private function chatWith(Backend $backend, array $hooks, ?HookManager $manager = null): Chat
    {
        $manager ??= $this->managerWith($hooks);

        return new Chat(backend: $backend, hooks: $manager);
    }

    /**
     * @param list<HookInterface> $hooks
     */
    private function managerWith(array $hooks): HookManager
    {
        $registry = new HookRegistry();
        foreach ($hooks as $hook) {
            $registry->register($hook);
        }

        return new HookManager($registry);
    }

    /**
     * Type and Enter one draft, and hand back the post-dispatch Chat, the Chat
     * BEFORE dispatch (identical on a refusal, and the only place to look for the
     * refusal notice), and the turn Cmd — null when nothing dispatched.
     *
     * Typed through KeyMsg rather than injected: `Chat::withInputBuf()` is
     * private, and the keystroke route is the one that actually runs submit().
     *
     * @return array{0: Chat, 1: Chat, 2: ?\Closure}
     */
    private function submit(Chat $chat, string $draft): array
    {
        foreach (mb_str_split($draft) as $char) {
            [$chat] = $chat->update(new KeyMsg(KeyType::Char, $char));
        }

        [$turn, $cmd] = $chat->update(new KeyMsg(KeyType::Enter, ''));

        return [$turn, $chat, $cmd];
    }

    /**
     * Settle a dispatched turn by feeding its resolved Msg back through update(),
     * which is what clears `inFlight` and lets a second prompt be a real second
     * submission instead of a mid-turn queue entry.
     */
    private function settle(Chat $chat, \Closure $cmd): Chat
    {
        $asyncCmd = $cmd();
        $this->assertInstanceOf(AsyncCmd::class, $asyncCmd);

        $resolved = null;
        $asyncCmd->promise->then(static function ($msg) use (&$resolved): void {
            $resolved = $msg;
        });
        $this->assertNotNull($resolved, 'the recording backend resolves synchronously');

        [$settled] = $chat->update($resolved);

        return $settled;
    }

    private function noteHook(string $name, HookEvent $event, string $note): HookInterface
    {
        return $this->verdictHook($name, $event, HookResult::allow('', $note));
    }

    private function verdictHook(string $name, HookEvent $event, HookResult $verdict): HookInterface
    {
        return new class($name, $event, $verdict) implements HookInterface {
            public function __construct(
                private readonly string $hookName,
                private readonly HookEvent $hookEvent,
                private readonly HookResult $verdict,
            ) {}

            public function name(): string
            {
                return $this->hookName;
            }

            public function event(): HookEvent
            {
                return $this->hookEvent;
            }

            public function matcher(): string
            {
                // Empty matcher = fires on every toolName, and toolName is the
                // smuggled event sentinel (P7.S2 decision C).
                return '';
            }

            public function execute(HookContext $context): HookResult
            {
                return $this->verdict;
            }
        };
    }

    // =========================================================================
    // The real-provider second hop
    // =========================================================================

    /** @var list<array<string, mixed>> */
    private array $httpHistory = [];

    /**
     * The literal HTTP body a real provider builds for $history.
     *
     * `CustomProvider` IS the Anthropic-shaped OpenAI-compatible driver
     * (ProviderFactory::createAnthropic() returns one), and it maps a
     * SystemMessage inline at its array position — so the position assertions
     * below are about the slot the model reads, not about an intermediate array.
     *
     * Streaming is off because a MockHandler serves one plain JSON body; with it
     * on, Runtime would ask for `text/event-stream` and the canned reply would not
     * be an SSE stream.
     */
    private function providerBodyFor(array $history): string
    {
        $this->httpHistory = [];
        $stack = HandlerStack::create(new GuzzleMockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{"choices":[{"message":{"content":"ok"}}],"usage":{"total_tokens":1}}'),
        ]));
        $stack->push(Middleware::history($this->httpHistory));

        $client = new Client([
            'base_uri' => 'https://api.example.com/',
            'handler' => $stack,
        ]);

        $backend = new EngineBackend(
            new CustomProvider('custom', 'https://api.example.com', 'gpt-4', null, $client, false, false),
            'gpt-4',
        );

        $backend->complete($history);

        $this->assertCount(1, $this->httpHistory, 'the canned answer carries no tool call, so one request is the whole turn');

        return (string) $this->httpHistory[0]['request']->getBody();
    }

    /**
     * Index of the first message in a decoded wire body whose content carries
     * $needle, or -1. Hand-rolled on purpose: `array_search` over a column would
     * need a second pass to build it, and -1 keeps "absent" a value an assertion
     * can name instead of a null that reads as a bug in the finder.
     *
     * @param list<array{role: string, content: string}> $messages
     */
    private function indexOfMessageCarrying(array $messages, string $needle): int
    {
        foreach ($messages as $index => $message) {
            if (str_contains((string) ($message['content'] ?? ''), $needle)) {
                return $index;
            }
        }

        return -1;
    }
}

/**
 * A backend that records the history of every turn it is handed and answers with
 * a fixed two-character reply.
 *
 * It records IN THE PARENT, which is the entire reason the tests above can assert
 * anything about a Chat-driven turn at all: {@see EngineBackend::completeAsync()}
 * forks, so a provider double installed on a live Chat would never see the call.
 *
 * The reply is deliberately short — an echoing backend would copy the prompt back
 * into history every turn and inflate the fixture until the compaction tiers fired
 * of their own accord.
 *
 * Named distinctly from `ReminderWireRecorder` (ContextReminderDedupTest) and
 * `RecordingTurnBackend` (AutomaticCompactionModelSummaryTest): all three live in
 * this namespace and a duplicate declaration is a fatal error in a full-suite run.
 */
final class HookWireRecorder implements Backend, ReportsContextWindow
{
    /** @var list<array<int, Message>> */
    private array $seen = [];

    public function __construct(private readonly int $window) {}

    public function contextWindow(): int
    {
        return $this->window;
    }

    public function calls(): int
    {
        return count($this->seen);
    }

    /** @return array<int, Message> */
    public function lastHistory(): array
    {
        return $this->seen[count($this->seen) - 1] ?? [];
    }

    public function complete(array $history, ?callable $onToken = null, ?callable $onEvent = null): Message
    {
        $this->seen[] = $history;

        return Message::assistant('ok');
    }

    public function completeAsync(
        array $history,
        ?callable $onToken = null,
        ?\SugarCraft\Crush\Backend\CancellationToken $cancellation = null,
        ?callable $onEvent = null,
    ): \React\Promise\PromiseInterface {
        $this->seen[] = $history;

        return \React\Promise\resolve(Message::assistant('ok'));
    }
}

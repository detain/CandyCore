<?php

declare(strict_types=1);

namespace SugarCraft\Crush\Tests\Commands;

use PHPUnit\Framework\TestCase;
use SugarCraft\Core\KeyType;
use SugarCraft\Core\Msg\KeyMsg;
use SugarCraft\Crush\Backend\EchoBackend;
use SugarCraft\Crush\Chat;
use SugarCraft\Crush\Context\MemoryBlock;
use SugarCraft\Crush\Memory\MemoryStore;
use SugarCraft\Crush\Message;
use SugarCraft\Crush\Tests\Support\HomeSandboxTrait;

/**
 * `/memory import claude|opencode` — the P7.S6 wiring of
 * {@see \SugarCraft\Crush\Memory\ForeignMemoryImporter} behind the chat command.
 *
 * Everything is driven as a real submitted draft through
 * `Chat::update(new KeyMsg(KeyType::Enter))` against a temp-dir real
 * `MemoryStore` (the {@see SlashDispatchTest} header idiom): the thing under
 * test is the command's three guards — target parsing, the
 * `.imported-{target}` sentinel, and the headroom clamp under
 * `MemoryBlock::MAX_ENTRIES` counted against the scope the importer ACTUALLY
 * writes — and none of them is visible from a unit test of the importer.
 *
 * MEASURED and pinned here, not assumed: `importClaudeCode()`/`importOpencode()`
 * write `MemoryScope::Local`, which `MemoryStore::normalizeScope()` persists
 * under the string `'agent'`; `MemoryBlock::capture()` on the other hand lists
 * `MemoryScope::Project`. Both facts have assertions below, because the
 * headroom math and the "beyond 12 renders omitted" behaviour are different
 * claims about different scopes and this plan has been burned by conflating
 * exactly that.
 */
final class MemoryImportCommandTest extends TestCase
{
    use HomeSandboxTrait;

    private string $sandbox = '';
    private string $storeDir = '';
    private string $projectRoot = '';
    private MemoryStore $store;
    private string $origErrorLog = '';

    protected function setUp(): void
    {
        $this->sandbox = sys_get_temp_dir() . '/crush-memimport-' . bin2hex(random_bytes(6));
        $this->useHomeSandbox($this->sandbox . '/home');
        $this->storeDir = $this->sandbox . '/store';
        $this->projectRoot = $this->sandbox . '/project';
        mkdir($this->storeDir, 0700, true);
        mkdir($this->projectRoot, 0700, true);

        // Keep any importer skip-notice out of the suite's stderr.
        $this->origErrorLog = (string) ini_get('error_log');
        ini_set('error_log', $this->sandbox . '/error.log');

        $this->store = new MemoryStore($this->storeDir);
    }

    protected function tearDown(): void
    {
        ini_set('error_log', $this->origErrorLog);
        $this->restoreHomeSandbox();
        if (is_dir($this->sandbox)) {
            exec('rm -rf ' . escapeshellarg($this->sandbox));
        }
    }

    private function chat(string $draft): Chat
    {
        return (new Chat(
            history: [Message::user('hello'), Message::assistant('hi')],
            inputBuf: $draft,
            backend: new EchoBackend(),
            memoryStore: $this->store,
            projectRoot: $this->projectRoot,
        ))->withSize(100, 30);
    }

    /** Submit $draft and hand back the assistant reply text. */
    private function reply(string $draft): string
    {
        [$next] = $this->chat($draft)->update(new KeyMsg(KeyType::Enter));
        $this->assertCount(4, $next->history, 'a memory command appends exactly the command and its reply');

        return (string) $next->history[3]->content;
    }

    /** @return list<string> filenames to place under `.opencode/memory` */
    private function seedOpencodeMemory(string ...$filenames): void
    {
        $dir = $this->projectRoot . '/.opencode/memory';
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
        foreach ($filenames as $name) {
            file_put_contents($dir . '/' . $name, "body of {$name}\n");
        }
    }

    /** Put $count entries into the scope the importer writes (`agent`). */
    private function seedAgentEntries(int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $this->store->add("preset agent note {$i}", 'agent');
        }
    }

    private function sentinel(string $target): string
    {
        return $this->projectRoot . '/.sugar-crush/memory/.imported-' . $target;
    }

    // ── 1. target parsing ────────────────────────────────────────────────────

    public function testBareAndUnknownTargetsAnswerWithHelp(): void
    {
        $bare = $this->reply('/memory import');
        $this->assertStringContainsString('Usage: /memory import claude|opencode', $bare);
        $this->assertStringContainsString('**Available /memory commands:**', $bare);

        $unknown = $this->reply('/memory import gemini');
        $this->assertStringContainsString("Unknown import target 'gemini'", $unknown);
        $this->assertStringContainsString('**Available /memory commands:**', $unknown);

        $this->assertSame([], $this->store->list('agent'), 'a refused target must not touch the store');
        $this->assertFileDoesNotExist($this->sentinel('gemini'));
    }

    // ── 2. happy paths ───────────────────────────────────────────────────────

    public function testOpencodeHappyPathWritesAgentScopeWithTag(): void
    {
        $this->seedOpencodeMemory('build-notes.md', 'deploy-notes.md');

        $reply = $this->reply('/memory import opencode');

        $this->assertStringContainsString('**Imported 2**', $reply);
        $entries = $this->store->list('agent');
        $this->assertCount(2, $entries);
        foreach ($entries as $entry) {
            $this->assertSame(['source:opencode'], $entry->tags());
        }
        $contents = array_map(static fn ($e): string => $e->content(), $entries);
        $this->assertContains("# build-notes\n\nbody of build-notes.md", $contents);
    }

    public function testClaudeHappyPathImportsViaHomeDirectory(): void
    {
        // Chat has no seam to inject $claudeHome, so the claude tier is driven
        // the way production drives it: HOME points at the sandboxed fixture.
        $slug = '-' . ltrim(str_replace('/', '-', $this->projectRoot), '-');
        $dir = $this->sandbox . '/home/.claude/projects/' . $slug . '/memory';
        mkdir($dir, 0700, true);
        file_put_contents(
            $dir . '/cadence.md',
            "---\ndescription: Ship-as-you-go cadence\n---\nOne PR per change-set.\n"
        );

        $reply = $this->reply('/memory import claude');

        $this->assertStringContainsString('**Imported 1**', $reply);
        $entries = $this->store->list('agent');
        $this->assertCount(1, $entries);
        $this->assertSame("Ship-as-you-go cadence\n\nOne PR per change-set.", $entries[0]->content());
        $this->assertSame(['source:claude'], $entries[0]->tags());
    }

    // ── 3. headroom clamp (the plan's hard constraint) ───────────────────────

    public function testOverCapImportWritesOnlyTheHeadroomAndHintsRemainder(): void
    {
        $this->seedAgentEntries(10);
        $this->seedOpencodeMemory('a.md', 'b.md', 'c.md', 'd.md', 'e.md');

        $reply = $this->reply('/memory import opencode');

        $this->assertStringContainsString('**Imported 2**', $reply, 'headroom 12-10=2, not 5');
        $this->assertCount(12, $this->store->list('agent'));
        $this->assertStringContainsString('Stopped at the headroom', $reply);
        $this->assertStringContainsString('unimported source files remain', $reply);
        $this->assertFileExists($this->sentinel('opencode'), 'a partial import is still an import');
    }

    public function testStoreFullRefusesImportAndWritesNoSentinel(): void
    {
        $this->seedAgentEntries(MemoryBlock::MAX_ENTRIES);
        $this->seedOpencodeMemory('a.md', 'b.md');

        $reply = $this->reply('/memory import opencode');

        $this->assertStringContainsString('Nothing imported', $reply);
        $this->assertStringContainsString('already holds 12', $reply);
        $this->assertStringContainsString('/memory list agent', $reply);
        $this->assertCount(MemoryBlock::MAX_ENTRIES, $this->store->list('agent'));
        $this->assertFileDoesNotExist($this->sentinel('opencode'), 'nothing was imported, so the one-shot must not burn itself');
    }

    // ── 4. sentinel ──────────────────────────────────────────────────────────

    public function testSentinelIsWrittenOnSuccessAndBlocksReImport(): void
    {
        $this->seedOpencodeMemory('one.md');

        $reply = $this->reply('/memory import opencode');
        $this->assertStringContainsString('**Imported 1**', $reply);

        $sentinel = $this->sentinel('opencode');
        $this->assertFileExists($sentinel);
        $this->assertStringContainsString('1 opencode memories imported by /memory import', (string) file_get_contents($sentinel));

        $second = $this->reply('/memory import opencode');
        $this->assertStringContainsString('Already imported (sentinel `', $second);
        $this->assertStringContainsString('.imported-opencode`', $second);
        $this->assertCount(1, $this->store->list('agent'), 'the sentinel short-circuits before the importer runs');
    }

    public function testEmptyImportWritesNoSentinel(): void
    {
        // Headroom exists, sentinel absent, but there is nothing to import:
        // burning the one-shot guard here would block the day the user DOES
        // have foreign memory files.
        $reply = $this->reply('/memory import opencode');

        $this->assertStringContainsString('no readable `opencode` memory files', $reply);
        $this->assertFileDoesNotExist($this->sentinel('opencode'));
    }

    // ── 5. refusals surface in the response ──────────────────────────────────

    public function testSymlinkEscapeRefusalIsSurfacedAndNothingImported(): void
    {
        $outside = $this->sandbox . '/outside';
        mkdir($outside, 0700, true);
        file_put_contents($outside . '/evil.md', "not this project's memory\n");
        mkdir($this->projectRoot . '/.opencode', 0700, true);
        symlink($outside, $this->projectRoot . '/.opencode/memory');

        $reply = $this->reply('/memory import opencode');

        $this->assertStringContainsString('**Refused directories:**', $reply);
        $this->assertStringContainsString('Nothing imported', $reply);
        $this->assertStringContainsString($this->projectRoot . '/.opencode/memory', $reply, 'the refusal names the directory it refused');
        $this->assertSame([], $this->store->list('agent'));
        $this->assertFileDoesNotExist($this->sentinel('opencode'));
    }

    // ── 6. help roster ───────────────────────────────────────────────────────

    public function testHelpNowListsTheImportVerb(): void
    {
        $reply = $this->reply('/memory');

        $this->assertStringContainsString('/memory import claude|opencode', $reply);
    }

    // ── 7. measured render-cap interplay ─────────────────────────────────────

    public function testImportedEntriesCannotOverflowAndTheRenderCapIsPinnedAsMeasured(): void
    {
        $this->seedAgentEntries(MemoryBlock::MAX_ENTRIES - 1);
        $this->seedOpencodeMemory('x.md', 'y.md', 'z.md');

        $this->reply('/memory import opencode');

        // Pin 1: the command path cannot push the `agent` scope past the cap —
        // the headroom clamp is what makes the omission below unreachable here.
        $this->assertCount(MemoryBlock::MAX_ENTRIES, $this->store->list('agent'));

        // Pin 2 (MEASURED, premise §5 + R-2): MemoryBlock::capture() lists
        // MemoryScope::Project, so agent-scope imports never enter the block —
        // the crowding risk the clamp answers is the scope cap as the store's
        // one shared bound, and this pins the two are different scopes.
        $this->assertSame([], MemoryBlock::capture($this->store)->entries());

        // Pin 3: beyond 12 same-scope entries ARE silently omitted at render
        // time — only a trailing-count notice marks them, which is exactly the
        // silent crowding the importer-side clamp exists to prevent. WHICH
        // note drops is not assertable: same-second modifiedAt ties break on
        // the random UUID id (capture()'s documented comparator), so the pin
        // is on the COUNT — twelve lines rendered, one reported omitted.
        for ($i = 1; $i <= MemoryBlock::MAX_ENTRIES + 1; $i++) {
            $this->store->add(sprintf('project note %02d', $i), 'project');
        }
        $block = MemoryBlock::capture($this->store)->render();
        $this->assertSame(
            MemoryBlock::MAX_ENTRIES,
            substr_count($block, '- [pattern] project note '),
            'render stops at the entry cap regardless of which note lost the id tiebreak'
        );
        $this->assertStringContainsString('1 further note(s) were omitted by those limits.', $block);
    }
}

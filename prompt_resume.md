# prompt_resume.md — the entry point for the prompt-architecture plan

> **This file has two jobs, and which one it is doing depends on when you read it.**
>
> 1. **Before the plan starts** it is a **start prompt**: hand it to a fresh agent with no prior
>    conversation and the plan begins correctly.
> 2. **After the plan starts — which is where it is now** — it is **rewritten after every step** so
>    that it becomes a **resume prompt**: hand it to a fresh agent with no prior conversation and the
>    plan picks up from exactly where it is and runs to the end. That is this file's job today, and
>    §0 is the instruction that makes it run to the END rather than to the next question.
>
> The rewrite instructions are in §R at the bottom. They are part of the file on purpose — whoever
> rewrites it is reading it.

**Current state: PHASE 6 CLOSED - 42 of 65 steps done. Phase 6's close review PASSED on cycle 1 (fresh read-only reviewer `courageous-silver-woodpecker`, verdict **PHASE 6 MAY CLOSE**, 0 BLOCKER / 0 MAJOR / 1 MINOR / 3 NIT; MINOR-1 fixed in this very bookkeeping pass). Six of seven steps merged (P6.S1 the trigger union, P6.S2 the rules tier, P6.S2b the harness-injected roster channel, P6.S3 the named toggleable rule packs + the /rules command, P6.S4 the `disabledRules` config surface, and P6.S5a one `paths:` glob dialect - merged `52de996fe` via the `--no-ff` merge `505734f9f`); the seventh, P6.S5b, is a BLOCKED user-decision step, so the phase closes on its review per §1.7, not on S5b. There is NO P6.S6 - the phantom was corrected 2026-09-05 and Phase 6 closed with its standing PHASE REVIEW, not a step. THE PLAN HAS 65 STEP HEADINGS SINCE THE P6.S5 SPLIT, NOT 64. **NEXT = OPEN PHASE 7 at P7.S1 (`HookResult::additionalContext` + stop `HookRegistry::executeHooks()` discarding it - plan :2520; touches only `src/Hooks/HookResult.php` + `src/Hooks/HookRegistry.php` + their two tests; NO prompt golden, NO collision row, so P7.S1 needs NO §5 re-check). There is no P6.S6 and NO buildable P6.S5x left, because P6.S5b is BLOCKED on the §1.10 user decision carried verbatim in §8 - do not brief, build or schedule it as a normal step.** NEW FLOOR for every future figure: **Tests 10937 / Assertions 168399 / Skipped 2 / EXIT 0**, gated at `714bea7ec` in a FRESH detached gate worktree and describing master by the belt (`git diff 714bea7ec 505734f9f -- sugar-crush/` = 0; `52de996fe` is a message-only amend of `714bea7ec`, tree byte-identical; `505734f9f` is the `--no-ff` merge of `52de996fe`); the prior P6.S4 floor `10923/167931` at `608914072` and everything older are SUPERSEDED and tree-named. **BOTH GOLDENS UNMOVED through P6.S5a** - system `f09f37366a1925565dcc7725f659ff41` (7,829 B; last MOVED at P6.S2 `aff501a35` BY DESIGN) + agent `ef0326dd38535aaa2f1d715919bff26e` (1,060 B; unmoved since `405252a41`); fixture path is `sugar-crush/tests/fixtures/prompt/`, not `fixtures/`. **PUSH REMAINS USER-GATED** - re-derive with `git -C /home/sites/sugarcraft rev-list --count origin/master..master` (measured **16** after this Phase-6-close bookkeeping commit; NEVER trust a count in this file). The 7 `.opencode/*` dirties are now COMMITTED - consumed by `1cf242f26`, a caliber/config pass touching ZERO sugar-crush paths - so master porcelain is EMPTY; the user decision on their DISPOSITION still stands (§8), but they are no longer dirty. No worktree and no branch of this plan is live: `git worktree list` prints exactly `/home/sites/sugarcraft [master]`, `git branch --list 'prompt/*'` prints nothing (the P6.S5a step worktree AND its disposable gate worktree are both removed). Section 8 carries the full state.**

## 0. STANDING ORDER — run to completion

**The user has asked for this file to be handed to a fresh agent that then runs the plan to the end.
That is your instruction. Do not stop at a phase boundary to ask whether to continue.**

Concretely:

1. Work the queue in §8 in order. When Phase 3's close review passes, **immediately open Phase 4**
   and keep going. Same at every later boundary. `prompt_plan.md` has twelve phases (0-11) and 65
   steps; Phases 0-5 are closed, Phase 6 is in progress, Phases 7-11 remain:
   **6** The rules tier and the trigger union · **7** Wire the dormant seams ·
   **8** Rebuild the compaction prompt · **9** Tool descriptions as prompt ·
   **10** Cache breakpoints · **11** Docs, sweep, final audit.
2. **Rewrite this file and append to `prompt_worklog.md` after EVERY step and EVERY phase close.**
   Not at the end of a session — after each one. If you are running out of context, doing this is the
   last and highest-value thing you do (§R).
3. **Decide the ordinary things yourself.** Batch composition, merge order, whether a finding is worth
   a fix step, whether an agent's work meets the bar, whether to schedule a follow-up as its own step
   or fold it into a queued one. You are the orchestrator; that is the job.

**STOP AND ASK only for these.** Everything else, keep moving.

- A **§1.10 dormant-code escalation** — you or an agent would have to REMOVE unfinished, dormant,
  unwired or unreachable code, or the only way forward is a redesign. Record it verbatim under
  `Awaiting user decision:` with `file:line`, what calls it (or that nothing does), and the options.
  **Escalating is a COMPLETED step, not a failed one — record it and MOVE ON to the next step.** Never
  block the queue on an unanswered escalation.
- A **genuine blocker**: the work cannot proceed without an answer, and no assumption is safe.
- Anything that would need a **`git push`**, or would touch `/home/sites/crush-lane-{a,b,c}` or
  `docs/plans/crush_code_*.md` / `left_steps.md`.
- **Before starting Phase 5 or Phase 6**, check §5's collision table. The `src/` file-count census
  reason is RESOLVED and no longer serialises those phases, but per-file collisions with the other
  plan are still live. If a lane has a round in flight on a file a step declares, ask the supervisor
  rather than reading the lane worktrees — and in the meantime run the steps that do not touch it.

**Three things await the user right now and NONE of them blocks the queue** — they are carried verbatim
under `Awaiting user decision:` in §8: (1) the 7 pre-existing dirty `.opencode/*` files (never touched by
this lane), (2) **PUSH AUTHORIZATION** (master is unpushed again; re-derive the count, never trust a number
in this file), and (3) **Gemini function calling** — that decision is still UNANSWERED, and it is
un-blocked only because the supervisor's 2026-09-04 reply SCHEDULED follow-up step F7 (the Gemini tools
shaper) instead of answering it. Carry all three forward, every rewrite, until the user answers. Do not
decide any of them yourself and do not let any of them hold up the queue.

---

## 1. Who you are and what you are doing

You are the **orchestrator** for the sugar-crush prompt-architecture plan, running in
`/home/sites/sugarcraft` on branch `master`.

You do not write production code. You spawn agents, verify their work against test output you run
yourself, merge it, commit it, and maintain two bookkeeping files. Everything that touches
`sugar-crush/src/` or `sugar-crush/tests/` goes through a spawned step agent, every time, including
changes that feel too small to be worth spawning for.

## 2. Read these first, in this order

1. **`/home/sites/sugarcraft/prompt_plan.md`** — your complete operating manual. Read it in full
   before doing anything else. §1 is the execution contract, §2 is concurrency, §3 is bookkeeping,
   §4 onward are the phases, §16 is the lessons every agent you spawn must be given, §17 is the
   invariants, §18 is what not to build. Inside §1, **§1.10 (removal is not an available outcome)**
   and **§1.11 (what counts as a test)** go to every agent you spawn, alongside §16 and §17.
2. **`/home/sites/sugarcraft/prompt_worklog.md`** — the record. It holds the conventions, the
   required entry format, one worked example, and every step entry so far. Read the format; you will
   be writing in it after every step.
3. **`/home/sites/sugarcraft/prompt_expand.md`** — the 4,063-line research dossier the plan
   executes. Do **not** read it end to end now. Each step names the sections its agent must read;
   read those sections when you brief that agent, and read §0 and §1 now so you understand the lead
   finding.
4. `CLAUDE.md`, `AGENTS.md`, `CONTRIBUTING.md` — repo conventions.

## 3. What has been built so far

Phases 0-6 are closed (seven phases). Phase 6 is fully merged (six of seven steps - P6.S1 the trigger union, P6.S2 the
rules tier, P6.S2b the harness-injected roster channel, P6.S3 the named toggleable rule packs + the /rules command,
P6.S4 the `disabledRules` config surface, P6.S5a one `paths:` glob dialect) AND closed by its standing cycle-1 phase
review (verdict PHASE 6 MAY CLOSE). The seventh step, P6.S5b, is BLOCKED on a user decision, not a buildable one.
There is no P6.S6. Phases 7-11 remain; the NEXT action is to open Phase 7 at P7.S1. Things a fresh agent needs about the **current** shape of the code:

1. **The prompt reaches the model.** `Runtime::buildSystemPrompt()` (`sugar-crush/src/Runtime.php`)
   assembles seven layers and `Runtime::run()` puts them on `CompleteRequest::$systemPrompt`. SIX
   of the seven providers transmit it on **both** `complete()` and `completeStream()` — the
   EchoProvider is EXEMPT and the exemption is pinned by assertion.
   `tests/Providers/SystemPromptTransmissionMatrixTest.php` pins the wire slot per protocol against
   a roster derived from `src/Providers/`.    Measured end to end: **assembled == golden == wire,
   7,829 B** post-P6.S2 (7,314 B post-P5.S6; 5,559 B post-P5.S4; 5,176 B before the verify-before-done clause), `messages[0].role = 'system'`.
   **P6.S2b added a roster tag with NO emitter, so the prompt did not grow** - 7,829 B is unchanged through it, and **P6.S3 is likewise a ZERO-movement step** - the `/rules` toggle renders through a temp HOME, not the golden, so the assembled prompt is still 7,829 B through P6.S3.
2. **Vertex has THREE arms.** `P1.audit-fix-1` (`03d8fed37`) hoisted the prompt into the Google
   `instances[0].context` slot; `P1.audit-fix-3` (`e0d00b6db`) built a real Gemini
   `:generateContent` arm with `systemInstruction` and streaming. Routing is by model FAMILY, not
   publisher; the legacy `instances` arm stays for `chat-bison`. **Gemini still cannot call tools** —
   see `Awaiting user decision`.
3. **The prompt is deterministic and golden-pinned.** Clock, platform and cwd are injectable;
   `golden-system-prompt.txt` (md5 `f09f3736…`, 7,829 B) pins the assembly byte-for-byte and
   `golden-agent-prompt.txt` (`ef0326dd…`) pins `Agent::systemPrompt()`. The system golden **MOVED at
   P5.S4 (`c5ba741a9`), P5.S5 (`d25be7540`), P5.S6 (`afebe1a39`) and P6.S2 (`aff501a35`) - **every one of
   those moves BY DESIGN, IS that step's deliverable** (pre-P5.S4 it was `32ea749d…`); the agent golden is **unmoved since P3.S5's merge point (`405252a41`)**. **P6.S2b and P6.S3 both MOVED NEITHER golden** - each a zero-movement step; P6.S3's blobs are identical base->tip and its toggle effect is proven by live temp-HOME renders, not a golden move.
   **State the window a no-move claim covers** — see §4's correction for what happens when you do not.
4. **`<env>` IS LAST IN BOTH ASSEMBLERS.** P3.S1 moved it from Runtime layer 2 to layer 7. The two
   assemblers are still deliberately separate, but on a **layer-set** argument (seven layers versus
   two), NOT an ordering one. Since `P3.audit-fix-3` BOTH assemblers share **ONE project-root
   resolution** — the `--root` flag orients the `<env>` block of the live Agent/WorkflowEngine path,
   not just Runtime's (a close-review finding: the seam no single step's file list could see).
5. **The write-signal is WIRED on the Runtime path, and MEASURED-BUT-NOT-WIRED on the Agent path.**
   P3.S5 (`405252a41`) marks the `Runtime` from `EngineBackend`'s per-step loop. P3.S6 (`f958ba8e6`)
   established the Agent path's per-step seam **is real and live** — in `Workflows/WorkflowEngine.php`,
   outside its declared scope — and pinned the cost instead: one render = 5 git subprocesses (3
   suppressed), a K-stage workflow = 5×K, one `ProcessExecutor` dispatch = 10 because it renders
   twice, and the stages see ONE DISTINCT PROMPT. A §18 row records this as **escalated, not
   waived**, and an exact-list assertion over `AgentResult::__construct` reds the day a tool-call
   field is added — the change that unblocks it.
   **Its sibling write-primitive scanner fails CLOSED — Eighteen-plus defeats, five reviewers.** Beyond the
   `T_NAME_RELATIVE` and alias-subtraction fixes, `P3.audit-fix-3` read the CONSTRUCTION CHANNEL:
   anon classes and same-file named subclasses (with aliased parents), `self`/`static`/`parent` in
   every scope, and `class_alias()` in every string spelling — indented heredoc/nowdoc terminators
   and escaped-backslash decode included. **Count the ledger from `tests/RuntimeTest.php`'s own
   pins — never trust a brief's number.** An unknown spelling costs a FALSE POSITIVE, never a
   silent miss — except the declared residuals, EACH PINNED: cross-file trait users,
   cross-file/imported parents, NAMESPACED extends parents, same-file CONSTANT and computed
   `class_alias`, roster-side self-in-subclass, and cross-file LITERAL `class_alias` pairings — the
   last declared at close-review cycle 3 (MEASURED LATENT; `class_alias` count in `src/` is 0).
6. **The tree-wide census set is DERIVED, not hand-maintained.**
   `sugar-crush/tests/TreeWideGuardRosterTest.php` walks 440 test files and derives **67** guards
   that scan `src/` or `tests/` wholesale, `unaccounted 0`. `P3.audit-fix-3` extended the
   classifier's alias arms (imported-`as`, `class_alias` literals) — the five derivation numbers
   are **UNCHANGED**: the new arms are declared-latent, zero live population. `prompt_plan.md`
   §1.2 action 7b points at it.
7. **Usage has real buckets and the providers fill them honestly.** `src/Usage.php` carries
    `inputTokens`/`outputTokens`/`cacheReadTokens`/`cacheCreationTokens` (each `?int`, missing != 0),
    `promptTokens() = cacheRead + cacheCreation + input` (output deliberately EXCLUDED — identity and
    reason pinned), and the fork socket round-trips all six keys with null-vs-zero intact. Every
    provider routes wire usage through public parse seams; per-provider cache-field EVIDENCE (live
    probe / vendored DTO / UNVERIFIED-documented) lives in the worklog — where a protocol reports no
    cache field on the real deployment (Sglang/Custom, probed 2026-09-02) the parser invents nothing
    and a test pins the absence. The P4.S1 `UsageTest` tripwires pass by REAL remediation: an
    expected provider-set + exact-count locators red the day any provider's split regresses.
8. **Cache health renders in the STATUS LINE only — and the buckets still have NO response-path
   consumer.** `Renderer::cacheIndicator()`/`formatCacheAge()` add a fourth fitted piece below spend
   — `round(cacheRead/promptTokens()*100)`% + age of the newest reported usage entry, TTL printed
   never coloured (design §4.16). The hard constraint — zero transcript messages — is pinned by a
   12-tick armed-loop test carrying its own known-positive control, per-tick transcript-signature
   asserts and an in-test painted-frame needle scan; a planted `/context`-style append reddens the
   signature claim ITSELF, first red at tick 0. The widen-CompleteResponse seam is recorded-open in
   §8's travel ledger: parse + total/cost routing is live, end-to-end cache observability ships when
   that seam lands. The status line is the one shipped consumer.
9. **E18: one oversized exchange no longer starves the queue.** `ContextCompactor::truncateOversizedExchange()`
     (:229-305) truncates ONLY a message whose own estimate reaches the blocking tier — aggregate overflow still
     returns byte-identical; `Chat::intraExchangeTruncation()` rescues BOTH blocking sites (`submit()` and
     `applyModelCompaction()`), rebuilding via `messageWithContent()` — an 11-field splice-copy pinned by a
     known-answer test (a gutted copy re-stamping `createdAt` reddens it). Measured before/after on a real
     800,040-char exchange: rising refusals [200520..201032] became [200520, 93126, 93149, 93172, 93195] with
     every turn dispatched; the +23/turn honest growth is disclosed, asserted as never-above-first-reading. The
     95% tier constant untouched; goldens unmoved. Residual recorded in the docblock: a drafted (not sent) giant
     can still rescue ~108,113 estimated tokens into a 100,000 window — draft-aware bound is a follow-up.

10. **PromptSection + PromptFence are LIVE (P5.S1-S6); maxims + the preamble trio are LIVE.** `Runtime::buildSystemPrompt()` renders an ordered `PromptSection` list through ONE assembler; P5.S2 migrated the env/memory/repoMap snapshot OBJECTS onto it (wrap-not-copy, memoization identity-pinned; the assembler's `render()===''` skip is the sole suppression). P5.S3 added `src/Context/PromptFence.php` — the ONE fence-escape authority (byte-oriented, 5-tag roster, idempotent, fail-loud) — and routes ALL FOUR production fences' user-/repo-derived bytes through it: `<project-memory>`, `<env>` (incl BOTH carried HIGH/SECURITY vectors: the unstaged-edit diff-body forge AND the raw branch-name interpolation), `<repo-map>`, and the inline `<project-instructions>` splice. A6 characterization pins now assert FIXED polarity. The 255-per-component ref argument is dead: raw git reads are capped at 255 + escaped, pinned by a real 359-byte multi-segment ref. Goldens UNMOVED since P5.S1 - escape transparent on clean content. P5.S4 then ADDED the verify-before-done clause, FOLDED INTO the `# Tool use` paragraph (a 5th heading would land past BASE_END_MARKER, outside the policed slice); the system golden moved with it (5,559 B) - by design. P5.S5 then ADDED `src/Context/Sections/MaximsSection.php` - SEVEN core maxims (the plan's #4/#5 dropped at brief stage: base heredoc + S4 clause already own them) as an UNFENCED Static final-readonly section wired at systemPromptSections() index [1] (base -> `## Maxims` -> repo-map -> ... -> <env> LAST); register guard pins IMPORTANT:/CRITICAL:/You MUST/digit+lines at zero with planted-violation proofs; the system golden moved again with it (6,750 B) - by design. P5.S6 then shipped the AMENDED provenance-fence scope (orchestrator ruling 2026-09-05 after the premise check found G1/G2/G7 already built): a one-line project-authority preamble (280 B, Runtime const) inside every `<project-instructions>` fence at the sole construction site - the three provenance preambles now speak with one voice: `<repo-map>`, `<project-memory>`, `<project-instructions>`; + 2 guard tests / 38 assertions (forge plant, per-tag roster-keyed counts, in-test positive control, red-on-revert 3/3 incl. the 2 P5.S3 escape pins still biting on escape deletion). The `<harness-injected>` fence (roster widening 5->6/7 + layer-mapping decision) and the `<user-rules>` fence + two-framings provenance split are DEFERRED to P6.S2 - fences ship WITH their feeder tier. The system golden moved again (7,314 B) - PURE INSERTION, preamble strictly inside the fences, fence counts unchanged - by design. **PHASE 5 CLOSED 6/6.**

11. **The trigger union is LIVE in `sugar-crush/src/Context/Triggers/` and UNWIRED by design (P6.S1, `88fa18f77`).** Four classes, no others: `Trigger` is a **zero-method interface** - class identity is the discriminator and there is deliberately NO `kind()` (zero precedent in this codebase; the intent family would have to lie). `KeywordTrigger` matches with `/\b…\b/iu` and carries an **instance-scoped lifetime ledger** keyed on the lowercased word, merging one-way via `mergeFiredFrom()`; the whole step exists because the historical unanchored matcher let `think` fire inside `rethinking`. `PathTrigger` SHIPPED its own anchored byte-wise glob→PCRE compiler (`*`/`?` did not cross `/`) but since **P6.S5a (item 15)** its `pattern()` is a shim delegating to the shared `src/Util/PathGlob` compiler - the segment-bound reading is GONE and that widening IS the S5a deliverable; it remains documented **A MATCHER, NOT A GATEKEEPER** - an out-of-root path matching is by design and containment is named as **P6.S2's** job. `IntentTrigger` truncates by **character** (`mb_substr`) so the cut is mb-safe. All four are `final`, immutable, zero-filesystem. Nothing outside their directory references the namespace yet; `Skill::matchesPrompt()` (`src/Skills/Skill.php:90`) remains the crude predecessor and **P7.S4 owns rewiring it**. Both goldens are UNMOVED by this step precisely because nothing renders these classes yet.

12. **The rules tier is LIVE (P6.S2).** `src/Context/Rule.php` + `RuleLoader.php` implement three tiers (user -> project -> root; tier is PATH-derived, never content-derived), containment with refusals RECORDED loader-local (`sugar-crush/docs/ENVIRONMENT.md:52` states nothing drains them at launch), a pre-read depth cap, MAX_FILES bounding READS, MAX_FILE_BYTES = 65536 stat-ed before reading, and a realpath-then-lowercase double dedup (pass 2 cited in-source as SPEC, not an upstream mirror). Runtime splices them with provenance separated: user tier inside `<user-rules>` behind USER_RULES_AUTHORITY_PREAMBLE, project+root inside `<project-instructions>`. PromptFence's roster is now SEVEN tags: P6.S2 made it SIX with `user-rules`, and P6.S2b added `harness-injected` as the seventh **with no emitter at all** — defang-only, on the `system-reminder` precedent, so a forged harness opener/closer from any tier is neutralised while no layer claims harness authority. The P6.S1 triggers are CONSUMED by `Rule::buildTriggers()` but NOT APPLIED at the splice - a `paths:`-scoped rule still renders globally, pinned as a §18 row. Four red-capable forgery guards pin the escape across tiers (user, project, root, cross-tier); deleting ANY production escape() call now reddens a test, which cycle 2 proved it did not before. **P6.S3 (see item 13) builds on this exact tier without adding a fourth tier, a new fence, or a single `ContainedPath` call site or read sink.**

13. **Rulebooks are LIVE and TOGGLEABLE (P6.S3, `017a691ae`)** - named, toggleable rule packs at the operator's rulebooks directory with a `/rules` command, and a **golden-ZERO-movement** step. Packs load through the EXISTING `RuleLoader::loadFromDirectory()` on the EXISTING `user` tier at `$HOME/.sugar-crush` - **no fourth tier, no new fence, +0 `ContainedPath` call sites and +0 read sinks** (both ledgers measured UNMOVED at 37/15 and 64 rows/90 verdicts). Pack identity is `Rule::$key` (basename minus `.md`), so there is no second identity function; `Rule::new()` gained an appended optional `?string $key = null` and every existing call site still compiles. `RulesState` (`src/Context/RulesState.php`) is **deliberately MUTABLE and shared by reference** - two owners with no call chain between them (the `/rules` command writes, the backend reads per turn), precedent `SkillRegistry`/`AgentManager`/`TokenTracker`, cited in the class doc-block; AND-semantics evaluate AFTER realpath dedup and BEFORE the `enabled` gate, and `TOGGLEABLE_TIER = 'user'` means a session name can never subtract a repository-authored rule. §1.10 was met by **ADOPTION, not removal**: `Rule::withEnabled()` (zero prior production callers) is now consumed by `RulesState::effectiveRule()` (`RulesState.php:181`). **NIT-5 is discharged** - a dangling `*.md` symlink is now a RECORDED refusal with a reason, pinned for both rules directories. The toggle is **SESSION-scoped**: `RulesCommandTest::testTogglingAPackLeavesTheConfigFileByteIdentical` pins that a toggle leaves `config.json` byte-identical with `onConfigChange` count `=== 0`, so **persistence is a pinned non-goal owned by P6.S4**. **RULING REVISED 2026-09-05:** P6.S4 is now RULED as read-side `disabledRules` key registration (option (i)) - a hand-edited config value is honoured at launch, while the `/rules` toggle stays session-scoped and S3's byte-identical pin stands untouched; persisting the toggle through the guarded write door is DEFERRED to its own step (its three-census cost is priced in the plan P6.S4 note). The earlier "P6.S4 owns persistence" framing was mis-sourced from the premise check, not the research.

14. **`disabledRules` is a LIVE layered key (P6.S4, `608914072`)** - the config surface the rules tier was missing, and a **second consecutive golden-ZERO-movement step**. `LAYERED_KEYS` now has **twelve** members (`LayeredSettings.php:298-311`); `disabledRules` is deliberately **NOT** in `PROJECT_TIER_KEYS`, so `userTierOnlyKeys()` derives it and now returns **five** keys. `Bootstrap::chat()` seeds the previously-dormant `RulesState::new()` at `Bootstrap.php:1028` from the **MERGED** view (`$userConfig = self::readUserConfig()` at `:933` - NOT `rawUserConfig()`, which is `config.json` alone) through the pure static `Bootstrap::rulePacksToDisable()` (`:2840-2875`), which keeps only entries that are `is_string` **and** `trim() !== ''` behind an `is_array()` guard - the sibling `disabledSkills` read filters `is_string` alone and would hand `RulesState::new()` a blank or an int and **crash the launch** (`parsePack()` throws; `RulesState.php:192 <- :103 <- Bootstrap.php:1028`, proven by mutation). No `try`/`catch` anywhere, so the AssertionSwallowingCatch census stays unfed. **There is NO write door**: the `/rules` toggle stays session-scoped and S3's byte-identical-config pin stands, so persistence of a toggle is a DEFERRED step, not shipped behaviour. `merge()` is key-wise **WHOLE-VALUE with `config.json` on top** (`:599-605`), so the two user files can never both contribute to one key - a launch-seed fixture must make `settings.json` the SOLE supplier or it pins one route twice and the other zero times. `Rule::$key` is the path **relative to the tier directory minus `.md`** (`RuleLoader.php:685-691`), so a nested pack's toggle handle is `style/terse` (pinned for the first time in `RuleLoaderTest`); root `RULES.md` keeps its literal name **with** extension at `:449`, is tier `root`, and is therefore untoggleable. Accepted documented residual: a whitespace-padded name (`" focus "`) is **inert** (`parsePack()` returns untrimmed at `RulesState.php:199`, `in_array` is strict). The two README roster sentences this step found UNPOLICED are now policed by two derived guards in `TrustKeyDocumentationDriftTest` (set-equality over the backticked names + a `NUMBER_WORDS` 2..16 spelled-count map) - never hand-edit those sentences again.
15. **`paths:` matching now speaks ONE dialect (P6.S5a, step commit `52de996fe`, merge `505734f9f`)** - a golden-ZERO-movement step. `sugar-crush/src/Util/PathGlob.php` is the single compiler, implementing the **`SkillRegistry` fnmatch (non-narrowing) dialect**: `compile()` is master's `compilePathPattern()` moved VERBATIM and `matchCompiled()` returns the THIRD answer `null` on a PCRE refusal instead of collapsing it. BOTH production matchers route through it. `SkillRegistry::pathMatches()` - the change is a MOVE, its YES-set proven UNCHANGED by 363 patterns x 359 paths = **130,317** derived comparisons plus the frozen 2,484-cell grid (378 YES); `compilePathPattern()` survives as a documented delegating seam and `legacyPathMatch()` stays REACHABLE on the PCRE-failure path per §1.10. `PathTrigger::pattern()` - a delegating shim; its YES-set moved on **13 of 33** differential rows (**10 widen / 3 narrow - rows #11,#22,#23 - / 20 hold**), and the widening IS the deliverable (zero production readers today). `tests/Context/GlobDialectDifferentialTest.php` (13 tests) holds 5 frozen INDEPENDENT oracles, a corpus DERIVED from the repo's own globs, and pins the doc-block's corpus figure with a NON-CIRCULAR assertion (two fixed-literal regexes + presence guards + the shared memoized corpora). **NOTHING reaches prompt rendering** - rule `paths:` are still NOT APPLIED at the splice; that is P6.S5b, BLOCKED (see §8). Both goldens UNMOVED; the floor through this step is Tests 10937 / Assertions 168399.

## 4. How to resume

**Key figures below were measured by the orchestrator 2026-09-05 and each row names its sha. Re-run a check ONLY
if the sha it names has moved. Do not spend seven minutes re-measuring a suite this file already gives you. P6.S5a's
step commit (`52de996fe`) and its `--no-ff` merge (`505734f9f`) move the floor exactly once: the P6.S5a row below IS
the current measurement (belt: `git diff 714bea7ec 505734f9f -- sugar-crush/` prints 0 bytes - `52de996fe` is a
message-only amend of the gated tip `714bea7ec` with a byte-identical tree (both trees are object
`22f85538ba75d68ce11e18d594e96f6d49b0c84e`, re-measured at this bookkeeping pass), the merge adds no `sugar-crush/`
byte over the gated tip, and `1cf242f26` plus the bookkeeping passes above it touch no `sugar-crush/` path).**

**IN FLIGHT: NOTHING. PHASE 6 IS CLOSED.** P6.S5a merged at `505734f9f` (step commit `52de996fe`, gated at
`714bea7ec`); its branch and both worktrees are gone (`git worktree list` prints exactly
`/home/sites/sugarcraft [master]`; `git branch --list 'prompt/*'` prints nothing; the branch was deleted with safe
`-d`, so it was fully merged, and `worktree remove` needed no `--force`).** Master porcelain is EMPTY apart from
the three `prompt-*.md` files this close bookkeeping pass edits - the 7 pre-existing `.opencode/*` dirties were
consumed by `1cf242f26` (a caliber/config pass touching ZERO sugar-crush paths); the user decision on their
DISPOSITION still stands (§8), but there is nothing dirty to touch. **THE PHASE 6 CLOSE REVIEW HAS NOW PASSED** - a
cycle-1 review by fresh read-only reviewer `courageous-silver-woodpecker` returned **PHASE 6 MAY CLOSE** (0 BLOCKER /
0 MAJOR / 1 MINOR / 3 NIT); the single MINOR-1 (a stale `Rule.php` `withTriggers()` line-cite - the pre-S5a span - now corrected to `285-298`, caused by
S5a's net +8-line doc-block) is fixed in this very commit, and the three nits need no action. **NEXT = OPEN PHASE 7
at P7.S1** (plan :2520). There is NO buildable P6.S5x left: **P6.S5b - glob-scoped rules reaching the model - IS
BLOCKED on the §1.10 user decision** carried verbatim in §8; do not brief, build or schedule it as a normal step.
**There is no P6.S6**: the phantom was purged 2026-09-05 - it had propagated precisely because a resume is copied
forward instead of re-derived, the failure mode §R warns about. The §5 re-check is owed before **P6.S5b AND P7.S3**
(both want `src/Backend/EngineBackend.php`, a live collision row) and before **Phase 8** (`Chat.php` +
`ContextCompactor.php`); **P7.S1 itself touches only the Hooks subsystem and needs no re-check.**

### Verified this session — do NOT redo unless the sha moved

| Check | Result | Measured at |
|---|---|---|
| **MASTER full suite (CURRENT)** | **`Tests: 10937, Assertions: 168399, Failures: 0, Errors: 0, Skipped 2`, EXIT 0** - the orchestrator's INDEPENDENT gate in a **FRESH detached worktree at `714bea7ec`** (`cp -al` the vendor, NEVER `ln -s`, PSR-4 proved printing the gate worktree's OWN `src`, serial, cwd checkout root, `</dev/null`, `--colors=never`, one run, Time 07:12.079; box-quiet probe **0** before the run); the prediction `10937/168399` was written BEFORE the run and **HIT EXACTLY**; per-class `cmp` = **0 movers beyond the differential harness's own +1 assertion**, and the full-suite delta vs the P6.S4 floor (+14t/+468a) is fully attributed by the builder's per-class sweep (18 census movers summing EXACTLY, the only CASE mover beyond the new file's +12 tests being `BinSugarcrushWiringTest` +1 src-derived row); goldens UNMOVED, fixtures diff 0 lines; `MouseModalGuardTest` NOT a mover; describes master by the belt argument - `git diff 714bea7ec 505734f9f -- sugar-crush/` prints 0 bytes (`52de996fe` is a message-only amend of `714bea7ec`, tree byte-identical object `22f85538…`; `505734f9f` is the `--no-ff` merge of `52de996fe`), so no second seven-minute run was warranted | `505734f9f` (gated at `714bea7ec`) |
| **(SUPERSEDED by P6.S5a)** MASTER full suite (P6.S4 floor) | **`Tests: 10923, Assertions: 167931, Failures: 0, Errors: 0, Skipped 2`, EXIT 0** - gate ran uncontended at the P6.S4 final branch tip `96e6577a3`, serial, `</dev/null`, `--colors=never`, one run (7:04), box-quiet probe 0 before the run (a FOREIGN phpunit from another project contended only the solo runs and was left alone); the headline prediction was derived from the final tip's per-class solos BEFORE the run and **HIT EXACTLY** (the process law that P6.S1..S3 each missed); per-class `cmp.py` vs `/tmp/opencode/P6.S4-gate/tip.xml` (10919/167896) = **5 movers, remainder 0** - TrustKeyDocDrift +2t/+19a, RulesStateWiring +1t/+6a, RuleLoaderTest +1t/+5a, BootstrapLayeredSettings +1a, GlobFigureDrift +4a (derived census) - and `MouseModalGuardTest` did NOT move; described master by the belt argument - `git diff 96e6577a3 608914072 -- sugar-crush/` is EMPTY so no second run was warranted | `608914072` (gated at `96e6577a3`) |
| **(SUPERSEDED by P6.S4)** MASTER full suite (P6.S3 floor) | **`Tests: 10912, Assertions: 167830, Failures: 0, Errors: 0, Skipped 2`, EXIT 0** - gate ran at branch tip `e28a99b04`, serial, cwd worktree root, `</dev/null`, `--colors=never`, one run (6:47), box-quiet probe 0 before and after; vs the P6.S2b floor `10868/167297` the per-class diff reconciles to **remainder 0** with every mover attributable and `MouseModalGuardTest` did NOT move; the headline prediction 10913/167807 MISSED (the orchestrator's miss, three steps running - the worklog P6.S3 entry records the NEW PROCESS LAW), so the run is a **PASS ON RECONCILIATION**; describes master by the belt argument - `git diff e28a99b04 017a691ae -- sugar-crush/` is EMPTY so the branch-tip figure covers the `--no-ff` merge without a second run (the bookkeeping commits since touch no `sugar-crush/` path) | `017a691ae` |
| **(SUPERSEDED by P6.S3)** MASTER full suite (P6.S2b floor) | **`Tests: 10868, Assertions: 167297, Failures: 0, Errors: 0, Skipped 2`, EXIT 0** - gate ran at branch tip `730478ab2`, serial, `</dev/null`, cwd worktree root, one run, box-quiet probe 0; vs the P6.S2 floor `10866/167264` movers were EXACTLY 4 - `BaseSystemPromptTest` +21a/+1t and `PromptSectionTest` +8a/+1t (the step's two new guards) plus the two derived source-walking censuses `AssertionSwallowingCatchTest` +3 (the step's one new `try`) and `GlobFigureDriftTest` +1 (`PromptFence.php` paragraphs 23→24) - **remainder 0**; `MouseModalGuardTest` did NOT wobble; describes master by the belt argument - the merge tree IS the reviewed tip tree `fd6d771fc03d2a48b5a91d37e3f45365009c9c7d`, so `git diff 730478ab2 506ef5f5e -- sugar-crush/` prints 0 bytes and no second run was warranted (re-measured at this bookkeeping pass) | `506ef5f5e` |
| **(SUPERSEDED by P6.S2b)** MASTER full suite (P6.S2 floor) | **`Tests: 10866, Assertions: 167264, Failures: 0, Errors: 0, Skipped 2`, EXIT 0** - gate ran at branch tip `ca1470ff2` prediction-first and hit EXACT on every figure; same-tree `cmp.py` of the gate junit against the step agent's junit = ZERO movers; `MouseModalGuardTest` did NOT wobble; describes `aff501a35` by the belt argument - `git diff ca1470ff2 aff501a35 -- sugar-crush/` is EMPTY and both trees are object `4df7a5177767417f80839d5bac9dc6bc8b762779` (no second 7-minute run); vs the P6.S1 floor `10832 / 166702` exactly **+34 tests / +562 assertions**, fully attributed per-class in the worklog P6.S2 entry; no existing test weakened, skipped, renamed or deleted | `aff501a35` |
| **(SUPERSEDED by P6.S2)** MASTER full suite (P6.S1 floor - **the PRIOR floor** `10832/166702` at `88fa18f77`) | **`Tests: 10832, Assertions: 166702, Failures: 0, Errors: 0, Skipped 2`, EXIT 0** - gate ran at branch tip `7e312e84e` prediction-first and hit EXACT on every figure; `cmp.py` gate-vs-branch ZERO movers; vs the old floor exactly **+4 tests / +357 assertions**, fully explained by **seventeen** derived source-walking guards growing over four new `src/` files (per-guard split in the worklog P6.S1 entry: GlobFigureDrift +139, StderrEmitterCensus +64, EnvRosterDrift +36, BinSugarcrushWiring +4t/+24, DocumentParagraphs +16, SymbolCitationDrift +15, ProcessUniqueTempName +15, RuntimeNoticeSinkDelivery +10, WorktreeRemovalReporting +8, ChildWallClockBudget +7, NonBlockingVocabulary +5, ProcessExecutor +4, HomeDirectoryPathReaderInventory +4, AppSkillDispatch +4, AssertionSwallowingCatch +3, TreeWideGuardRoster +2, ReflectionLineSliceReaderCensus +1) - **remainder 0**; `MouseModalGuardTest` NOT a mover; no existing test weakened, skipped, renamed or deleted; describes `88fa18f77` because `git diff 7e312e84e 88fa18f77 -- sugar-crush/` is EMPTY and both trees are object `71873399fd52c10f659891f7a9093b55256d214b` (belt argument - no second 7-minute run) | `88fa18f77` |
| **(SUPERSEDED by P6.S1)** MASTER full suite (P5.S6 / Phase-5 floor) | **`Tests: 10795, Assertions: 166235, Failures: 0, Errors: 0, Skipped: 2`, EXIT 0** - gate ran at branch tip `afebe1a39` prediction-first and hit EXACT on EVERY figure (6:49, serial, cwd checkout root, `</dev/null`, probe 0; cmp gate-vs-lead ZERO movers; vs the S5 floor exactly the 3 permitted movers - BaseSystemPromptTest +38a/+2t, AssertionSwallowingCatchTest +6, GlobFigureDriftTest +4 - the +10 assertion growth is adaptive source-count census with ZERO literal updates = §17.1 decoupling as designed); described `826564bdf` by the belt argument - **belt `git diff afebe1a39 826564bdf -- sugar-crush/` = 0 bytes** (re-measured at the Phase-5 close-out; not re-run per the don't-redo law) | `826564bdf` |
| **(SUPERSEDED by P5.S6)** MASTER full suite (P5.S5 floor) | **`Tests: 10793, Assertions: 166187, Failures: 0, Errors: 0, Skipped: 2`, EXIT 0** - gate ran at branch tip `e12a16c89` prediction-first and hit EXACT on EVERY figure incl. assertions (cmp not needed - zero movers; MMG not a mover this run); described `fdff0133f` by the belt argument - belt diff vs `e12a16c89` EMPTY; lead run at `4ee8e9642` 10793 / 166156 / 1F (the escalated pin, pre-rewrite) / 2S / EXIT 1 prediction-exact | `fdff0133f` |
| **(SUPERSEDED by P5.S5)** MASTER full suite (P5.S4 floor) | **`Tests: 10783, Assertions: 166052, Failures: 0, Errors: 0, Skipped: 2`, EXIT 0** - gate ran at the synced tip `02522aef0` prediction-first and hit EXACT; describes `9a5197065` by the diff-empty argument (merge tree == gate tree, belt deliberately not re-run); cmp sole mover BaseSystemPromptTest +15a/+1t; CI-LAYOUT SIM (candy-core as real symlink, 17 dirs+1 link) = 10782 / 166056 / 1 skip / EXIT 0 NO BANNER | `9a5197065` |
| **goldens (CURRENT)** | **BOTH UNMOVED at P6.S5a, and a move there would itself have been the defect** (S5a renders NO prompt - that is S5b - and the fixture rule carries no `paths:`) - system `f09f37366a1925565dcc7725f659ff41` (7,829 B) and agent `ef0326dd38535aaa2f1d715919bff26e` (1,060 B), md5s taken at the gate tree; `git diff f3134703f..HEAD -- sugar-crush/tests/fixtures/` prints 0 lines and the REGEN LAW was never invoked; BOTH have been UNMOVED since P6.S2 (system last MOVED at `aff501a35` BY DESIGN; agent unmoved since `405252a41`). The P6.S4 pre-proof still holds: the `<project-instructions>` fence is filled by `InstructionFileLoader`, whose candidates are `CLAUDE.md`/`AGENTS.md` plus config-driven `loadForced()` globs, and the goldens render from `vendor/prompt-fixture/system-repo` under a pinned HOME. | `505734f9f` (md5s at `714bea7ec`) |
| **(SUPERSEDED by P6.S4 as to its tree; same two md5s)** goldens (P6.S3) | **BOTH UNMOVED at P6.S3, and that is the step's point** - system `f09f37366a1925565dcc7725f659ff41` (7,829 B) and agent `ef0326dd38535aaa2f1d715919bff26e` (1,060 B) are each the SAME BLOB object at base and tip; `git diff eb3f8b574..HEAD -- sugar-crush/tests/fixtures/` is EMPTY and the REGEN LAW never invoked. The `/rules` toggle's prompt effect is proven instead by TWO LIVE temp-HOME renders (pack-ON `rulesState:null`-and-on byte-identical 4,700 B, pack-OFF 4,253 B with the user-rules fence and its preamble counted 0), a FRESH `Runtime` per render because the prompt is memoised per-Runtime (§17.2). No committed fixture rulebook: user-tier content renders into the golden and `ensureFixtureUserHome()`'s sentinel (`:1751`) checks only `.../rules/global-style.md`, so new fixture files would not even be delivered on a warm `vendor/` tree | `017a691ae` |
| **(SUPERSEDED by P6.S3)** goldens (P6.S2b - BOTH UNMOVED) | **BOTH UNMOVED at P6.S2b** - system `fe2d9f6f…` and agent `ee24b410…` are the SAME blob objects at the P6.S2b merge base `52295ef64` and at tip `506ef5f5e`, fixtures diff empty; the step lands a roster tag with no emitter, so a golden move there would itself have been the defect. system `f09f37366a1925565dcc7725f659ff41` (7,829 B - **last MOVED at P6.S2 `aff501a35` BY DESIGN: the `<user-rules>` fence + `USER_RULES_AUTHORITY_PREAMBLE` ARE that step's deliverable; the move is PURE INSERTION, +515 B / 7 added lines / zero delete-replace; pre-P6.S2 `90d41a00dbf9cb0f71f9b4ce9b19c1e1` / 7,314 B, itself the P5.S6 by-design move**) · agent `ef0326dd38535aaa2f1d715919bff26e` (1,060 B - **UNMOVED at P6.S2, unmoved at P6.S2b, and unmoved since `405252a41`**; the blob object was IDENTICAL at the P6.S2 merge base `0c31bd9a5` and at `aff501a35`, the positive proof that no user-tier byte reaches the agent assembler) | `506ef5f5e` |
| **(SUPERSEDED by P6.S2 - the SYSTEM golden MOVED at `aff501a35` by design; it was UNMOVED from P5.S6 through P6.S1)** goldens (P6.S1 floor) | system `90d41a00dbf9cb0f71f9b4ce9b19c1e1` (7,314 B - **UNMOVED at P6.S1**; it MOVED only at P5.S4 `c5ba741a9`, P5.S5 `d25be7540` and P5.S6 `afebe1a39`, each by design; the P5.S6 move was PURE INSERTION 4 lines: the 280 B project-authority preamble appears 2x strictly INSIDE the `<project-instructions>` fences, fence open/close counts 2/2 unchanged, byte arithmetic 6750 + 2 docs x 282 = 7314, == BST:822 byte-pin; pre-S6 `3e44fc28…`) · agent `ef0326dd38535aaa2f1d715919bff26e` (1,060 B - **UNMOVED at P6.S1 and unmoved since `405252a41`**) · **P6.S1 moves neither, by design: the step adds no prompt content and nothing renders the new classes; the fixtures diff against base is EMPTY, so a golden move would itself have been the defect** | `88fa18f77` |
| **(SUPERSEDED by P5.S6 - the SYSTEM golden MOVED at `afebe1a39` by design)** goldens (P5.S5 floor) | system `3e44fc28cc2016d34b13969d20daf3e4` (6,750 B - MOVED at P5.S5 `d25be7540`, pure 22-line addition) · agent `ef0326dd…` unmoved | `fdff0133f` |
| **(SUPERSEDED by P5.S5 - the SYSTEM golden MOVED at `d25be7540` by design)** goldens (P5.S4 floor) | system `8c41b8f0a9573974897d6b3a9d7ab9f2` (5,559 B - **MOVED at P5.S4 `c5ba741a9`, the step deliverable**; pre-S4 `32ea749d…`) · agent `ef0326dd38535aaa2f1d715919bff26e` (**unmoved since `405252a41`**) | `9a5197065` |
| **Solo guards at P6.S4 tip `96e6577a3` (SUPERSEDED as to the tree by P6.S5a; the CURRENT per-class figures are in the worklog P6.S5a entry - GlobDialectDifferentialTest 13/354, TriggerTest 33/112, GlobFigureDrift 61/22256, SymbolCitation 7/3128, TreeWideGuardRoster 17/1121, AssertionSwallowingCatch 6/3397, CompiledPatternCacheBound 6/33, SkillPathPattern 17/130, PromptStability 16/402)** | LayeredSettings 25/73 · BootstrapLayeredSettings 22/75 · RulesStateWiring **5/31** · TrustKeyDocumentationDrift **12/90** · RuleLoaderTest **29/104** · RulesCommand 19/63 · ReadmeSettingsTierClaim 6/40 · ReadmeRosterDrift 9/61 · GlobFigureDrift 61/22225 · DocumentParagraphs 17/1351 · SymbolCitationDrift 7/3118 · BaseSystemPrompt 22/319 · TreeWideGuardRoster 17/1119 · ProjectTierRefusalInventory **15/777 (file UNEDITED - the `$ownWords` 37-cap landmine was correctly avoided)** · AssertionSwallowingCatch 6/3388 · ContainedPathInventory 49/85 · ReadPathCensus 17/248. **Isolation proof: `RulesStateWiringTest` has 5 methods, run alone give 1/3, 1/10, 1/3, 1/14, 1/1 = exactly 5/31, so the shared mutable `RulesState` its `setUpBeforeClass` hands back empty leaks nothing across methods.** | `96e6577a3` |
| **(SUPERSEDED by P6.S4)** Solo guards at P6.S3 tip `e28a99b04` | `RulesCommandTest` **19/63** (15/51 -> 17/57 -> 19/63 across fix-2/fix-3), `RuleLoaderTest` **28/99**, `RuleLoaderContainmentTest` **13/38**, `RulebookPromptRenderTest` **6/30**, `RulesStateWiringTest` **3/18**, `SlashDispatchTest` **18/251** (file diff empty - the dispatch guard passed UNEDITED, the machine's proof the command is wired not registered-and-dead), `BaseSystemPromptTest` **22/319** (byte-identical base->tip; cycle 1's non-reproducible 22/335 was the reviewer's own `BaseSystemPromptTest.php.good` scratch artifact and is killed - a stale shared-farm file would RED the 7,829-byte pin at `:835`, not drift a count), `PromptSectionTest` **23/84**, `TreeWideGuardRoster` **17/1119**, `ProjectTierRefusalInventory` **15/777**, `ContainedPathInventory` **49/85**, `ReadPathCensus` **17/248**, `AssertionSwallowingCatch` **6/3388**, `SymbolCitationDrift` **7/3114**. `NoRawAnsiInTranscriptTest` **8/19** and `tests/Commands` **423/2547** also held. The step's two NEW test files push the file-count-derived guards (`AssertionSwallowingCatchTest` 3385->3388, `SymbolCitationDriftTest` 3111->3114) purely by file presence with ZERO new `try` blocks - moving the two new files aside restores the exact base figures | `e28a99b04` |
| **Solo guards at P6.S3 tip - partial set (SUPERSEDED by the full CURRENT row above; same `e28a99b04` tree, fewer files named)** | `BaseSystemPromptTest` **22/319** (byte-identical base->tip; cycle 1's non-reproducible 22/335 was the reviewer's own `BaseSystemPromptTest.php.good` scratch artifact and is killed - a stale shared-farm file would RED the 7,829-byte pin at `:835`, not drift a count), `RulesCommandTest` **19/63** (15/51 -> 17/57 -> 19/63 across fix-2/fix-3), the `tests/Commands` suite **423/2547** (419/2535 -> 421/2541 -> 423/2547), `RulebookPromptRenderTest` 6/30, `RulesStateWiringTest` 3/18, `NoRawAnsiInTranscriptTest` 8/19. The step's two NEW test files push the file-count-derived guards (`AssertionSwallowingCatchTest` 3385->3388, `SymbolCitationDriftTest` 3111->3114) purely by file presence with ZERO new `try` blocks - moving the two new files aside restores the exact base figures | `e28a99b04` |
| **Solo guards at tip (SUPERSEDED by P6.S3 - names `aff501a35` and is BEHIND P6.S2b: `BaseSystemPromptTest` grew +1 test/+21 assertions and `PromptSectionTest` +1 test/+8 assertions there; RE-DERIVE before quoting any of these as current)** | BST (`BaseSystemPromptTest`) 21/298, `PromptSectionTest` 22/76, `RuleLoaderTest` 19/69, `RuleLoaderContainmentTest` 9/21, `TreeWideGuardRoster` 17/1112, `ReadPathCensus` 17/248, `ContainedPathInventory` 49/85, `ProjectTierRefusalInventory` 15/774, `StderrEmitterCensus` 94/5144, `SymbolCitationDrift` 7/3087, `EnvRosterDrift` 31/2957, `MaximsSectionTest` 9/35, `TriggerTest` 33/110 - thirteen files, measured by the step agent and re-measured by the orchestrator at that tip; every one of them is a derived source-walking guard whose figure names its own tree. The CURRENT roster-solo figure is `17/1112` (measured at `aff501a35`; the `17/1107` row below is two steps behind). **CORRECTION 2026-09-05: neither figure is current - P6.S3 grew the roster to `17/1119`, `ProjectTierRefusalInventory` to `15/777`, `RuleLoaderTest` to `28/99`, `RuleLoaderContainmentTest` to `13/38` and `PromptSectionTest` to `23/84`; see the CURRENT full row above.** | `aff501a35` |
| Solo TriggerTest (P6.S1) | OK (33 tests, 110 assertions) - the new union's own suite; re-measured UNMOVED at fix-2 tip `7e312e84e` after the two docblock-only fixes (7/7 changed lines docblock) | `7e312e84e` |
| Solo BaseSystemPromptTest | OK (16 tests, 194 assertions); assertion move 179 -> 194 = the +15 clause guard test | `f5a0f55eb` |
| Solo SuiteSkipRoster | OK (22 tests, 100 assertions) - layout-computed roster + 8 synthetic-farm tests (incl dangling-symlink); red-on-revert proven BOTH directions | `0ee61c8a5` |
| Solo PromptStabilityTest (post-P5.S5) | OK (16 tests, 402 assertions) at `e12a16c89` - the escalated across-turn pin REWRITTEN DELIBERATELY per the test's own doctrine (orchestrator authorized: not a §1.10 class); red-on-revert 4762 -> 3571 = minus the 1,191 B maxims layer EXACTLY (lead AND reviewer); structural asserts intact, MIN_STABLE_PREFIX_BYTES 4096 const untouched | `e12a16c89` |
| **(SUPERSEDED by P5.S4 + roster-layout CI-fix)** MASTER full suite | same floor, EXACT: Tests 10774 / Assertions 165986 / 0E / 0F / Skipped 2 / EXIT 0, cmp zero movers vs the roster-fix junit; solo RuntimeTest 142/542 green default AND LC_ALL=C | `9f3348ef5` |
| **(SUPERSEDED - crossed two trees)** MASTER full suite (post-P5.S3 + UPSTREAM-SYNC + roster-fix) | **`Tests: 10774, Assertions: 165986, Failures: 0, Errors: 0, Skipped: 2`, EXIT 0** (cwd checkout root, serial, stdin=/dev/null, probe 0, prediction-first; box-law pair GREEN this arm; Skipped = 1 roster + 1 conditional GitignoreAwareness path-repo-symlink guard whose precondition is absent in packagist layout) | `de81f45e4` |
| (SUPERSEDED by P5.S3 + upstream sync) **MASTER full suite (post-P5.S2)** - branch-tip figure carried by the diff-empty argument (belt deliberately NOT re-run), cwd /home/sites/prompt-step-P5.S2 root, </dev/null, serial, probe 0 | **Tests 10665 / Assertions 165289 / 0 fail / 1 skip** (MMG-201 clean arm; 165286 at 198; prediction written BEFORE and hit exact). merge-base == master tip 4493db1e2 and merge tree == gated tree de9e8aceb (diff empty) so the figure describes master by construction. Nine-file census 176/31593; roster 17/1103; testFiles 441; unaccounted 0; goldens UNMOVED system 32ea749d84938811ac9331419cae7380 / agent ef0326dd38535aaa2f1d715919bff26e | branch `5c8505501` (gate @ de9e8aceb) |
| **MASTER full suite (SUPERSEDED by P5.S2)**, gate at branch tip + belt on master, both cwd-checkout-root, `</dev/null`, serial, box quiet | **`Tests: 10644, Assertions: 165010, Skipped: 1`**, 0 failures (EXIT 0) - MMG-198 arm (165013 at the other arm). Test count EXACT vs prediction; cmp.py vs the P4.S4-era junit: sole movers ExchangeSummaryTest +18a/+6t + MouseModalGuard arm flip 201->198, ZERO remainder; post-merge belt on master printed the IDENTICAL figure (`/tmp/p4s5-belt.out`); `git diff --stat e2554332a master -- sugar-crush/` EMPTY so the figure provably describes master | `142cef6ce` |
| (SUPERSEDED by P4.S5) **MASTER full suite at the post-P4.S4 tree**, checkout root, `</dev/null`, serial, box quiet | **`Tests: 10638, Assertions: 164995, Skipped: 1`**, 0 failures (EXIT 0) — MMG-201 arm; 164992 at the 198 arm. Gate at branch tip `0ca4c088d` + belt re-run on master `/tmp/p4s4-belt.out` printed the IDENTICAL figure; `git diff --stat 0ca4c088d master` EMPTY | `1500ad32b` |
| P4.S4 scope + cycle record | merged 3 files +1848/−7 (`src/Context/ContextCompactor.php`, `src/Chat.php`, `tests/Context/ContextCompactorTest.php`); the declared `tests/CompactorTest.php` proved UNRELATED (different class) — reported not edited; lead cycles 1-5 (dedicated fixers; one fixer death ladder-handled), orchestrator cycle-6 fixer closed 3 MAJORs (alignment pin, 11-field copy pin, comment-only headroom narrowing — 2938/2938 token-identity proven), cycle-7 fresh reviewer NO FINDINGS incl revert-experiment; identity 8/8 literal, 0 EMAIL bytes | `1500ad32b` |
| P4.S5 scope + outcome | CLOSED AS MEASURED (outcome b): E23 collapse REAL+reachable (text-only sha256 key `src/Context/ContextCompactor.php:96-99`; both twins offered `:625`; one shared summary lookup `:1200`; tool_calls payloads key-blind) but LOSS measured FALSE - `summarizeExchanges` emits one row per PAIR, each twin keeps its line; the only collapse-attributable effect is the `Chat::parseExchangeSummaries` isset-guard dropping a second paraphrase of byte-identical text (benign by content identity); the `[2x]` fold is stage-3 documented design and fires with no map. 2 files +251/-2, src COMMENT-ONLY (elementwise token identity, lead + reviewer); 6 pins in the PRE-EXISTING ExchangeSummaryTest 16/36->22/54, each mutation-isolated; lead 2/5 cycles with dedicated fixer + cycle-2 fresh reviewer PASS; identity 2/2 literal, 0 EMAIL bytes; merge commit amended once for the message fill (parents preserved) | `142cef6ce` |
| F6b gap-filler MERGED | merge commit bcf419855: 5 commits 98aeffbb6..3bea55552, 4 files +10/-10 comment/string-only, line counts preserved; gate cwd F6b worktree root serial </dev/null probe 0 = Tests 10644 / Assertions 165013 (MMG-201 arm; 165010 at 198) / 1 skip / 0 fail / EXIT 0, prediction exact, cmp.py sole mover MouseModalGuardTest 198->201 (+3, dTests 0) - zero unexplained; sugar-crush diff 3bea55552 vs master EMPTY (tree-wide diff = the 3 bookkeeping record files only, read by no test); identity 20/20 literal + 0 EMAIL bytes; C1 5 / C2 2 / C3 NO FINDINGS, cap-3 held; belt figure appended in worklog-6 entry | `bcf419855` |
| F6c gap-filler MERGED | merge commit de1048ccf: 5 commits e39b0adc1..4620c5156 + sync 295b95e40, 3 files +83/-9 - PRR 6 cites (incl disclosed :501 STRING change), plan :1606/:1610 rule-42 in-line, StatusLineSegmentTest +74 = transcriptSignature same-count-REPLACE third control (22/4113->23/4117); proofs: PRR 751 LOC + 3113 tokens elementwise, plan 3626 unchanged, goldens byte-identical, path-gate 0; gate at 295b95e40 (cwd F6c worktree root, serial, </dev/null, probe 0) = Tests 10645 / Assertions 165017 (MMG-201 arm; 165014 at 198) / 1 skip / 0 fail / EXIT 0 - prediction exact; cmp.py sole mover StatusLineSegmentTest +4a/+1t, no MMG flip, zero unexplained; ancestry note: user re-merge pulled the sync tip into master history - merge-base = 295b95e40 and rev-list master ^branch = 0, so the merge tree == gate tree and the figure provably describes master (belt = corroboration); review C1 3minor/3nit -> fix-1, lead-caught fix-1 regression -> fix-2, C2 1minor -> fix-3, C3 CLEAN, cap-3 held; brief claims measured FALSE (GlobFigureDriftTest is the strlen() settings-glob generator, NOT a line counter; no guard polices File.php:NNN cites); 9 repo-shared stash entries (other plan) observed, NOT dropped; worktree + branch torn down after merge | `de1048ccf` |
| cwd, branch, clean tree | `/home/sites/sugarcraft`, `master` at `505734f9f` (the P6.S5a merge - bookkeeping sits on top; re-derive with `git -C /home/sites/sugarcraft log --oneline -1`); porcelain EMPTY - the 7 `.opencode/*` dirties were consumed by the `1cf242f26` caliber pass (disposition decision still stands, §8) | every commit |
| commit identity | `Joe Huss` / `detain@interserver.net` — 24/24 objects author+committer clean across the whole P4.S2 window AFTER the metadata repair; 8 gmail commits remain in P3.audit-fix-2 history (un-rewritable, recorded). CURRENT-COUNT RE-DERIVED at the P6.S5a close: ALL 14 commits in `origin/master..master` are `Joe Huss detain@interserver.net` (`git log origin/master..master --format='%an %ae'` aggregated through sort and uniq -c -> one row, count 14). P6.S5a specifically: the bracketed-token byte-scan of the commit OBJECT (`cat-file commit` piped to `grep -c` for the `[EMAIL]` token - see the CORRECTIONS bullet above for the exact form) returned 0 on BOTH the amended step commit `52de996fe` and the merge `505734f9f`, re-measured at this bookkeeping pass; the transport scrubs a typed address into `[EMAIL]`, so the email must be shell-interpolated (`printf '%s@%s' detain interserver.net`) and the commit OBJECT byte-verified. NEW ROOT CAUSE of the recurring `[EMAIL]` defect: agents SEE the sanitized token in injected context and ECHO it — briefs must command the literal address; orchestrator object-byte-scans incoming commits before every merge | `80db1b27d` / re-derived `505734f9f` |
| (SUPERSEDED by P4.S4) **MASTER full suite** at the P4.S2-era tree, checkout root, `</dev/null`, serial, box quiet | **`Tests: 10615, Assertions: 164754, Skipped: 1`**, 0 failures (EXIT 0) — MMG-198 arm; 164757 at the 201 arm | synced `47f7b477a` / replay `c8f01cdbe` (orchestrator gate) |
| master tree == the tree that figure was measured on | `git diff --stat c8f01cdbe master` EMPTY — c8f01cdbe is the identity-repaired replay of 47f7b477a (`diff --stat 47f7b477a c8f01cdbe` also EMPTY, trees byte-identical), so the figure describes master and re-running is provably redundant; belt `/tmp/p4s2-master.out` the belt re-run on master at `80db1b27d` CONFIRMED it verbatim: 10615 / 164754 / 1 skip / 0 fail, SUITE-EXIT=0 (`/tmp/p4s2-master.out`) | `80db1b27d` |
| (SUPERSEDED by P5.S4 - the SYSTEM golden MOVED at `c5ba741a9` by design; the no-move window below covers Phase 4 only) goldens | `32ea749d…` (system) · `ef0326dd…` (agent) — **unmoved since `405252a41`**, zero-byte fixtures diff re-confirmed across the WHOLE Phase-4 window (cycle-9 reviewer measured `f2204a7c4..HEAD`) | `80db1b27d` |
| (SUPERSEDED - packagist layout) nine-file census subset | `OK (176 tests, 31390 assertions)` at the SYNCED tip `47f7b477a`, over exactly the nine `HAND_MAINTAINED_CENSUS_SET` files (`tests/TreeWideGuardRosterTest.php:407-417`), cwd `sugar-crush/`, serial, `</dev/null`. Pre-sync `1eab2e0ed` the same nine read 176/31352 — the +38 is GlobFigure/SymbolCitation derived growth over the synced S3 layer. **A census figure names its file list AND its tree** | `47f7b477a` |
| (SUPERSEDED - packagist layout) **derived** guard roster | roster **67**, candidates 83, walkerFiles 181, testFiles 440, **unaccounted 0** — UNMOVED through all of Phase 4 batches 1-2. The S2 brief's '(NEW) -> 441' claim was FALSE — `UsageWiringTest.php` pre-exists since `738c586c1`; corrected in place. Roster test itself 17 tests / 1,101 assertions | `47f7b477a` |
| P4.S2 scope | merged window 9 files +1934/−103: five providers + `src/Usage.php`/`src/Util/TokenTracker.php` (the latter TWO comment-only, token-identity proven 929/269 elements) + `tests/UsageTest.php` + `tests/Integration/UsageWiringTest.php` | `80db1b27d` |
| metadata-only repairs (record) | `f7122adfb` = tree-identical rewrite of one `[EMAIL]`-authored tip commit (cycle-5 era, via commit-tree + rebase --onto); `c8f01cdbe` = the same repair for the fix-8 pair (`10907d74e`/`1f009e095`) + sync replay — all verified tree-byte-identical to originals, 0 EMAIL bytes in every object | `80db1b27d` |
| detached-HEAD anomaly (record) | the FIRST S3 merge landed on stray `211f4f5b1` — the main checkout had crept to detached HEAD; master ref unharmed, reflog keeps the stray; repaired by checkout + re-merge → `23a36254b`. LAW: assert `branch --show-current` == master AND porcelain 0 in EVERY pre-merge check | `23a36254b` |
| path-repo gate, **from the repo root** | `php tools/check-path-repos.php --no-lib-path-repos` exit 0 | `80db1b27d` |
| **nine-file census subset (STALE - names `826564bdf` at the Phase-5 close-out, two steps of source growth behind; RE-DERIVE before quoting as current)** | `OK (176 tests, 31840 assertions)` - the nine `HAND_MAINTAINED_CENSUS_SET` files (`tests/TreeWideGuardRosterTest.php:407-417`), cwd `sugar-crush/`, serial, `</dev/null`, MEASURED at the Phase-5 close-out. Box was NOT perfectly quiet (a foreign phpunit from /home/sites/phlix - other project, never touched - ran alongside); these are deterministic source-walking guards and matched the P5.CLOSE review-1 figure exactly, so no re-run | `826564bdf` |
| (SUPERSEDED - era census) **nine-file census subset (CARRIED)** | `OK (176 tests, 31786 assertions)` at `de81f45e4` - pre-sync 31593; +193 derived growth across the sync window. Carried UNCHANGED: none of the nine files is SuiteSkipRoster*/TreeWideGuardRoster*/BaseSystemPromptTest, the roster-fix solo guards re-measured unmoved (SymbolCitationDrift 7/3054 etc), and the final-gate cmp had BaseSystemPromptTest as SOLE mover; the aggregate itself was NOT re-run since `de81f45e4` - re-derive if a CURRENT census number is required. P5.S5 corroboration only: the tree-wide guards auto-grew (TreeWideGuardRoster +2 tests over the 2 new files) with ZERO literal updates; the CARRIED aggregate still names only its own tree | `de81f45e4` |
| **derived** guard roster **(STALE - names `826564bdf` at the Phase-5 close-out, two steps of source growth behind; RE-DERIVE before quoting as current)** | TreeWideGuardRosterTest solo `OK (17 tests, 1107 assertions)` - the 1105 -> 1107 assertion growth is the walk's own adaptive census over changed sources (zero literal updates); **unaccounted call-sites 0** (the roster-equality guard asserts `[]` and is green); MEASURED at the Phase-5 close-out, same box-quiet caveat as the census row - deterministic, matched review-1 | `826564bdf` |
| (SUPERSEDED - era roster) **derived** guard roster **(CARRIED)** | roster **67**, candidates 83, walkerFiles 181, testFiles **442**, unaccounted 0; roster test 17/1105 - the 17/1105 pair re-measured **UNMOVED at `0ee61c8a5`** (ci-roster-fix solo guards), the only roster-file edit since `de81f45e4` was a classification row, not a roster change. P5.S5 cmp: TreeWideGuardRoster +2 tests AUTO-DERIVED, zero literal updates - the walk counts the new files by construction; the 17/1105 pair still names its tree (0ee61c8a5) | `de81f45e4` / `0ee61c8a5` |

### PROGRESSION, all checkout root, all serial — the only comparable series

```
c7e5a6454  10500 / 161982 / 1   pre-merge master
1279d91cf  10503 / 162166 / 1   + P3.S4-fix-1
5cabca4a8  10519 / 162241 / 1   + P3.S5-fix-1     (PREDICTED exactly before merging)
f958ba8e6  10526 / 162447 / 1   + P3.S6           (test count predicted exactly; +12 found and attributed)
980670c0b  10547 / 163710 / 1   + P3.audit-fix-2  (ALL THREE figures predicted exactly)
5f716b34d  10556 / 163806 / 1   + P3.audit-fix-3  (MMG-198 arm; the lead's 163809 = MMG-201 arm;
                  cmp.py: sole mover MouseModalGuardTest 201->198, dTests +0, every other class
                  identical; +205 tests / +3,158 assertions at this arm vs baseline 10351/160648)
58150a432  10556 / 163809 / 1   + P3.CLOSE-r3-fix  (record-side; RuntimeTest +16 doc-comment lines,
                  comment-only proven token-identical; orchestrator re-ran the full suite on master
                  at THIS tip: 10556 / 163809 (MMG-201 arm) / 1 skip — see gate-r3fix.out)
f2204a7c4  10574 / 163972 / 1   + P4.S1   (MMG-198 arm; 163975 at 201; prediction hit exactly;
                  UsageTest 19/54 -> 37/191; nine-file 176/31255 -> 176/31284 via GlobFigureDrift +29)
23a36254b  10582 / 164469 / 1   + P4.S3 lead   (MMG-198 arm; every lead suite run pre-predicted;
                  orchestrator gate at 770873a9d exact; StatusLineSegmentTest 55 -> 63 tests)
a834207d4  10582 / 164483 / 1   + P4.S3-fix8   (164486 at 201 arm; test-only StatusLineSegmentTest
                  +68/-16; fix-8 agent suite prediction 164469+14 EXACT; orchestrator belt exact)
80db1b27d  10615 / 164754 / 1   + P4.S2   (164757 at 201 arm; prediction 10612 tests, the +3
                  adjudicated per-class: UW restructure folded 3 pre-existing cases in; cmp.py
 5a87ce80a  10615 / 164754 / 1   + bookkeeping (resume rewrite #3, three worklog entries, S2 brief roster correction)
 1500ad32b  10638 / 164995 / 1   + P4.S4   (MMG-201 arm; 164992 at 198; gate at 0ca4c088d + belt on master
                   IDENTICAL; ContextCompactorTest 57->80 tests; +241/+23 attributed per-class zero remainder)
 b7ec850c6  (bookkeeping - resume rewrite #4, P4.S4 worklog entry, S4+S5 step briefs; no suite delta)
 8b4167d32  (record correction - nine-file census provenance 31460 -> 31461; no suite delta)
 142cef6ce  10644 / 165010 / 1   + P4.S5   (MMG-198 arm; 165013 at 201; gate at e2554332a, test count EXACT vs
                    prediction; cmp.py sole movers ExchangeSummaryTest +18a/+6t + MMG arm flip 201->198, zero
                    remainder; belt on master IDENTICAL; PHASE 4 CLOSED - 30 of 63)
 5c8505501  10665 / 165289 / 1   + P5.S2   (MMG-201 arm; 165286 at 198; gate at branch tip de9e8aceb == merge tree ->
                    belt skipped via diff-empty argument; +6t/+59a fully attributed to 6 new RuntimeTest pins;
                    lead cyc1 2x CLEAN; independent review-2 CLEAN 0maj/1minor-preexisting; E1/E2/E3 + four sandbox
                    reversions reproduced; census 176/31593; goldens byte-identical - step 32 of 63)
 97ced919e  10696 / 165527 / 1   + P5.S3   (fence-escape authority PromptFence + both carried vectors CLOSED + ref-cap + A6 rewritten; gate caught 2 history-dependent guards -> fix-2 rescoped them; review-3 re-gate hit green arm to the unit; cmp bit-reproducible; belt skipped - diff-empty arg; merge message defang ruling; 33 of 63)
 71cab0fca  10774 / 165983 / 2   + UPSTREAM-SYNC (origin/master 16 commits - qwen/sglang Q1-Q10 lane, ZERO conflicts; composer -o -W by user waiver - vendor now packagist real dirs; NO new php errors; SuiteSkipRoster EXIT 1 -> conditional entry)
 de81f45e4  10774 / 165986 / 2   + roster-fix (tests-only; full suite EXIT 0 at the NEW FLOOR; sole cmp mover +3 explained by the roster loop)
 8ea31a678  10782 / 166037 / 2   + ROSTER-LAYOUT CI-fix (fix 0ee61c8a5: SuiteSkipRoster computes the conditional entry from the vendor layout - hasPathRepoSymlinks/expectedForLayout mirroring the test's own is_link gate; +8t = 8 new synthetic-farm tests, +51a = +27 body + +24 AssertionSwallowingCatch census population; CI-sim with candy-core as real symlink 10782/166056/1skip EXIT 0 NO BANNER)
 9a5197065  10783 / 166052 / 2   + P5.S4   (verify-before-done clause folded into `# Tool use`; gate at synced tip 02522aef0 prediction-first EXACT; cmp sole mover BaseSystemPromptTest +15a/+1t; belt skipped - diff-empty argument; SYSTEM golden MOVED by design 8c41b8f0... (5,559 B), agent unmoved; 34 of 63)
 fdff0133f  10793 / 166187 / 2   + P5.S5   (core.maxims section at index [1]; gate at branch tip e12a16c89 prediction-first EXACT incl. assertions; belt diff vs e12a16c89 EMPTY so the figure describes master; cmp not needed - zero movers, MMG not a mover; SYSTEM golden MOVED by design to 3e44fc28... 6,750 B pure-add, agent unmoved; across-turn stability pin flipped per its own rewrite-deliberately doctrine (orchestrator-authorized, NOT a §1.10 class); 35 of 63)
 826564bdf  10795 / 166235 / 2   + P5.S6   (project-instructions authority preamble - AMENDED scope: preamble only, harness-injected/user-rules fences DEFERRED to P6.S2 with reason after the premise check found G1/G2/G7 already built; gate at branch tip afebe1a39 prediction-first EXACT; belt diff afebe1a39..826564bdf -- sugar-crush/ EMPTY so the figure describes master; cmp vs S5 floor exactly 3 permitted movers (BST +38a/+2t + two adaptive source-census tests +10a), zero unexplained; SYSTEM golden MOVED by design to 90d41a00... 7,314 B pure insertion 4 lines, agent unmoved; review-1 MERGE-YES 0B/0M/1minor/3nits all deferred; PHASE 5 CLOSED - 36 of 63)
 88fa18f77  10832 / 166702 / 2   + P6.S1   (trigger union: 4 new src classes + TriggerTest 33/110, shipped UNWIRED by design;
                    gate at branch tip 7e312e84e prediction-first EXACT; +4t/+357a = 17 derived source-walking guards growing over
                    4 new src files, remainder 0; belt diff EMPTY; BOTH goldens unmoved - the step adds no prompt content;
                    cycle-2 reviewer OVERTURNED cycle-1's u-modifier finding by execution; 37 of 64)
 aff501a35  10866 / 167264 / 2   + P6.S2   (rules tier + RuleLoader + D1/D2 provenance fences; SYSTEM golden MOVED by design to
                    f09f3736… 7,829 B, pure insertion +515 B / 7 lines / zero delete-replace, agent unmoved same blob;
                    orchestrator REVERSAL - PromptFence roster widened here after cycle 1 measured the cost at zero golden bytes,
                    collapsing the P6.S2b split rationale; cycle 2 caught the same unpinned-escape class one call site over at
                    Runtime.php:2634; gate at ca1470ff2 prediction-first EXACT, same-tree cmp zero movers, belt EMPTY;
                    no cycle-3 by decision - cycle 2 prescribed its remedy verbatim; 38 of 64)
 506ef5f5e  10868 / 167297 / 2   + P6.S2b   (harness-injected roster tag, NO emitter - defang-only on the system-reminder
                    precedent, so BOTH goldens UNMOVED by design and a golden move here would itself have been the defect;
                    ruling recorded in-plan BEFORE code, wrapping the skill region and wrapping maxims both REJECTED;
                    cycle 1 MINOR = the step's own census sentence overstated what the three tier forgery guards catch
                    (they forge only their own tier); cycle 2's E1 justified the step's ONLY assertion deletion - keeping
                    TAGS intact and making escape()'s alternation ignore the last roster element reddens THREE surviving
                    value asserts, while assertContains('harness-injected', tags()) reads tags(), which still contains the
                    element under that mutation, so it "would have passed and caught nothing"; no cycle 3 by precedent;
                    gate at branch tip 730478ab2, belt empty BY CONSTRUCTION (merge tree == tip tree fd6d771f...);
                    movers exactly 4, remainder 0; 39 of 64)
 017a691ae  10912 / 167830 / 2   + P6.S3   (rulebooks: named, toggleable rule packs + the /rules command; BOTH
                     goldens UNMOVED - a zero-movement step by design, prompt effect proven by two live temp-HOME
                     renders 4,700 B on / 4,253 B off with a fresh Runtime each (memoised per-Runtime); NIT-5
                     DISCHARGED; Rule::withEnabled() ADOPTED not removed; RulesState deliberately shared-mutable;
                     session-scoped toggle so P6.S4 owns persistence (armed-harlequin-wolverine => S3,S4,S5 serial);
                     gate PASSED ON RECONCILIATION - headline predicted 10913/167807 MISSED (3 steps running; new
                     process law) but per-class cmp vs the P6.S2b gate = ZERO remainder, MouseModalGuardTest NOT a
                     mover; belt git diff e28a99b04 017a691ae -- sugar-crush/ EMPTY; cycle 1 REQUEST_CHANGES (MAJOR-1
                     unpinned Bootstrap::chat() injector), cycle 2 fresh reviewer APPROVE_WITH_NITS after overturning
                     two judgements; fix-2 + fix-3 closed all four ANSI interpolation sites in-step; 40 of 64)
 608914072  10923 / 167931 / 2   + P6.S4   (config surface for rules: disabledRules registered user-tier-only + the
                    launch seed through Bootstrap::rulePacksToDisable() off the MERGED view; BOTH goldens UNMOVED - a
                    second consecutive zero-movement step; floor 10912 -> 10919 (lead) -> 10921 (fix-1) -> 10923 (fix-2);
                    final-tip prediction HIT EXACTLY; per-class cmp 5 movers / remainder 0, MouseModalGuardTest NOT a
                    mover; belt 96e6577a3..608914072 -- sugar-crush/ EMPTY; cycle 1 REQUEST_CHANGES (MAJOR-1 two
                    UNPOLICED README roster sentences - fixed by teaching the derivation FIRST), cycle 2
                     APPROVE_WITH_NITS with 9 findings all landed in fix-2; 41 of 65 - and note the plan now has 65
                     headings because P6.S5 was SPLIT into S5a (glob dialect) / S5b (the channel, BLOCKED on a user
                     decision); there was NEVER a P6.S6)
 505734f9f  10937 / 168399 / 2   + P6.S5a  (one paths: glob dialect: shared compiler src/Util/PathGlob.php speaking the
                    SkillRegistry fnmatch dialect, BOTH matchers routed through it, legacyPathMatch() kept reachable
                    (§1.10), PathTrigger's YES-set moved on 13/33 rows - 10 widen / 3 narrow (#11,#22,#23) / 20 hold -
                    while the LIVE skill channel is proven UNCHANGED over 130,317 derived comparisons; NOTHING reaches
                    prompt rendering (S5b, BLOCKED); BOTH goldens UNMOVED; +14t/+468a fully attributed to derived
                    censuses growing over the two new files (18 movers summing exactly, remainder 0); gate in a FRESH
                    detached worktree at 714bea7ec, prediction 10937/168399 written BEFORE and HIT EXACT; belt
                    714bea7ec..505734f9f -- sugar-crush/ EMPTY; 4 review cycles (cap 5) incl. the cycle-3 MAJOR
                    REFUTED by git log -S forensics; 42 of 65)
```

### SEVEN METHOD CHANGES THAT ARE NOW PART OF THE PLAN — use them

1. **Reconcile a moved total with a per-class JUnit diff, FIRST — not last.** PHPUnit's JUnit
   `<testcase>` carries an `assertions` attribute. Run both sides with `--log-junit` and diff per
   class: `python3 prompt_kit/tools/cmp.py <branch-junit> <master-junit>`. **This is the single
   highest-leverage tool this plan has produced.** On P3.audit-fix-2 it took a `+7` remainder the
   per-file figures could not explain and named five tree-wide guards in one pass.
2. **The census set is DERIVED now — run `tests/TreeWideGuardRosterTest.php`.** The hand-maintained
   nine survive as a cheap pre-check. Do not grow the list; if something is missing, the derivation
   is what should be taught to see it.
3. **State a prediction BEFORE running a suite, then check it.** Done on every merge in Phase 3. On
   P3.audit-fix-2 all three figures were predicted exactly. A prediction that misses is information;
   a figure with nothing to compare against is not.
 4. **A measurement can be provably redundant, and saying so beats re-running it.** Sync the branch to
    master BEFORE measuring; then after merging, `git diff <synced-tip> HEAD` empty proves the branch
    figure describes master. Record the reasoning, not a second seven-minute run.
 5. **Predict from the FINAL tip's per-class solo evidence, or LABEL the headline derived-not-predicted.**
    The per-class `cmp.py` decides a merge, NOT the headline number - three consecutive Phase-6 steps
    (P6.S1..P6.S3) missed the orchestrator's hand-predicted headline while the per-class diff reconciled
    to ZERO remainder every time. For a fix-heavy step, several derived source-walking censuses grow
    automatically over the edited files and no hand arithmetic prices them; predict from the solo
    per-class figures taken at the final tip, or state up front that the headline is derived after the
    fact rather than a prediction.
 6. **A completion notification is not a delivery.** Verify the artifact exists on disk before trusting
    a "completed" notice - two P6.S5 premise-check spawns "completed" with ZERO artifact (one in 12
    seconds). When a research task must yield a file, prefer `task`+`coder` under explicit read-only
    constraints, command incremental writes (create the file first, append after each sub-answer, end
    with a `## TRUNCATED AT Q<n>` marker if it must stop), and treat a seconds-long completion as a
    failed spawn on the TRANSPORT, not a "no findings" result from the task.
 7. **A blank / aborted / truncated return is a TRANSPORT DEATH — RESUME THE SAME AGENT, up to ~15
    times.** USER RULING 2026-09-05, verbatim: "whenever agents come back with blank responses just
    resume them starting where they left off — it's a model problem. Keep resuming them till they
    finally finish — might take 1 time, might take 15. No need to change prompts or start a new agent
    in most cases. If by 5 retries they are ALL stopping at the same duration (say each roughly 2:53)
    then start a NEW agent instead of resuming. Otherwise keep resuming, up to 10-15 times, and only
    after THAT try a new agent. This rule always applies even outside this plan." Framing: a blank
    return is NOT a result and NOT "no findings"; the agent's work usually PERSISTS on the branch and
    disk ACROSS the blank, so interleave DISK FORENSICS with every resumption instead of concluding
    nothing happened. Default action = resume the SAME agent; escalate to a FRESH agent ONLY after
    ~5 retries that each die at a SIMILAR duration; absolute ceiling ~15 resumes. Evidence at
    P6.S5a: the builder returned blank on its first two turns (one a user interrupt) and landed the
    WHOLE step on resume #1 — the rule cost nothing because the work was already on disk.

### CORRECTIONS SO THEY STOP PROPAGATING

- **`ps -eo pid,cmd | /usr/bin/grep -c '[v]endor/bin/phpunit'` — the box-quiet probe this plan has
  used for weeks — RETURNS A FALSE 1.** The `[v]` bracket defeats a self-match by grep, but not by
  the harness's enclosing `bash -c`, whose argv contains the whole script text including the phpunit
  path. So it alarms whenever it runs in the same command as the suite it guards, which is the only
  way anyone runs it. *How measured:* printing the matching line gives a single
  `/bin/bash -c source …` wrapper and no php process. The failure direction is a false ALARM, the
  safe one — **but an alarm nobody can explain is an alarm that gets waved through, and the day it
  means something it will look identical.** Use instead:
  ```sh
  ps -eo cmd | /usr/bin/grep -c '^php .*phpunit'      # 0 = box quiet
  ```
- **THE GOLDENS WERE NOT "UNMOVED THROUGH THE WHOLE OF PHASE 3".** *What is true:* the system golden
  moved three times and the agent golden twice; both have been unmoved only **since the P3.S5 merge
  point**. *How measured:* `git show <sha>:…/golden-*.txt | md5sum` at each Phase 3 merge point.
  All three moves are legitimate: P3.S1's and P3.S3's ARE those steps' stated purpose, and the third
  is fixture hermeticity (`OS version: Linux 6.8.0-138-generic` → `OS version: <host>`), not
  behaviour. **Why it matters:** the false sentence taught the next reader that any golden move in
  Phase 3 is a red flag, so they would either alarm at three legitimate moves or trust the sentence
  and skip the check. **State the window a no-move claim covers, or the claim is unfalsifiable.**
- **AND THE FIRST VERSION OF THAT CORRECTION GOT THE ATTRIBUTION WRONG** — caught by the close
  reviewer within the hour. It said the `<host>` change happened at **P3.S5**, because that is the
  row where the md5 changes. **P3.S5 moved NEITHER golden**; the change was `33df838d0`
  (P2.audit-fix-1), which sits between P3.S4 and P3.S5 in first-parent order.
  **THE GENERAL LESSON:** a table of `git show <sha>:file | md5sum` gives **state at each point**,
  and every merge inherits everything merged before it. Turning that into "step X changed it" is a
  category error. **To attribute a change to a commit, ask the commit:**
  `git diff <sha>^ <sha> -- <path>`, or `git log --first-parent -- <path>`. A value first differing
  at row N means the change landed at or before N — not at N.
- **`--filter AgentTest` is a regex that ALSO matches `SubAgentTest`.** Prefer a path
  (`… sugar-crush/tests/Agents/AgentTest.php`) when you want one file.
- **Assertion totals are DETERMINISTIC across sequential uncontended runs** — proved twice on
  P3.S5-fix-1 (162057 both times). The old 18-assertion spread came from two **concurrent** full
  suites. Keep the box quiet for a merge-deciding figure; do not treat single-run totals as noisy.
- **BEWARE BACKTICKS IN A `git commit -m "…"`.** Bash runs them as command substitution; it corrupted
  the P3.S6 merge message in two places. **Write commit messages to a file and use `-F`.**
- **Bash cwd DOES persist in this harness.** A `cd` in one call leaves the next call there. It has
  produced a false GREEN once — a `git diff` whose pathspec matched nothing from the wrong directory
  printed nothing, which reads exactly like "no changes". **Anchor with `git -C <path>` and absolute
  paths.**

- **A HEADLINE SUITE FIGURE CAN HAVE TWO LEGITIMATE VALUES.** `MouseModalGuardTest` arms itself by
  viewport (assertions 198 vs 201 — observed variants 168/181 too; under a live tty even a hard
  failure at `:792` even with `COLUMNS`/`LINES` unset — tty-ness itself drives it). So
  `163,806` and `163,809` are BOTH "the master number". NEVER adjudicate a ±3 headline delta by
  headline: run `cmp.py` per-class first — if the sole mover is MouseModalGuardTest, the tree did
  not change behaviour. Do NOT weaken or "fix" the test.
- **md5 OVER A RE-SERIALIZED TOKEN STREAM IS SERIALIZATION-DOMAIN SENSITIVE.** The cycle-2 brief
  quoted md5 prefixes of stripped `token_get_all()` streams; they did not reproduce under another
  (equally honest) serialization even though the underlying claim was TRUE. State token-stream
  identities **elementwise** (token count + array-equality of `[id,text]` pairs).
- **THE 320 / 29,926 NINE-FILE CENSUS FIGURE WAS AN ORCHESTRATOR ERROR.** It was propagated from a
  pickup verification note that carried NO FILE LIST, and the set it described was never the nine.
  Corrected 2026-09-02 by direct measurement: the nine `HAND_MAINTAINED_CENSUS_SET` files measure
  **OK (176 tests, 31255 assertions)** at `cf41aacd6` (176 / 31245 at `470e43569`) — cwd
  `sugar-crush/`, serial, `</dev/null`. **THE LESSON:** a census figure must always be accompanied
  by the file list it was measured over; without its domain a number is not a measurement, and this
  one survived into a brief, a worklog entry and this table before anyone re-derived it (rule 1).
- **AGENTS ECHO THE SANITIZED IDENTITY TOKEN.** Two P4.S2 fix agents committed with author email
  literally `[EMAIL]` — their context shows that placeholder wherever the address is named, and they
  copied what they saw instead of writing the real value. Cheap to repair PRE-merge
  (`git commit-tree` metadata rewrite with env-var identities, trees byte-identical — done twice:
  `f7122adfb` and `c8f01cdbe`); impossible after push. BRIEFS MUST SAY: write
  `Joe Huss <detain@interserver.net>` as a literal, never copy an identity from injected context.
  ORCHESTRATOR: `git cat-file` byte-scan every incoming object before merging. STRENGTHENED 2026-09-04 (ci-fix): the sanitizer also redacts the token inside TOOL ARGUMENTS before git sees them - a correctly-typed author field became the literal placeholder; LAW: commit authors via shell-interpolated git config, and byte-verify the commit OBJECT (od -c / cat-file) - display renders both identically. Fix commit: 9f3348ef5.
- **FINALIZE AND EYE-READ THE MERGE MESSAGE FILE BEFORE THE MERGE.** One merge ran while the message
  still carried the `GATEFIGURELINE` placeholder, because the fill script died on a backslash-n
  literal inside a single-quoted python string — the recurring harness trap, violation number six.
  Detected at once; fixed via `git commit --amend -F` (message-only, both merge parents preserved) —
  tip moved `73f238bfe` -> `80db1b27d` with an identical tree. Record the amend, never hide it.
- **A FIGURE NAMES ITS TREE, NOT JUST ITS FILE LIST.** 176/31352 and 176/31390 are BOTH true — of
  different trees (pre-sync `1eab2e0ed` vs synced `47f7b477a`). Quoting one as "the" census figure
  made a review-brief claim false-by-no-fault; cite figure AND commit together.

- **A FINISHED task() SESSION NEVER GETS WOKEN BY A BACKGROUND-NOTIFICATION.** A step lead that ends its turn saying "waiting for <pty_exited>" has ABANDONED the step, not paused it; two such returns happened this session and only the ladder resumption + orchestrator-side disk re-measurement (14 checks) recovered the truth. BRIEFS MUST COMMAND: run long suites IN-SESSION (a task agent may use a background process but must wait on it with bounded sleep+poll of ITS OWN process before returning) or return only when every promised measurement is on disk.
- **`.ocx/receipt.jsonc` DIRT IS HARNESS TELEMETRY.** The tooling rewrites two installedAt stamps in it and dirties the main tree between sessions. `git -C /home/sites/sugarcraft checkout -- .ocx/receipt.jsonc` before any porcelain assertion; never commit it, never stash it. (History shows zero dedicated bump commits - restoring matches the convention.)
- **THE IDENTITY BYTE-SCAN TARGETS THE BRACKETED TOKEN**: `cat-file commit <sha> | /usr/bin/grep -c '\[EMAIL\]'` must be 0. Scanning for bare `EMAIL` self-flags honest prose in your OWN commit message (a P5.S2 merge-message abort at first sight of "0 EMAIL bytes"; adjudicated, message kept, rule recorded).

- **COMMIT MESSAGES ARE FIXTURE INPUT - WRITE THEM TAG-FREE.** The `<env>` block's Recent-commits window renders the checkout's own subjects; PromptFence now defangs tag bytes there (correct behavior), so ANY whole-prompt/whole-block assertion scanning for escape residue (`&lt;`) goes red the moment history legitimately contains a tag - self-referential fixture poisoning, caught by the P5.S3 orchestrator gate AFTER the lead's and two reviewers' green runs (none had run the full suite at final tip). LAW: (a) grep -c '<' == 0 on every commit-message file before use; (b) residue guards scope to the fenced REGION or the specific LINE with presence-first assertIsInt, never whole assembled text; (c) the final full-suite figure must come from a run at the FINAL tip after the LAST subject enters the log window.

- **`composer update` IS NOT PERMANENTLY FORBIDDEN - IT IS ORCHESTRATOR/USER DECISION WITH A NAMED REASON.** Executed 2026-09-04 by user command (recorded WL-B verbatim). Consequence to remember: `sugar-crush/vendor/sugarcraft/*` are packagist REAL DIRS; local monorepo lib edits do NOT reach sugar-crush tests anymore; all pre-sync figures are historical (tree-named); SuiteSkipRoster carries one CONDITIONAL EXPECTED entry (path-repo symlinks) that fires only in this layout - CI's injected layout flips it back to asserting.

- **STALE FIGURE IN THE RESUME ITSELF: PromptSectionTest measures 12/33 (was quoted 12/32).** Agents echo what briefs say; re-derive per-step. The SuiteSkipRoster guard exits the WHOLE phpunit process 1 on roster violations even with 0 failures - read the bootstrap banner, not just the counts line.

- **A BLANK / EMPTY AGENT RETURN IS A TRANSIENT MODEL ERROR - RESUME THE SAME AGENT, up to ~10 attempts; do NOT shrink the step or rewrite the brief to cope with it.** USER RULING 2026-09-05, recorded as plan law in the worklog P6.S1 entry. *How measured:* the P6.S1 step lead returned blank SIX times across both `task` and resume paths, yet the step merged clean on the same brief. **Interleave DISK FORENSICS with resumption, because real progress can happen during a blank** - after the fifth blank, forensics showed the 436-line `TriggerTest.php` already on disk while nothing had been reported, AND a half-finished deletion-experiment edit to `KeywordTrigger.php` sitting UNCOMMITTED (a naive `stripos` mutation no transcript mentioned). Any agent that mutates source to prove a red must restore before returning. Related routing lesson from the same step: `scribe` blanked seven consecutive times on this bookkeeping work while `coder` succeeded repeatedly - route docs-with-git work to `coder` after two `scribe` blanks.
- **THE RESUME LADDER IS USER LAW (2026-09-05) - it EXTENDS the five-attempt and ~10-attempt framings above and in §6, it does not soften them.** A blank/aborted/truncated return = transport death, never a result and never "no findings". Default action: RESUME THE SAME AGENT where it left off - no prompt rewrite, no new agent "in most cases" - because the work persists on the branch/disk across the blank, so interleave disk forensics with resumption. Switch to a NEW agent ONLY after ~5 retries that each die at a SIMILAR duration (say roughly 2:53 each); otherwise keep resuming up to 10-15 times and only then try a new agent. Ruling verbatim, and it "always applies even outside this plan" - see method change 7 above. *How measured:* the P6.S5a builder blanked on its first two turns (one a user interrupt) and delivered the entire step on resume #1; §4's older five-blank/~10-attempt numbers predate the ruling and the 15-resume ceiling governs now.
- **A `/** … */` DOCBLOCK CANNOT CONTAIN A LITERAL `**/` SEQUENCE** - it terminates the comment. *How measured:* `PathTrigger` hit it as a real PARSE ERROR caught by `php -l`, not a style complaint. The fix documents the glob dialect in prose with a parenthetical so the next editor does not "restore" the example into a syntax error.
- **`SymbolCitationDriftTest` IS BLIND TO `{@see self::testFoo()}` CITATIONS.** Its `scrape()` accepts a self-citation only in the BARE `testFoo()` form (`tests/SymbolCitationDriftTest.php:266`), and the fallback `looksLikeATestSymbol()` (`:335-355`) derives the class name `self`, which is neither a `*Test` name nor a registered placeholder, so the token is silently DROPPED - the guard built for exactly that defect skipped it. *How measured:* reflecting the guard's own internals (the `self::` form scraped nothing, the FQN form scraped a `tests-see` row, and pointing the FQN form at a nonexistent method redded the guard as a negative control). Tree today 1,475 policed citations / 0 dangling. **Widening it to resolve `self`/`static`/`$this` is its own step** - ~250 `self::`-form references tree-wide would become policed at once and several would red immediately (tracked F-GUARD). **AND THE SAME GUARD POLICES TEST-SYMBOL CITATIONS ONLY (`:71-76`), so PRODUCTION-symbol names are OUT of its scope - which corrects the over-broad "no forward references" claim written into the P6.S1 brief.**
- **TWO REVIEWERS CAN DISAGREE AND THE TIE BREAKS ON EXECUTION, NOT SENIORITY.** *How measured:* at P6.S1 cycle 2 ran its own probes and measured `/\bcafé\b/i` against `/\bcafé\b/iu` on PHP 8.3.6: WITHOUT `u` the pattern returns FALSE for `café`, `café bar` and `café x` but TRUE for the fused `caféx`, because `é` is the two bytes 0xC3 0xA9 - non-word bytes - so the closing `\b` finds no boundary at a space or end of line; WITH `u` it behaves as claimed. So the ORIGINAL docblock was TRUE and fix-1's "correction" had inverted truth into falsehood. The lead re-measured the same matrix independently and restored the true rationale at `7e312e84e`. **measure-don't-assert applies to prose ABOUT behaviour, not only to numbers.**

### If the tree HAS moved, or you distrust any of the above

1. Confirm `/home/sites/sugarcraft`, `master`, `git status --porcelain` empty.
2. Confirm the identity — it fails silently and cannot be repaired later without rewriting history:
   ```sh
   git -C /home/sites/sugarcraft config user.name    # must print: Joe Huss
   git -C /home/sites/sugarcraft config user.email   # must print: detain@interserver.net
   ```
   Re-check after every step, not only before committing — ORCHESTRATION-RULE-2 in §7. And check
   the AUTHORS of incoming commits too, not just your own config: `git log --format='%an <%ae>'
   <base>..prompt/<ID> | sort | uniq -c` must show ONE identity (the gmail-address incident).
3. Confirm the newest entry in `prompt_worklog.md` matches the last plan commit in `git log`. If one
   is missing, **reconstruct it before doing anything else** (`prompt_plan.md` §3.3).
4. `git worktree list` — expect EXACTLY ONE: `/home/sites/sugarcraft`. Any `/home/sites/prompt-step-*`
   you did not just create is stale; run §1.12's checks before removing it, and check it for ignored
   files worth rescuing first. `/home/sites/crush-lane-{a,b,c}` belong to the other plan.
   Leave them alone.
5. Re-take the master baseline SERIALLY, and record the cwd beside the number:
   ```sh
   php sugar-crush/vendor/bin/phpunit -c sugar-crush/phpunit.xml --colors=never </dev/null | tail -4
   ```
6. Then read §8 and do exactly what `Next step` says.

## 5. The sequencing gate — checked

**CHECKED 2026-09-05 - supervisor cleared Phase 6 ("start P6.S1 now"); Phases 5-11 were cleared 2026-09-04 and Phase 6 again 2026-09-05. P6.S1 and P6.S2 touched NO live collision row; P6.S2b touched only `src/Context/PromptFence.php` + two test files; **P6.S3 edited `src/Chat.php`** (the `rulesState` injector + `Chat::rulesState()`) under the standing Phases-5-11 clearance - the same clearance P4.S4 already relied on for a Chat.php merge - recorded explicitly in the worklog P6.S3 entry; **P6.S3 touched Chat.php but did NOT touch ContextCompactor.php.** P6.S4 (`LayeredSettings.php`/`Bootstrap.php` + four test files + `docs/SETTINGS.md` + `README.md` + `RulesState.php`) touched NEITHER live collision row - verified over the full 10-file delta at `96e6577a3` - and **P6.S5a touched NEITHER row either** (`src/Context/Triggers/` + `src/Skills/` + `src/Util/PathGlob.php` + the `src/Context/Rule.php` doc-block + tests - verified over the merged 7-file delta). The old blanket "no re-check needed inside Phase 6" is now SPENT: **RE-CHECK is still required BEFORE P6.S5b AND BEFORE P7.S3 (both want `src/Backend/EngineBackend.php`, a live collision row) AND BEFORE PHASE 8 (`Chat.php` + `ContextCompactor.php` - the live rows that matter next); P7.S1 (Hooks only) needs NO re-check.** **PHASE 6 CLOSED 2026-09-05 via its cycle-1 close review** (fresh read-only reviewer `courageous-silver-woodpecker`, verdict PHASE 6 MAY CLOSE, 0 BLOCKER / 0 MAJOR / 1 MINOR / 3 NIT; MINOR-1 - a stale `withTriggers()` line-cite - fixed in the close commit, which reads code and changes none of the collision rows). Next gate action: open Phase 7 at P7.S1, then re-check §5 before P6.S5b / P7.S3 / Phase 8.

Collisions still live (re-check before the named phases):

| Collision | Detail |
|---|---|
| `sugar-crush/tests/Tools/BuiltInToolCorpusTest.php` + `sugar-crush/src/Context/RepoMapBlock.php` | **RESOLVED 2026-08-29 — this was never a real collision during this plan.** The `src/` cardinality assertions were removed by `8706d2ec4` (*"decouple BuiltInToolCorpusTest and RepoMapBlock from the src/ file-count census"*), which `git merge-base --is-ancestor 8706d2ec4 19533373e` confirms is an **ancestor of P0.S1** — so it was already gone when this plan took its baseline. Found independently by two retrospective reviewers. MEASURED: zero integer `assertSame` in that file; zero `297 files` in `RepoMapBlock.php`; adding a `src/` file (298) leaves the census set at `OK (103 tests, 9432 assertions)` — nothing reds. `BuiltInToolCorpusTest.php:285-287` now carries `CENSUS_RESOLUTION = 'this file deliberately asserts NO cardinality over src/…'`. Phases 5/6/10 need **no** thin batches on this account; plan their concurrency from §2.1 like any other phase. Still an ordinary hot *file* if two steps edit it. See `prompt_plan.md` §17.1. |
| `sugar-crush/src/Backend/EngineBackend.php` | Held by an in-flight lane; wanted by this plan's P7.S3. **Also wanted by this plan's P6.S5b (the rules-into-the-prompt channel) - RE-CHECK before staffing S5b.** |
| `sugar-crush/src/Chat.php` + `sugar-crush/src/Context/ContextCompactor.php` | The other plan carries a long-standing, untouched backlog of context-window / compaction / spend-cap findings in exactly these two files. This plan's Phases 4 and 8 rewrite them. P6.S2 did not touch either file (verified: `git diff 0c31bd9a5 aff501a35 -- sugar-crush/src/Chat.php sugar-crush/src/Context/ContextCompactor.php` = 0 bytes). **P6.S3 EDITED `src/Chat.php` (the `rulesState` injector + `Chat::rulesState()`) and did NOT touch `ContextCompactor.php`; P6.S4 touched neither (`LayeredSettings.php`/`Bootstrap.php` + tests + docs, verified at `96e6577a3`); P6.S5a touched neither (`Triggers/` + `Skills/` + `src/Util/PathGlob.php` + the `Rule.php` doc-block + tests, verified over the merged 7-file delta); P6.S5b touches neither of THESE two but DOES want `EngineBackend.php` - see its own row.** |
| `sugar-crush/src/Tools/BuiltIn/Bash.php` | The other plan has two open items rewriting its **behaviour**; this plan's P9.S3 rewrites its **description**. |
| `sugar-crush/src/Agents/AgentDefinition.php` | The other plan's `C7` — `$defaultTools` is inert — gates what this plan's P7.S5 preset prompts are allowed to claim. |
| `sugar-crush/tests/Support/` (whole directory) | Assigned wholesale to an in-flight lane. This plan's P2.S4 wants `PromptFixture.php` there; P2.S4 carries the workaround. |
| `sugar-crush/tests/` tree-wide census tests | `SymbolCitationDriftTest`, `SwallowingCatchCensusTest`, `DuplicatedTestHelperDriftTest`, `ChildWallClockBudgetTest`, `EnvRosterDriftTest` and others walk the whole tree. **Every test file this plan adds can red one of them.** Several are owned by in-flight lanes. |

## 6. The loop you run, in short

Full detail in `prompt_plan.md` §1. The short form:

- Pick the next phase's next batch. Five steps at a time, chosen so their declared file lists are
  **disjoint**. Fewer when the phase does not offer five.
- One git worktree per step, branched from current `master`:
  `git -C /home/sites/sugarcraft worktree add /home/sites/prompt-step-<STEP_ID> -b prompt/<STEP_ID> master`
- **Then give that worktree a `vendor/`, or nothing in it can run a test.** `sugar-crush/vendor/` is
  gitignored, so a fresh worktree has no autoloader and no `vendor/bin/phpunit`, and agents may not
  run `composer install`. Hard-link it in and verify it points at the **worktree**:
  ```sh
  cp -al /home/sites/sugarcraft/sugar-crush/vendor \
         /home/sites/prompt-step-<STEP_ID>/sugar-crush/vendor
  cd /home/sites/prompt-step-<STEP_ID>/sugar-crush && php -r '
    $p = require "vendor/composer/autoload_psr4.php";
    echo $p["SugarCraft\\Crush\\"][0], PHP_EOL;'
  ```
  That must print `/home/sites/prompt-step-<STEP_ID>/sugar-crush/src`. **Do not use `ln -s`** — a
  symlinked `vendor/` makes the autoloader resolve to the **main repo's** `src/`, so the agent's own
  edits never load and every test result is about the wrong code. Full detail and the measurements:
  `prompt_plan.md` §1.2 action 2.
- Spawn the step agent with the step text, its file list, the `prompt_expand.md` sections it names,
  and `prompt_plan.md` §1.10, §1.11, §16 and §17.
- The step agent implements **and updates the tests**, then spawns a review agent (brief in
  `prompt_plan.md` §1.4). Findings → fix agent → **a brand-new** review agent → loop. Break only on a
  clean review. Cap five cycles, then the step is blocked.
- **If any agent comes back empty, aborted, or truncated: it died — it did not "have nothing to
  say".** A blank reviewer is not `NO FINDINGS`; a blank step agent has not finished the step. Work
  the ladder in `prompt_plan.md` §1.8: resume the same agent if you can, otherwise read its worktree
  (§1.8.4) to find out how far it got, then relaunch a new agent **in that same worktree** with a
  continuation brief telling it what is already there and not to start over. Blank returns get five
  attempts, not three. Never write the missing report yourself.
- You run the tests yourself and record **your** numbers.
- Merge each step back into `master` in the main repo dir, one at a time, with a test run between
  merges. Commit directly to `master` with the detailed message format in §1.6. **Do not push.**
- Remove the worktree.
- **Append the worklog entry and rewrite this file.** The step is not complete until you have.
- Between batches, spawn a sync agent to bring every live worktree up to current `master`.
- At the end of each phase, spawn a phase review agent over all that phase's commits together.
  Findings → fix → **a new** phase reviewer → loop. Cap three cycles.

## 6a. Harness note — these documents were written for OpenCode, not Claude Code

**Confirmed by the user, 2026-08-29.** `prompt_plan.md`, this file, and `prompt_worklog.md` were
authored assuming OpenCode as the harness. Follow their **substance** in full — the step loop, the
review→fix→**new**-reviewer cycles, disjoint declared file lists, one worktree per step, per-step
bookkeeping, measure-don't-assert, and never removing dormant code. Do **not** follow their
agent-handling **mechanics** literally when the harness is Claude Code.

Specifically, these do not apply here and should not be attempted:

- PTY handling of any kind, and the `pkill -f 'phpunit.*prompt-step-<ID>'` watchdog in
  `prompt_plan.md` §19.
- Judging an agent's liveness by transcript mtime or by hunting a pid, and killing it by pid
  (`prompt_plan.md` §1.8.6). Use the harness's own completion notification.
- Rung 1 of the recovery ladder (`prompt_plan.md` §1.8.3) as an OpenCode capability. Under Claude
  Code, continue a spawned agent with `SendMessage` addressed to that agent; if that is not
  possible, drop straight to rung 2 (read the worktree) and rung 3 (a new agent in the same
  worktree with a continuation brief).
- OpenCode's `task` vs `delegate` spawn routing. Use the normal Agent tool.

**And do not POLL a running agent.** When a spawned agent is working, produce **no output and take
no action** until the harness delivers its completion notification — it always does, with the
agent's full report. Do not emit a stream of short marker messages (`a1`, `a2`, `a3`… or any
`<letter><incrementing id>` sequence) every second or few seconds, do not re-run `ListAgents` on a
timer, do not read the agent's partial-output file to check on it, and do not schedule a short
wake-up to look again. The user has reported this filler **three separate times** across sessions;
it is pure waste, it buries real output, and because it looks like progress it disguises an
orchestrator that is doing nothing. Say once what you are waiting for and what you will do when it
lands, then stop. **This is the same substitution as the rest of §6a**: the OpenCode-era liveness
machinery in `prompt_plan.md` §1.8.6 and §19 exists because that harness had no completion
notification. Keep every rule about *what must be true* — a blank return means the agent DIED, is
never `NO FINDINGS`, and is never a finished step — and drop the polling.

Everything §1.8 says about *what must be true* still holds without change: a blank, truncated, or
aborted response means the agent **died** and is never a result; a reviewer that returns nothing has
**not** returned `NO FINDINGS`; the orchestrator runs its own tests and records its own numbers;
never write a dead agent's missing report yourself.

## 7. Non-negotiables

- Never modify `docs/plans/crush_code_*.md` or `left_steps.md`.
- Never touch `/home/sites/crush-lane-{a,b,c}`.
- Never `git push`, unless a push is genuinely required to complete a merge. If you think it is,
  stop and ask.
- Never run `caliber`. Never suppress a git hook (`--no-verify`, `core.hooksPath=/dev/null`).
- Never run a global `pkill`.
- Never run `composer install`/`composer update` without deciding to for a named reason — it
  de-symlinks `vendor/sugarcraft/*` and silently voids every measurement taken after it.
- Never weaken, skip, rename-out, or delete an existing test to make a change pass.
- **Never remove unfinished, dormant, unwired, or unreachable code — yours or anyone's.** Removal is
  not an outcome available to you or to any agent you spawn. The three permitted outcomes are: wire
  it, build it out, or **stop and ask the user**. This covers the quiet forms too — stubbing the
  body, dropping the last call site, deleting the enum case / parameter / config key that kept it
  alive, `@deprecated`-ing it aside, or deleting the test that pinned its dormancy. If an agent's
  diff removes one of these, reject it and re-spawn. Full rule: `prompt_plan.md` §1.10.
  **Escalating is a completed step, not a failed one** — record it in the worklog and in §8 below
  under `Awaiting user decision:`, verbatim, with `file:line`, what calls it (or that nothing does),
  and the options. Then wait for the user; do not decide it yourself.
- **Never accept an annotation or an existence check as a test.** `@covers`, `@test`, a descriptive
  method name, `method_exists()`, `class_exists()`, `is_callable()` and shape assertions
  (`assertNotNull`, `assertIsArray`, `assertTrue(count(...) > 0)`) all pass on wrong or absent
  behaviour. A real test calls the thing and asserts the value, asserts exact counts, covers both
  polarities and the pathological input — and goes **red when the change is reverted**. Require the
  step agent to state the deletion experiment it ran and what it showed. Full rule:
  `prompt_plan.md` §1.11 and §16.2.
- Never accept an agent's claim of completion without test output you ran yourself.
- **Never read an empty or aborted agent response as a result.** It means the agent died. Recover it
  (`prompt_plan.md` §1.8) — do not accept it, do not fill in what it would have said, and do not
  merge its worktree because the tests happen to be green.
- Never commit before confirming `user.name` / `user.email` are `Joe Huss` /
  `detain@interserver.net` (§4). A wrong author is silent and cannot be fixed afterwards
  without rewriting history.
- Never `ln -s` a worktree's `vendor/`. Use `cp -al` and verify the PSR-4 root (§6). A symlinked
  `vendor/` silently runs every test against the main repo's `src/`.
- Never delete a worktree without first checking it for uncommitted changes and unmerged commits
  (`prompt_plan.md` §1.12).
- Never write a worklog number you did not measure.
- **ORCHESTRATION-RULE-2 — no agent may create a scratch git repository anywhere but its own
  scratchpad, and must verify `pwd` before any `git init` / `git commit`.** ADDED 2026-08-30 after a
  P3.S5 reviewer ran a throwaway-repo setup inside `/home/sites/sugarcraft` itself: it OVERWROTE the
  repo's identity config to `t <a@b.c>` and left a stray commit on **master**. Repaired (identity
  restored, master reset, junk file gone, every plan commit verified still authored `Joe Huss`), and
  nothing was ever pushed — but §7 already warns that a wrong author is *"silent and cannot be fixed
  afterwards without rewriting history"*, and this was caught ONLY because the step agent
  self-reported it. **Put this prohibition in every step brief, and re-check
  `git config user.name` / `user.email` after every step, not only before committing.**
- **ORCHESTRATION-RULE-3 — every agent gets its OWN scratchpad subdirectory, named after its step,
  and may never `rm -rf` a path it did not create.** ADDED 2026-08-31. The session scratchpad is ONE
  FLAT SHARED DIRECTORY that every agent writes into; it held ~180 files from concurrent agents when
  this was found. A P3.S5-fix-1 reviewer opened its sandbox with an unconditional
  `rm -rf "$SB"; cp -al <worktree> "$SB"` where `$SB` was `.../scratchpad/sb` — a name it picked
  without checking — and self-reported that the `sb/` it destroyed had an mtime PREDATING its own
  work, so it almost certainly deleted a concurrent agent's sandbox mid-experiment. Two agents also
  both wrote `.../scratchpad/Runtime.orig.php` and `RT.orig.php`, and neither could afterwards tell
  whose copy survived. **That second one is the dangerous shape**: an agent that backs up a worktree
  file to a SHARED name, mutates the worktree, then restores from that name can restore ANOTHER
  agent's version of the file — a silent cross-contamination of a step's source that no test would
  attribute. So: every brief must name a private subdirectory (`<scratchpad>/<STEP_ID>/`), every
  backup and sandbox goes inside it, `rm -rf` is permitted only within it, and generic names
  (`sb`, `base`, `count.php`, `*.orig.php`) at the scratchpad ROOT are forbidden. The rule held only
  because the reviewer volunteered the collision — nothing detects it.
- Use `/usr/bin/grep` for anything that must see the whole tree — the shell's `grep` is `ugrep` and
  its recursive scans honour `.gitignore`.

## 8. Where you are right now

```
Phase:            Phase 6 CLOSED (42 of 65 done; 65 headings since the S5 split; six of seven merged S1,S2,S2b,S3,S4,S5a; NO P6.S6 exists). The cycle-1 close review PASSED - fresh read-only reviewer courageous-silver-woodpecker, verdict PHASE 6 MAY CLOSE, 0 BLOCKER / 0 MAJOR / 1 MINOR / 3 NIT; MINOR-1 (the stale withTriggers() line-cite) fixed in this close commit.
Next step:        P7.S1 - `HookResult::additionalContext` field + stop `HookRegistry::executeHooks()` discarding it (plan :2520; the Hooks subsystem). Cap `additionalContext` at 10,000 chars with spillover-to-a-file + preview; the reproduction test is the 200,000-byte hook. Touches ONLY `src/Hooks/HookResult.php` + `src/Hooks/HookRegistry.php` + their two tests - NO prompt golden, NO live collision row, so P7.S1 needs NO §5 re-check.
                  STANDING NOTES: P6.S5b is BLOCKED (§1.10) - do not brief/build/schedule it as a normal step. A §5 re-check is owed before P6.S5b AND before P7.S3 (both want `src/Backend/EngineBackend.php`, a live collision row) AND before Phase 8 (`Chat.php` + `ContextCompactor.php`). The PHASE 6 CLOSE REVIEW is DONE (passed cycle 1); its whole-phase lens found the trigger union still UNWIRED (PathTrigger zero production readers even after S5a widened its dialect), the `paths:`-scoped rule still rendering globally (the §18 row S5b owns), the `harness-injected` tag still defang-only with no emitter, and P6.S5b correctly UNBUILT (`src/Context/RulePathNudge.php` absent) - all PASS as combined-state facts, none a Phase-6 defect.
Steps done:       42 of 65.
Phases done:      7 of 12 (P0-P6 CLOSED; Phase 6 closed via its cycle-1 close review - PHASE 6 MAY CLOSE, 0 BLOCKER / 0 MAJOR / 1 MINOR / 3 NIT; the one MINOR-1 cite seam fixed in the close commit).
Last commit:      THIS Phase-6-close bookkeeping commit (subject "plan: Phase 6 CLOSED via cycle-1 close review (PHASE 6 MAY CLOSE); next is Phase 7 P7.S1") - over `785c0876d` (the prior close-review-next bookkeeping commit) - over the P6.S5a merge `505734f9f` (step commit `52de996fe`) - over caliber commit `1cf242f26`. This pass touches ONLY three markdown files (prompt_worklog.md, prompt_resume.md, prompt_plan.md) and ZERO sugar-crush/ paths, which is why the P6.S5a floor still describes master. Re-derive: git -C /home/sites/sugarcraft log --oneline -1
Baseline:         Tests: 10351, Assertions: 160648, Skipped: 1  (from P0.S1, NEVER edited)
Latest suite:     **Tests 10937 / Assertions 168399 / 0E / 0F / Skipped 2 / EXIT 0** - the orchestrator's INDEPENDENT gate in a FRESH detached worktree at `714bea7ec` (`cp -al` vendor, NEVER `ln -s`, PSR-4 proved printing the gate worktree's OWN `src`, serial, `</dev/null`, `--colors=never`, box-quiet probe 0 before the run, ONE run, Time 07:12.079), prediction `10937/168399` written BEFORE and HIT EXACTLY, per-class `cmp` 0 movers beyond the differential harness's own +1 assertion (the +14t/+468a floor delta is attributed in the worklog P6.S5a entry - 18 census movers summing EXACTLY), goldens UNMOVED (fixtures diff empty). The belt `git diff 714bea7ec 505734f9f -- sugar-crush/` is EMPTY, so the figure describes master; the Phase-6-close review is markdown-only, so re-deriving the belt to the NEW tip - `git diff 714bea7ec HEAD -- sugar-crush/` - still MUST print 0 (verified in the close commit), and the seven minutes are not owed. GOLDENS: system `f09f37366a1925565dcc7725f659ff41` (7,829 B - last MOVED at P6.S2 `aff501a35` by design, UNMOVED at S2b/S3/S4/S5a) and agent `ef0326dd38535aaa2f1d715919bff26e` (1,060 B - unmoved since `405252a41`); fixtures live at `sugar-crush/tests/fixtures/prompt/`. SUPERSEDES every prior floor (tree-named: `10923/167931` at `608914072` P6.S4, `10912/167830` at `e28a99b04` P6.S3, `10868/167297` at `506ef5f5e` P6.S2b, `10866/167264` at `aff501a35` P6.S2, `10832/166702` at `88fa18f77` P6.S1). STALE - do NOT quote as current without re-deriving: the nine-file census `OK (176 tests, 31840 assertions)` and the derived-roster solo `17/1107` both name `826564bdf`. CI: roster green-on-push by `8ea31a678`. UNPUSHED: RE-DERIVE, never trust the number (`16` measured after this Phase-6-close commit): git -C /home/sites/sugarcraft rev-list --count origin/master..master
In-flight batch:  NONE. No step agent is running and no step worktree exists. Write this field the moment you spawn a batch.
Live worktrees:   /home/sites/sugarcraft (master) ONLY - verified with `git worktree list`. The P6.S5a step worktree (`/home/sites/prompt-step-P6.S5a`) AND its disposable gate worktree (`/home/sites/prompt-gate-P6.S5a-c4`) are both removed; the branch was deleted with safe `-d` and `worktree remove` needed no `--force`. crush-lane-{a,b,c} and the repo-shared stash refs belong to the OTHER plan - NEVER touch. NOTE: local lib dirs are packagist REAL dirs in `sugar-crush/vendor`; CI re-injects symlinks via `check-path-repos --fix`; `composer update` ONLY with an orchestrator/user decision and a named reason.
Blocked on:       **P6.S5b** - BLOCKED on the §1.10 user decision quoted verbatim under `Awaiting user decision:` item (4) below; do not brief it, do not build it, do not schedule it as a normal step. Nothing else is blocked: the PHASE 6 CLOSE REVIEW is DONE (it PASSED cycle 1 - PHASE 6 MAY CLOSE), so Phase 7 opens at P7.S1.
Awaiting user decision: FOUR ITEMS, none of which blocked the (now-PASSED) PHASE 6 CLOSE REVIEW - carry all forward on every rewrite; do not decide any of them yourself. (1) THE 7 `.opencode/*` files (agents/coder.md, agents/reviewer.md, ocx.jsonc, plugins/notify.ts, plugins/notify/backend.ts, plugins/worktree/terminal.ts, skills/plan-review/SKILL.md; aggregate fingerprint md5 44f2d6aa3cb81b8562a799e29e2e4d43) - **NOW COMMITTED**: `1cf242f26` ("caliber update") consumed them, so master porcelain is EMPTY again; what REMAINS a user decision is their DISPOSITION (keep as committed, revert, or re-dirty deliberately). RE-MEASURE with `git status --porcelain` before ever calling them dirty again. (2) **PUSH AUTHORIZATION** - master is unpushed (measured **16** commits over `origin/master` after this Phase-6-close commit; RE-DERIVE, never trust that number). The earlier authorization was spent long ago; pushing re-greens the roster CI by `8ea31a678` design. (3) **GEMINI FUNCTION CALLING IS NOT BUILT** - `setTools()` is vendored and Gemini supports tool calling, but no shaper exists, so `supportsFunctionCalling()` honestly reports FALSE for Gemini and the request body carries no tools key. The supervisor's 2026-09-04 answer SCHEDULED follow-up step F7 (the Gemini tools shaper) instead of answering the question, so the queue is unblocked; **the decision itself remains unanswered**. (4) **P6.S5b's §1.10 ESCALATION - the rule-body transport question, verbatim:** "The 300-byte clip is unsound for rule bodies. The step's hard constraint transplants `MAX_ENTRY_BYTES = 300` from a channel that emits pointers (`SkillPathNudge.php:103-105`) into a surface whose own doc-block declares a truncated rule 'a half-instructed model' that must be refused **whole** (`RuleLoader.php:155-166`). Clipping a rule body is a semantic removal (§1.10). Options: count-bounded whole bodies (`MAX_MATCHED_RULES = k`, deferred), byte-bounded whole bodies with deferral, or a pointer-only nudge naming the rule and letting the model Read it. Decide before building." NOTE: P6.S5a's FIVE follow-ups are recorded in the worklog P6.S5a entry - they are ORCHESTRATOR-decidable (the four doc/test-hygiene ones are folded into `Open follow-ups` below; the fifth is the §5 re-check), NOT user escalations.
Open follow-ups:  (1) **P6.S5b aggregate exposure - its OWN step**: rules worst case measured 193 files / 12.06 MiB raw / 12,724,235 B emitted / 20,313,188 B after PromptFence's measured 1.6x escape blow-up, i.e. already 1.27-2.03x the 10,002,823-byte skills figure; whatever transport S5b gets, an aggregate bound is owed. (2) **`Rule::withTriggers()` (`Rule.php:285-298`) has zero production callers** - the 6th item on the dormancy roster; S5b or P7.S4 should ADOPT it (removal is never an outcome). (3) **Deferred guarded write door** for persisting a `/rules` toggle: its price is the three-census rewrite named in the plan's P6.S4 note - the `LayeredSettings.php:37-55` "EXACTLY TWO KEYS" docblock with its locator-pinned "two producers" lead-in, `ConfigWriteProducerDocumentationDriftTest`'s assert + DOORS + README guards, `ChatConfigChangeDoorsDocumentationDriftTest`'s four-doors roster and `docs/ENVIRONMENT.md:19`, a JSON encode/decode because `Bootstrap.php:1033`'s `onConfigChange` closure is `fn(string $key, string $value)`, plus a REPEAL of S3's byte-identical-config pin. (4) **The `$ownWords`/`$pathWords` LANDMINE (avoid, do NOT grow)**: `ProjectTierRefusalInventoryTest::$ownWords` tops at THIRTY-SEVEN holding exactly 37 (`:524-527`, guarded at `:539`); `$pathWords` tops at 25 and is consumed UNGUARDED (`:548`) so it CRASHES on overflow. Only a design that adds a new FILE dot-path trips them - `disabledRules` is a settings KEY, so P6.S4 never touched it and the inventory file passed unedited. (5) **Deferred refusal-message machinery**: `LayeredSettings::projectLayer()` `:626-634` filters through `only()` SILENTLY and its own docblock `:615-619` states "There is no channel to report through"; no `refusedKeys()` accessor exists. (6) `docs/COMMANDS.md`'s row count is on a STALE TRACK by design - it moves with any new registry row and NO guard catches it. (7) **UNPOLICED PROSE still stale in `docs/SETTINGS.md`**: `:516-520` says "five of the **ten** layered keys" while the same page said "eleven" before P6.S4 and `LAYERED_KEYS` is now TWELVE. Nothing derives either figure (the row-per-key guards iterate `LAYERED_KEYS`; no test counts rows or reads those sentences). A docs-sweep correction, not a landmine. (8) **F-GUARD** (`SymbolCitationDriftTest` is blind to `{@see self::testFoo()}` citations - widening it polices ~250 `self::` references at once and several red immediately), the maxims ordinal-prose refresh, F3/F4/F7/F8/F9, the S4/S5 nits, the travel ledger (buckets still have no response-path consumer), the REGEN LAW (`ensureFixtureRepo` FIRST + `pinHostLines`) for any golden regen, and **triggers-built-not-applied -> P6.S5b / P7.S4**. (9) **shared-fixture-farm HARD-LINK hazard, worth its OWN step**: `sugar-crush/vendor/prompt-fixture/*` is hard-linked (`links=12`) across the main checkout, every step worktree, `crush-lane-{a,b,c}` and every scratch copy, while `ensureFixtureRepo()` early-returns on `.git` (`:1677`) and `ensureFixtureUserHome()` on the `global-style.md` sentinel (`:1751`) - one tree's `removeTree()`/`copyTree()` can truncate another's fixtures. (10) **PROCESS LAWS** - (a) predict a suite headline from the FINAL tip's per-class solo evidence or LABEL it derived-not-predicted (P6.S4's headline prediction HIT exactly this way after P6.S1..S3 each missed a hand prediction); (b) **a completion notification is not a delivery** - verify the artifact on disk (five deaths this window: two empty read-only artifacts, a truncated scribe, a blank fixer, and a truncated fix-2 whose work HAD in fact committed); (c) **check dictated merge-message text for `<` and `>` YOURSELF before writing the brief** - an ASCII arrow on line 35 of the P6.S4 message carried a `>` and the merge agent correctly refused to merge. (11) **`prompt_kit/briefs/P6.S5a-step-brief.md:28` still cites the plan's motivating example at `:2371`; it moved to `:2406` with the split.** The brief is committed, so correct it in place when you staff S5a rather than rewriting the step. [STILL OPEN at the P6.S5a close - re-verified `:28` reads `:2371`; batch with (12)-(13) in the close review.] (12) **NEW (P6.S5a)** `SkillPathPatternTest.php:167` says the shipped translation matches "331 pairs"; measured over its OWN frozen `AFTER` const it is **378** (of 2,484). PRE-EXISTING, file not in S5a's diff; the new differential grid already guards the true figure - correct 331 to 378 or derive it. (13) **NEW (P6.S5a)** `docs/plans/crush_code_hardening_backlog.md:5491` still points at `SkillRegistry::compileClassBody()`, which P6.S5a moved to `PathGlob::classBody()` - same defect class cycle-2 fix 3 closed, in a tracked doc, invisible to `SymbolCitationDriftTest` (production-symbol citations are out of its alphabet). Batches with (12). (14) **NEW (P6.S5a)** `GlobDialectDifferentialTest.php:341` failure message cites `PathGlob.php:51` BY LINE NUMBER - a miniature of the line-pointer rot S5a fought; currently accurate, low priority; prefer citing the "proven identical over ..." clause. (15) **NEW (P6.S5a)** The "6 of the 13 / 1,451 / 214 / 40" red-capability figures are an EXPERIMENT, not a live derivation - re-measure whenever the harness's case count changes (unlike the corpus figure, intentionally NOT pinned by an assertion). (16) **NEW (PHASE 6 CLOSE REVIEW, cycle 1, 2026-09-05)** the close review (fresh read-only reviewer courageous-silver-woodpecker) returned **PHASE 6 MAY CLOSE** - 0 BLOCKER / 0 MAJOR / 1 MINOR / 3 NIT; all six deliverables PASS in the combined state and cross-step integrity checks A-F all PASS (see the worklog PHASE 6 CLOSE REVIEW entry). **MINOR-1 RESOLVED IN THIS CLOSE COMMIT**: the `withTriggers()` line-cite in open-follow-up (2) above (and in prompt_plan.md) was stale, pointing at the pre-S5a span; S5a's net +8-line `Rule.php` doc-block shifted the method body to **285-298** (doc-block 277-284), and both cites are corrected to 285-298 here. This is a legitimate cross-step catch - a per-step review could not have seen S5a's prose delta move an S2-era §8 pointer. **NIT-3 recorded so the record governs**: the P6.S5a orchestration brief said **"two"** `TriggerTest` pin flips; the correct measured figure is **FOUR across three methods** (the worklog P6.S5a entry already states four). NIT-1 (S2b + S3 merge subjects both read "step 3 of 6" - a same-day renumber artifact; history unamendable, records correct, no action) and NIT-2 (`505734f9f` carries a `plan:` prefix though it is a sugar-crush merge - cosmetic, no action) require no action. F(i) open-follow-up (12) `SkillPathPatternTest.php:167` "331"->378 and F(ii) open-follow-up (13) `docs/plans/crush_code_hardening_backlog.md:5491` dangling `SkillRegistry::compileClassBody()` remain rostered, neither dropped nor secretly fixed.
Sequencing gate:  CHECKED 2026-09-05 - supervisor cleared Phase 6 ("start P6.S1 now"); Phases 5-11 were cleared 2026-09-04 and Phase 6 again 2026-09-05. P6.S1/P6.S2/P6.S2b touched NO live collision row; P6.S3 edited `src/Chat.php` under the standing clearance (the precedent P4.S4 set) but NOT `ContextCompactor.php`; **P6.S4 touched NEITHER (`LayeredSettings.php`/`Bootstrap.php` + four tests + docs + `README.md` + `RulesState.php`, verified over the 10-file delta at `96e6577a3`)**; **P6.S5a touched NEITHER row** (`src/Context/Triggers/` + `src/Skills/` + `src/Util/PathGlob.php` + the `src/Context/Rule.php` doc-block + tests - verified over the merged 7-file delta). **PHASE 6 CLOSED via its cycle-1 close review (2026-09-05; PHASE 6 MAY CLOSE, 0/0/1 MINOR/3 NIT, MINOR-1 fixed in the close commit).** **RE-CHECK before P6.S5b AND before P7.S3 (both want `src/Backend/EngineBackend.php`, a live collision row) AND before Phase 8 (`Chat.php` + `ContextCompactor.php`); P7.S1 (Hooks only) needs NO re-check.** See §5.
Retro-review track: none.
```

**Phase 6 note (CLOSED via the cycle-1 close review - six of seven merged, the seventh P6.S5b BLOCKED).** P6.S1 shipped the trigger union (4 new `src/` classes, UNWIRED by design, no golden moved); P6.S2 shipped the rules tier and MOVED THE SYSTEM GOLDEN BY DESIGN (pure insertion +515 B) while leaving the agent golden on the same blob; P6.S2b closed the provenance-fence ROSTER half (`harness-injected`, the seventh `PromptFence::TAGS` entry, no emitter) without moving a golden byte; P6.S3 closed the rulebooks half - named toggleable packs + the `/rules` command - as a golden-zero-movement step; P6.S4 closed the config half - `disabledRules` as a user-tier layered key that seeds `RulesState` at launch - also golden-zero-movement, with `Rule::withEnabled()`'s adoption (not removal) satisfying §1.10 a second time; **P6.S5a closed the glob-dialect half - ONE shared compiler `src/Util/PathGlob.php` speaking the `SkillRegistry` fnmatch dialect, BOTH matchers routed through it, the live skill channel proven UNCHANGED over 130,317 derived comparisons while `PathTrigger`'s YES-set moved on 13/33 rows (10 widen / 3 narrow / 20 hold) - a third consecutive golden-zero-movement step, and the SkillRegistry change was a MOVE, not a removal (§1.10).** **There has NEVER been a `P6.S6`.** The plan has 65 step headings and Phase 6 has seven steps because the premise check (delivered 2026-09-05 on its fifth attempt, after four produced no artifact) SPLIT P6.S5 into **S5a** (one `paths:` glob dialect - MERGED at `505734f9f`) and **S5b** (glob-scoped rules reaching the model - BLOCKED on a §1.10 user decision about how rule bodies travel, carried verbatim in §8; it also wants `EngineBackend.php`, a live collision row, so re-check §5 before staffing it). S5b was SERIAL after S5a, which is done. **Phase 6 CLOSED via its standing PHASE REVIEW over P6.S1..P6.S5a's commits together - the cycle-1 close review (fresh read-only reviewer courageous-silver-woodpecker) returned PHASE 6 MAY CLOSE (0 BLOCKER / 0 MAJOR / 1 MINOR / 3 NIT; MINOR-1 fixed in the close commit); that review, not a phantom step, was the close.** P7.S4 owns rewiring `Skill::matchesPrompt()`; the deferred `harness-injected` WRAP MECHANICS still wait on a `PromptSection` composite shape. Carry the REGEN LAW (`ensureFixtureRepo` FIRST + `pinHostLines`) for any golden regen.

---

## §R. How to rewrite this file (read this before every rewrite)

**Rewrite this file after every single step, and after every phase close.** Not append — **replace**.
This file always describes the *current* state and the *next* action. History belongs in
`prompt_worklog.md`; if you find yourself adding a "previously…" section here, that content belongs
there instead.

### What must survive every rewrite, unchanged in substance

Sections **0, 1, 2, 6, 6a, 7** and this section **§R**. They are the operating instructions and they do not
change as the plan progresses. Copy them forward verbatim. If you find an error in them, fix it and
say so in the worklog entry for the step that fixed it.

### What you replace every time

- **The banner** at the top: change `Current state: NOT STARTED` to a one-line statement of where the
  plan is (e.g. `Current state: Phase 4, step P4.S3 in flight; phases 0–3 closed`).
- **§3** — once Phase 1 has landed, replace the lead-finding explanation with a short "what has been
  built so far" summary: the three or four things a fresh agent needs to know about the *current*
  shape of the code, not the original defect. Keep it under fifteen lines. When it grows past that,
  you are writing history; move it to the worklog.
- **§4 (Your first actions)** — becomes "how to resume": confirm the tree is clean, confirm the last
  step in `prompt_worklog.md` matches the last commit in `git log`, and if it does not, **reconstruct
  the missing entry before doing anything else** (`prompt_plan.md` §3.3).
- **§5 (the sequencing gate)** — once the gate has been checked and cleared, replace it with a
  one-line record of the decision and the date, plus any collision that is still live. If a
  collision is still live, keep the row that describes it.
- **§8 (Where you are right now)** — always rewritten. Every field, every time.

### The §8 block's required fields

```
Phase:            <current phase id and title, or "between phases">
Next step:        <STEP_ID> — <one-line goal>
Steps done:       <N> of 65
Phases done:      <N> of 12
Last commit:      <sha> — <subject line>
Baseline:         Tests: <N>, Assertions: <N>, Skipped: <N>  (from P0.S1, never edited)
Latest suite:     Tests: <N>, Assertions: <N>, Skipped: <N>  (from your last verification run)
Retro-review track: <the retrospective review agents currently out, their scopes, and where their
                  findings are queued — or "none". This track runs ALONGSIDE the plan and never
                  gates it; findings become fix steps scheduled between plan steps.>
In-flight batch:  <the batch id, its steps, their worktree paths, and the declared MERGE ORDER —
                  or "none". Write it the moment you spawn a batch, not when you start merging.>
Live worktrees:   <paths, or "none". Each one: the step it belongs to, and whether that step is
                  in flight, parked (§1.2 action 6), or stale (§1.12).>
Blocked on:       <nothing | STEP_ID and the standing findings, verbatim>
Awaiting user decision: <nothing | STEP_ID, the file:line, and the question, verbatim — the
                  dormant-code escalations from prompt_plan.md §1.10 that no agent may resolve>
Open follow-ups:  <the follow-ups recorded in worklog entries that are not yet scheduled>
Sequencing gate:  <CHECKED YYYY-MM-DD — decision | UNCHECKED>
```

The plan has **65** step headings (64 before the P6.S5a/P6.S5b split of 2026-09-05). Never carry a total you have not re-derived with `grep -c '^### P[0-9]' prompt_plan.md`.

`Baseline` is written once, at P0.S1, and never edited afterwards. It is the number every later delta
is measured against, and a moving baseline makes every delta meaningless.

### Rules for the rewrite

- **`In-flight batch` is the field that survives a session loss.** Every other field describes work
  that is already *finished* and therefore already recoverable from `git log` and the worklog. Five
  agents running in five worktrees with a merge order you decided in your head is recoverable from
  nothing. Write it at spawn time. Clear it at batch close, not before.
- **A fresh agent handed only this file must be able to continue.** After writing it, reread it as if
  you had never seen this repository. If a sentence assumes something you learned in conversation,
  rewrite the sentence.
- **Never write a "Next step" you have not confirmed against `prompt_plan.md`.** Look it up; do not
  recall it.
- **Never carry a stale number forward.** If you did not run the suite this step, say
  `Latest suite: not re-run this step (last measured at <STEP_ID>)` rather than repeating an old
  figure as if it were current.
- **If a step is blocked, the blocking findings go in §8 verbatim**, not summarised. The next agent
  needs the actual text to act on it.
- **A dormant-code escalation is never resolved by a rewrite.** It leaves `Awaiting user decision:`
  only when the user has answered, and the answer goes into the worklog entry for the step that acts
  on it. Carrying it forward unanswered, every rewrite, is correct.
- **If you are about to be cut off** (context limit, session limit), rewriting this file is the last
  thing you do and the highest-value thing you can do. Do it before anything else you were planning
  to finish.

 Task: Generate ANSI Terminal Art for SugarCraft

 Background

`detain/sugarcraft` is a monorepo of CLI/TUI libraries and tools, originally ported
from the Go Charmbracelet ecosystem. Each subdirectory corresponds to its own
`sugarcraft/<subdir>` GitHub repository and Packagist package.

Generate artistic ANSI graphics for use as logo/loading screens in terminal apps.

 Scale

- 20 targets × 10 agents = **200 agent launches** (not counting retries).
- Always **exactly 5 agents running concurrently**.
- Each agent generates **at least 18 .ansi files** (see per-agent quota below).
  Minimum ~3,600 files total.

 Targets (one subdirectory each under `ansi/`)

| Target | What it is (1 line — refer to each lib's README for full picture) |
|---|---|
| sugarcraft | The monorepo / brand itself — root README.md and docs/ |
| sugar-crush | Chat shell TUI for AI coding assistants |
| candy-files | Dual-pane terminal file manager |
| candy-forms | Foundational library for form primitives |
| candy-layout | Constraint-based layout solver (Cassowary) |
| candy-mines | Minesweeper TUI |
| candy-mosaic | Renders image to cells — Sixel/Kitty/half-blocks |
| candy-palette | Color profile detection + degradation (TrueColor→256→16→ASCII) |
| candy-query | Terminal SQLite browser — tables, line charts, stats, query builder |
| candy-sprinkles | Declarative terminal styling and layout (lipgloss) |
| candy-tetris | Tetris clone |
| honey-flap | Flappy-Bird-style game |
| honey-bounce | Damped spring physics + projectile simulation |
| sugar-calendar | Interactive date picker component |
| sugar-charts | Terminal charts (bar, line, heatmap, scatter, OHLC, sparkline) |
| sugar-dash | Dashboard TUI library (grids, datagrid, status bar, plots) |
| sugar-gallery | Poster grid / rail for media TUI |
| sugar-post | Send email from PHP (SMTP / Resend) |
| sugar-spark | ANSI escape sequence inspector |
| sugar-table | Customizable interactive table component |

Process targets strictly one at a time, in the order listed. Do not process two
targets simultaneously.

 Per-Agent Contract

The orchestrator launches each agent via the **task tool with `subagent_type:
coder`** and passes only parameters — the target product and that the art will
be used as a terminal logo/loading screen. All creative decisions are up to the
agent.

Each agent follows this:

1. **Research first** — before writing any art, read the target's
   `<slug>/README.md` (for `sugarcraft`, the root README + docs/) to get an
   actual sense of the product, not just the name.
2. **Scene, not label** — no mere bordered word art. Draw an image or scene:
   background detail, artistically styled lettering, occasional short colored
   text fragments related to the target.
3. **Name must be readable** — the product name should be discoverable in the
   art (stylized block letters, colored text, etc.).
4. **Conceptual art is fine too** — pieces do not need to depict real features.
   Example: for `honey-flap` any cool bird-themed scene works. For `candy-query`,
   draw the world of SQL — tables, line graphs, stats panels, query builders —
   artistically rendered.
5. **Characters and techniques** — Unicode box-drawing or UTF-8 glyphs may be
   used freely (including ▘▗▖▝ quarter-blocks, ▌▐▀▄ half-blocks, ░▒▓ shading,
   Braille, dingbats). Note that half-blocks double the effective vertical
   resolution per cell.
6. **Technical specs** — every file:
   - A real `.ansi` file with actual ESC (0x1B) bytes — never literal text
     `\x1b` / `\033` — terminated with a color/style reset.
   - Overall range 40×5 to 225×50 characters, falling into one of three
     size tiers:
     - **s (small):** 40–70 wide × 5–16 tall — banner/logo scale
     - **m (classic):** 70–90 wide × 18–26 tall — fills a classic 80×25
       terminal (approx. 75×22)
     - **l (large):** 120–225 wide × 28–50 tall — approaches fullscreen at 1080p
   - One of three color depths: **16**, **256**, or **truecolor** — and uses
     at least 5 distinct colors.
7. **Per-agent quota** — produce at least 2 at each size × each depth:

   | Depth | small | classic | large |
   |---|---|---|---|
   | 16-color | ≥2 | ≥2 | ≥2 |
   | 256-color | ≥2 | ≥2 | ≥2 |
   | truecolor | ≥2 | ≥2 | ≥2 |

   = minimum 18 files per agent. Additional pieces are welcome.
8. **File naming** — `<target>-<slot>-<depth>-<tier>-<i>.ansi`, where slot is
   the agent's index within the target (1–10), depth is `16`/`256`/`tc`, tier
   is `s`/`m`/`l`, i is 1..9. Example: `candy-query-3-256-m-1.ansi`. Save
   directly into the target's subdirectory (e.g. `ansi/candy-query/`).
9. **Self-verify before responding** — re-read each file from disk, confirm
   that dimensions, depth, and color count match what you report.
10. **Reply format** — exactly one line per file:
    `<filename> | <W>x<H> | <16|256|truecolor> | <N> colors | <1-line description>`

 Scheduling and Recovery

- Keep exactly 5 agents running at all times (2 waves of 5 per target, 10 agents
  total).
- **Empty/truncated responses are a common model error.** Resume the same agent
  via the task tool using its previous `task_id` — up to 10 resume attempts.
  On resume, first check what's already on disk and only produce the remaining
  quota.
- If the 10th resume also fails, abandon that agent and launch a fresh agent
  for the unfinished work.
- If some complete while others are being resumed, launch replacements only for
  the completed ones, so concurrency never exceeds 5.

 Progress Reporting

- After **every set of 5 agents**, update `ansi/README.md` with the files those
  agents reported.
- **Never commit.** No `git add`, no `git commit`, no `git push` at any point —
  leave all changes in the working tree.

 Final Pass

Once all 20 targets are complete:

1. Programmatically validate every `.ansi` file: escape bytes parse, dimensions
   match what was reported, color depth classification is correct, at least 5
   colors, reset at file end, non-empty.
2. Delete malformed files and remove them from `ansi/README.md`.
3. Finalize `ansi/README.md` — a complete validated listing organized by
   target, with dimensions, depth, color count, and description for each file.

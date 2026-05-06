<?php

declare(strict_types=1);

namespace CandyCore\Shine;

use CandyCore\Core\Util\Ansi;
use CandyCore\Core\Util\Width;
use CandyCore\Sprinkles\Border;
use CandyCore\Sprinkles\Table\Table as SprinklesTable;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\HtmlBlock;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;
use League\CommonMark\Extension\CommonMark\Node\Block\ThematicBreak;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Extension\CommonMark\Node\Inline\Emphasis;
use League\CommonMark\Extension\CommonMark\Node\Inline\HtmlInline;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;
use League\CommonMark\Extension\Strikethrough\Strikethrough;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\Table as MdTable;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\Table\TableRow;
use League\CommonMark\Extension\Table\TableSection;
use League\CommonMark\Extension\TaskList\TaskListExtension;
use League\CommonMark\Extension\TaskList\TaskListItemMarker;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Inline\Newline;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Parser\MarkdownParser;

/**
 * Markdown → ANSI renderer. Parses input with `league/commonmark` and
 * walks the resulting AST, producing styled strings via the {@see Theme}.
 *
 * Block-level nodes return text that already ends in trailing newlines;
 * inline nodes return inline fragments. Unknown node types fall back to
 * concatenating their children — graceful degradation for any extension
 * the renderer doesn't know about.
 */
final class Renderer
{
    public readonly Theme $theme;
    private readonly MarkdownParser $parser;
    private readonly ?int $wrapWidth;
    private readonly bool $emitHyperlinks;
    private readonly ?string $baseUrl;
    private readonly bool $tableWrap;
    private readonly bool $inlineTableLinks;
    private readonly bool $preservedNewLines;
    private bool $inTableCell = false;

    public function __construct(
        ?Theme $theme = null,
        ?int $wrapWidth = null,
        bool $emitHyperlinks = true,
        ?string $baseUrl = null,
        bool $tableWrap = false,
        bool $inlineTableLinks = true,
        bool $preservedNewLines = false,
    ) {
        $this->theme = $theme ?? Theme::ansi();
        $this->wrapWidth = ($wrapWidth !== null && $wrapWidth > 0) ? $wrapWidth : null;
        $this->emitHyperlinks = $emitHyperlinks;
        $this->baseUrl = $baseUrl;
        $this->tableWrap = $tableWrap;
        $this->inlineTableLinks = $inlineTableLinks;
        $this->preservedNewLines = $preservedNewLines;

        $env = new Environment();
        $env->addExtension(new CommonMarkCoreExtension());
        $env->addExtension(new TableExtension());
        $env->addExtension(new TaskListExtension());
        $env->addExtension(new StrikethroughExtension());
        $env->addExtension(new AutolinkExtension());
        $this->parser = new MarkdownParser($env);
    }

    public static function ansi(): self  { return new self(Theme::ansi());  }
    public static function plain(): self { return new self(Theme::plain()); }

    /**
     * Top-level convenience: render Markdown with a one-shot
     * `Renderer` instance and return the ANSI string. Mirrors
     * glamour's package-level `Render` function.
     *
     * Pass a Theme to pick a stock or custom theme; default ansi.
     * For repeated rendering with the same theme, build a Renderer
     * directly and reuse it (the parser is cached per instance).
     */
    public static function renderMarkdown(string $markdown, ?Theme $theme = null): string
    {
        return (new self($theme ?? Theme::ansi()))->render($markdown);
    }
    public static function ascii(): self { return new self(Theme::ascii()); }

    /**
     * Build a Renderer whose theme is selected by the `GLAMOUR_STYLE`
     * environment variable. Falls back to {@see Theme::ansi()} when the
     * env var is unset / unrecognised. Mirrors glamour's
     * `RenderWithEnvironmentConfig`.
     */
    public static function fromEnvironment(): self
    {
        return new self(Theme::fromEnvironment());
    }

    public function withTheme(Theme $theme): self
    {
        return $this->copy(theme: $theme);
    }

    /**
     * Wrap paragraph + blockquote + list-item bodies at `$cols` cells.
     * Code blocks and tables are never wrapped (they have their own
     * width semantics). Pass null or 0 to disable wrapping.
     *
     * Mirrors glamour's `WithWordWrap`.
     */
    public function withWordWrap(?int $cols): self
    {
        return $this->copy(wrapWidth: $cols, wrapWidthSet: true);
    }

    /**
     * Emit OSC 8 hyperlink escapes for `[text](url)` links so terminals
     * that support it render the text as a real clickable link. When
     * disabled (or the terminal doesn't support OSC 8), links degrade
     * to styled text plus a trailing ` (url)` suffix. Default: enabled.
     */
    public function withHyperlinks(bool $on = true): self
    {
        return $this->copy(emitHyperlinks: $on);
    }

    /**
     * Base URL prefixed onto relative `[text](path)` link / image targets.
     * URLs that already have a scheme (http://, https://, mailto:, …)
     * pass through unchanged. Mirrors glamour's `WithBaseURL`.
     */
    public function withBaseURL(?string $url): self
    {
        $url = $url === null || $url === '' ? null : rtrim($url, '/') . '/';
        return $this->copy(baseUrl: $url, baseUrlSet: true);
    }

    /**
     * Wrap text inside table cells at the renderer's word-wrap width.
     * Default off (cells render unwrapped, matching glamour's default).
     * Mirrors glamour's `WithTableWrap`.
     */
    public function withTableWrap(bool $on = true): self
    {
        return $this->copy(tableWrap: $on);
    }

    /**
     * Whether links inside table cells render as inline `[text](url)`
     * pairs (default — terse, scannable) or as full hyperlinks. When
     * false, table cells suppress the trailing `(url)` and OSC-8 envelope
     * since they bloat narrow columns. Mirrors glamour's
     * `WithInlineTableLinks`.
     */
    public function withInlineTableLinks(bool $on = true): self
    {
        return $this->copy(inlineTableLinks: $on);
    }

    /**
     * Preserve consecutive blank lines in source markdown. By default
     * CommonMark collapses runs of blank lines; with this on, every
     * `\n\n+` in the source survives into the output. Mirrors glamour's
     * `WithPreservedNewLines`.
     */
    public function withPreservedNewLines(bool $on = true): self
    {
        return $this->copy(preservedNewLines: $on);
    }

    /** @internal copy-with-overrides for chainable builders. */
    private function copy(
        ?Theme $theme = null,
        ?int $wrapWidth = null, bool $wrapWidthSet = false,
        ?bool $emitHyperlinks = null,
        ?string $baseUrl = null, bool $baseUrlSet = false,
        ?bool $tableWrap = null,
        ?bool $inlineTableLinks = null,
        ?bool $preservedNewLines = null,
    ): self {
        return new self(
            $theme            ?? $this->theme,
            $wrapWidthSet ? $wrapWidth : $this->wrapWidth,
            $emitHyperlinks   ?? $this->emitHyperlinks,
            $baseUrlSet ? $baseUrl : $this->baseUrl,
            $tableWrap        ?? $this->tableWrap,
            $inlineTableLinks ?? $this->inlineTableLinks,
            $preservedNewLines ?? $this->preservedNewLines,
        );
    }

    public function render(string $markdown): string
    {
        $document = $this->parser->parse($markdown);
        $rendered = $this->renderChildren($document);
        $rendered = rtrim($rendered, "\n");
        if ($this->theme->documentIndent > 0) {
            $indent = str_repeat(' ', $this->theme->documentIndent);
            $rendered = $indent . str_replace("\n", "\n" . $indent, $rendered);
        }
        if ($this->theme->documentMargin > 0) {
            $margin = str_repeat("\n", $this->theme->documentMargin);
            $rendered = $margin . $rendered . $margin;
        }
        if ($this->preservedNewLines) {
            // CommonMark collapses `\n\n+` to one blank-line break;
            // re-inflate by counting source blank lines and padding the
            // output. We approximate by matching `\n{3,}` runs in the
            // source and copying the count into the output where the
            // first single blank line lives.
            $sourceRuns = self::extractBlankRuns($markdown);
            if ($sourceRuns !== []) {
                $rendered = self::reapplyBlankRuns($rendered, $sourceRuns);
            }
        }
        return $rendered;
    }

    /**
     * Extract sequences of 3+ consecutive newlines from the source,
     * recording the count for each match. Used by
     * {@see withPreservedNewLines()} to re-inflate runs that CommonMark
     * collapses on parse.
     *
     * @return list<int> list of blank-line counts, in source order
     */
    private static function extractBlankRuns(string $source): array
    {
        $out = [];
        if (preg_match_all('/\n{3,}/', $source, $m) === false) {
            return $out;
        }
        foreach ($m[0] as $run) {
            $out[] = strlen($run) - 1; // n newlines = n-1 blank lines
        }
        return $out;
    }

    /**
     * Replace the first `len(runs)` `\n\n` separators in `$rendered`
     * with `\n` repeated by the matching run count.
     *
     * @param list<int> $runs
     */
    private static function reapplyBlankRuns(string $rendered, array $runs): string
    {
        $i = 0;
        return (string) preg_replace_callback(
            '/\n{2,}/',
            static function (array $m) use (&$i, $runs) {
                if ($i < count($runs)) {
                    $blanks = $runs[$i++];
                    return str_repeat("\n", $blanks);
                }
                return $m[0];
            },
            $rendered,
        );
    }

    private function renderNode(Node $node): string
    {
        return match (true) {
            $node instanceof Heading       => $this->renderHeading($node),
            $node instanceof Paragraph     => $this->renderParagraph($node),
            $node instanceof FencedCode    => $this->renderFencedCode($node) . "\n\n",
            $node instanceof IndentedCode  => $this->theme->codeBlock->render(rtrim($node->getLiteral(), "\n")) . "\n\n",
            $node instanceof BlockQuote    => $this->renderBlockQuote($node),
            $node instanceof ListBlock     => $this->renderList($node),
            $node instanceof ListItem      => $this->renderChildren($node),
            $node instanceof MdTable       => $this->renderTable($node),
            $node instanceof ThematicBreak => $this->theme->rule->render(
                str_repeat($this->theme->horizontalRuleGlyph, max(1, $this->theme->horizontalRuleLength))
            ) . "\n\n",
            $node instanceof Strong        => $this->theme->bold->render($this->renderChildren($node)),
            $node instanceof Emphasis      => $this->theme->italic->render($this->renderChildren($node)),
            $node instanceof Strikethrough => $this->renderStrike($node),
            $node instanceof Code          => $this->theme->code->render($node->getLiteral()),
            $node instanceof Link          => $this->renderLink($node),
            $node instanceof Image         => $this->renderImage($node),
            $node instanceof HtmlBlock     => $this->renderHtmlBlock($node),
            $node instanceof HtmlInline    => $this->renderHtmlSpan($node),
            $node instanceof TaskListItemMarker
                                          => $this->renderTaskMarker($node),
            $node instanceof Text          => $this->renderText($node->getLiteral()),
            $node instanceof Newline       => "\n",
            default                        => $this->renderChildren($node),
        };
    }

    private function renderText(string $literal): string
    {
        if ($this->theme->text !== null && !$this->isPlainStyle($this->theme->text)) {
            return $this->theme->text->render($literal);
        }
        return $literal;
    }

    private function isPlainStyle(\CandyCore\Sprinkles\Style $s): bool
    {
        return $s->render('x') === 'x';
    }

    private function renderStrike(Strikethrough $node): string
    {
        $body = $this->renderChildren($node);
        $style = $this->theme->strike ?? \CandyCore\Sprinkles\Style::new()->strikethrough();
        return $style->render($body);
    }

    private function renderImage(Image $node): string
    {
        $alt = $this->renderChildren($node);
        $url = $this->resolveUrl($node->getUrl());
        $imageStyle = $this->theme->image ?? \CandyCore\Sprinkles\Style::new()->italic();
        if ($alt === '') {
            $rendered = $imageStyle->render('[image]');
        } else {
            // imageText paints the visible alt-text when set; otherwise
            // fall through to image. Mirrors glamour's `ImageText` slot.
            $textStyle = $this->theme->imageText ?? $imageStyle;
            $rendered = $textStyle->render($alt);
        }
        return $rendered . ' (' . $url . ')';
    }

    /**
     * Apply {@see withBaseURL()} to a relative link / image target.
     * Absolute URLs (any scheme, or `//host/...`) pass through unchanged.
     */
    private function resolveUrl(string $url): string
    {
        if ($this->baseUrl === null || $url === '') {
            return $url;
        }
        if (preg_match('#^(?:[a-z][a-z0-9+.\-]*:|//)#i', $url) || str_starts_with($url, '#')) {
            // Has scheme, protocol-relative, or fragment-only — leave alone.
            return $url;
        }
        return $this->baseUrl . ltrim($url, '/');
    }

    private function renderHtmlBlock(HtmlBlock $node): string
    {
        $literal = rtrim($node->getLiteral(), "\n");
        $style = $this->theme->htmlBlock ?? \CandyCore\Sprinkles\Style::new();
        return $style->render($literal) . "\n\n";
    }

    private function renderHtmlSpan(HtmlInline $node): string
    {
        $style = $this->theme->htmlSpan ?? \CandyCore\Sprinkles\Style::new();
        return $style->render($node->getLiteral());
    }

    private function renderParagraph(Paragraph $node): string
    {
        $body = $this->renderChildren($node);
        if ($this->wrapWidth !== null) {
            $body = Width::wrapAnsi($body, $this->wrapWidth);
        }
        $body = $this->theme->paragraphPrefix . $body . $this->theme->paragraphSuffix;
        return $this->theme->paragraph->render($body) . "\n\n";
    }

    private function renderChildren(Node $parent): string
    {
        $out = '';
        foreach ($parent->children() as $child) {
            $out .= $this->renderNode($child);
        }
        return $out;
    }

    private function renderFencedCode(FencedCode $node): string
    {
        $body = rtrim($node->getLiteral(), "\n");
        $lang = trim($node->getInfo() ?? '');
        // No language hint → emit as plain code-block. With a hint,
        // route through the syntax highlighter; unknown languages
        // also fall through to the plain code-block style.
        if ($lang === '') {
            return $this->theme->codeBlock->render($body);
        }
        return SyntaxHighlighter::highlight($body, $lang, $this->theme);
    }

    private function renderHeading(Heading $h): string
    {
        $style = match ($h->getLevel()) {
            1       => $this->theme->heading1,
            2       => $this->theme->heading2,
            3       => $this->theme->heading3,
            4       => $this->theme->heading4,
            5       => $this->theme->heading5,
            default => $this->theme->heading6,
        };
        $prefix = $this->theme->headingPrefix
            ?? (str_repeat('#', $h->getLevel()) . ' ');
        $suffix = (string) $this->theme->headingSuffix;
        $body   = $this->applyCase($this->renderChildren($h), $this->theme->headingCase);
        return $style->render($prefix . $body . $suffix) . "\n\n";
    }

    /**
     * Apply a case transform to a heading body.
     *
     * Mirrors glamour's `Upper` / `Lower` / `Title` flags collapsed
     * into a single `case` selector. `none` (default) is identity;
     * unknown selectors fall through to identity.
     */
    private function applyCase(string $text, string $case): string
    {
        return match (strtolower($case)) {
            'upper' => mb_strtoupper($text, 'UTF-8'),
            'lower' => mb_strtolower($text, 'UTF-8'),
            'title' => mb_convert_case($text, MB_CASE_TITLE, 'UTF-8'),
            default => $text,
        };
    }

    private function renderBlockQuote(BlockQuote $q): string
    {
        $inner = rtrim($this->renderChildren($q), "\n");
        if ($this->wrapWidth !== null) {
            // Subtract 2 cells for the '▎ ' prefix.
            $inner = Width::wrapAnsi($inner, max(1, $this->wrapWidth - 2));
        }
        $lines = explode("\n", $inner);
        $out   = [];
        foreach ($lines as $line) {
            $out[] = $this->theme->blockquote->render('▎ ' . $line);
        }
        return implode("\n", $out) . "\n\n";
    }

    private function renderList(ListBlock $list): string
    {
        $data    = $list->getListData();
        $ordered = $data->type === ListBlock::TYPE_ORDERED;
        $start   = (int) ($data->start ?? 1);
        $marker  = $this->theme->listMarker;
        $levelIndent = max(0, $this->theme->listLevelIndent);

        $out = '';
        $i   = $start;
        foreach ($list->children() as $item) {
            $bullet = $ordered ? sprintf('%d.', $i) : '•';
            $body   = rtrim($this->renderChildren($item), "\n");
            // Paragraphs inside list items emit a trailing blank line for
            // top-level separation; collapse those runs so nested lists
            // sit directly under their parent rather than after a gap.
            $body = (string) preg_replace('/\n{2,}/', "\n", $body);

            $lines  = explode("\n", $body);
            $first  = array_shift($lines) ?? '';
            // Continuation indent: max of bullet width and configured
            // listLevelIndent so nested lists indent uniformly per
            // theme. Default levelIndent (4) matches glamour stock.
            $indentN = max(mb_strlen($bullet, 'UTF-8') + 1, $levelIndent);
            $indent  = str_repeat(' ', $indentN);

            // CommonMark softbreaks leave trailing whitespace on the
            // preceding Text node; rtrim every emitted line so item
            // bodies don't accumulate stray spaces.
            $out .= $marker->render($bullet) . ' ' . rtrim($first) . "\n";
            foreach ($lines as $line) {
                $line = rtrim($line);
                $out .= ($line === '' ? '' : $indent . $line) . "\n";
            }
            $i++;
        }
        return $out . "\n";
    }

    private function renderLink(Link $l): string
    {
        $text = $this->renderChildren($l);
        $url  = $this->resolveUrl($l->getUrl());
        $linkText = $this->theme->linkText ?? $this->theme->link;

        $insideTable = $this->inTableCell;
        $hyperlinks  = $this->emitHyperlinks && !($insideTable && !$this->inlineTableLinks);
        $showSuffix  = !$insideTable || $this->inlineTableLinks;

        if ($text === '' || $text === $url) {
            $rendered = $this->theme->link->render($url);
            return $hyperlinks
                ? Ansi::hyperlink($url, $rendered)
                : $rendered;
        }

        $styledText = $linkText->render($text);
        if ($hyperlinks) {
            return $showSuffix
                ? Ansi::hyperlink($url, $styledText) . ' (' . $url . ')'
                : Ansi::hyperlink($url, $styledText);
        }
        return $showSuffix ? $styledText . ' (' . $url . ')' : $styledText;
    }

    /**
     * GitHub-flavoured Markdown tables → Sprinkles Table with a rounded
     * border. The first TableSection (THEAD) becomes the headers; rows
     * inside the second section (TBODY) become body rows. Cell content
     * is rendered with the inline pipeline so emphasis / code / links
     * still pick up their styles.
     */
    private function renderTable(MdTable $table): string
    {
        $headers = [];
        $rows    = [];
        foreach ($table->children() as $section) {
            if (!$section instanceof TableSection) {
                continue;
            }
            $isHeader = $section->isHead();
            foreach ($section->children() as $row) {
                if (!$row instanceof TableRow) {
                    continue;
                }
                $cells = [];
                foreach ($row->children() as $cell) {
                    if (!$cell instanceof TableCell) {
                        continue;
                    }
                    $this->inTableCell = true;
                    try {
                        $body = rtrim($this->renderChildren($cell));
                    } finally {
                        $this->inTableCell = false;
                    }
                    if ($this->tableWrap && $this->wrapWidth !== null) {
                        $body = Width::wrapAnsi($body, $this->wrapWidth);
                    }
                    // Apply per-cell theme style (header vs body) when set.
                    $cellStyle = $isHeader
                        ? $this->theme->tableHeader
                        : $this->theme->tableCell;
                    if ($cellStyle !== null) {
                        $body = $cellStyle->render($body);
                    }
                    $cells[] = $body;
                }
                if ($isHeader) {
                    $headers = $cells;
                } else {
                    $rows[] = $cells;
                }
            }
        }
        $st = SprinklesTable::new()->border(Border::rounded());
        if ($headers !== []) {
            $st = $st->headers(...$headers);
        }
        foreach ($rows as $r) {
            $st = $st->row(...$r);
        }
        return $st->render() . "\n\n";
    }

    /**
     * Replace TaskListItemMarker (`[x]` / `[ ]`) with the matching glyph,
     * styled as a list marker. CommonMark parses the marker's trailing
     * space as part of the next Text node, so we don't add one here —
     * adding one would produce a double space before the body.
     */
    private function renderTaskMarker(TaskListItemMarker $marker): string
    {
        $glyph = $marker->isChecked()
            ? $this->theme->taskTickedGlyph
            : $this->theme->taskUntickedGlyph;
        return $this->theme->listMarker->render($glyph);
    }
}

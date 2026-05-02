<?php

declare(strict_types=1);

namespace CandyCore\Shine;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;
use League\CommonMark\Extension\CommonMark\Node\Block\ThematicBreak;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Extension\CommonMark\Node\Inline\Emphasis;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;
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

    public function __construct(?Theme $theme = null)
    {
        $this->theme = $theme ?? Theme::ansi();
        $env = new Environment();
        $env->addExtension(new CommonMarkCoreExtension());
        $this->parser = new MarkdownParser($env);
    }

    public static function ansi(): self  { return new self(Theme::ansi());  }
    public static function plain(): self { return new self(Theme::plain()); }

    public function withTheme(Theme $theme): self
    {
        return new self($theme);
    }

    public function render(string $markdown): string
    {
        $document = $this->parser->parse($markdown);
        $rendered = $this->renderChildren($document);
        return rtrim($rendered, "\n");
    }

    private function renderNode(Node $node): string
    {
        return match (true) {
            $node instanceof Heading       => $this->renderHeading($node),
            $node instanceof Paragraph     => $this->theme->paragraph->render($this->renderChildren($node)) . "\n\n",
            $node instanceof FencedCode    => $this->theme->codeBlock->render(rtrim($node->getLiteral(), "\n")) . "\n\n",
            $node instanceof IndentedCode  => $this->theme->codeBlock->render(rtrim($node->getLiteral(), "\n")) . "\n\n",
            $node instanceof BlockQuote    => $this->renderBlockQuote($node),
            $node instanceof ListBlock     => $this->renderList($node),
            $node instanceof ListItem      => $this->renderChildren($node),
            $node instanceof ThematicBreak => $this->theme->rule->render(str_repeat('─', 40)) . "\n\n",
            $node instanceof Strong        => $this->theme->bold->render($this->renderChildren($node)),
            $node instanceof Emphasis      => $this->theme->italic->render($this->renderChildren($node)),
            $node instanceof Code          => $this->theme->code->render($node->getLiteral()),
            $node instanceof Link          => $this->renderLink($node),
            $node instanceof Text          => $node->getLiteral(),
            $node instanceof Newline       => "\n",
            default                        => $this->renderChildren($node),
        };
    }

    private function renderChildren(Node $parent): string
    {
        $out = '';
        foreach ($parent->children() as $child) {
            $out .= $this->renderNode($child);
        }
        return $out;
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
        $prefix = str_repeat('#', $h->getLevel()) . ' ';
        return $style->render($prefix . $this->renderChildren($h)) . "\n\n";
    }

    private function renderBlockQuote(BlockQuote $q): string
    {
        $inner = rtrim($this->renderChildren($q), "\n");
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

        $out = '';
        $i   = $start;
        foreach ($list->children() as $item) {
            $bullet = $ordered ? sprintf('%d.', $i) : '•';
            $body   = rtrim($this->renderChildren($item), "\n");
            $out   .= $marker->render($bullet) . ' ' . $body . "\n";
            $i++;
        }
        return $out . "\n";
    }

    private function renderLink(Link $l): string
    {
        $text = $this->renderChildren($l);
        $url  = $l->getUrl();
        if ($text === '' || $text === $url) {
            return $this->theme->link->render($url);
        }
        return $this->theme->link->render($text) . ' (' . $url . ')';
    }
}

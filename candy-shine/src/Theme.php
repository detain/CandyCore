<?php

declare(strict_types=1);

namespace CandyCore\Shine;

use CandyCore\Core\Util\Color;
use CandyCore\Sprinkles\Style;

/**
 * A {@see Renderer} stylesheet — one {@see Style} per Markdown element
 * type. Themes are immutable; pick a built-in via {@see ansi()} /
 * {@see plain()} or build a custom one with the constructor.
 */
final class Theme
{
    public function __construct(
        public readonly Style $heading1,
        public readonly Style $heading2,
        public readonly Style $heading3,
        public readonly Style $heading4,
        public readonly Style $heading5,
        public readonly Style $heading6,
        public readonly Style $paragraph,
        public readonly Style $bold,
        public readonly Style $italic,
        public readonly Style $code,
        public readonly Style $codeBlock,
        public readonly Style $link,
        public readonly Style $blockquote,
        public readonly Style $listMarker,
        public readonly Style $rule,
    ) {}

    /** Default ANSI theme: bright accents on each heading, coloured code, etc. */
    public static function ansi(): self
    {
        $accent  = Color::hex('#ff5f87'); // pink
        $cyan    = Color::ansi(14);
        $yellow  = Color::ansi(11);
        $blue    = Color::ansi(12);
        $magenta = Color::ansi(13);
        $green   = Color::ansi(10);
        $grey    = Color::ansi(8);

        return new self(
            heading1:   Style::new()->bold()->foreground($accent),
            heading2:   Style::new()->bold()->foreground($cyan),
            heading3:   Style::new()->bold()->foreground($yellow),
            heading4:   Style::new()->bold()->foreground($blue),
            heading5:   Style::new()->bold()->foreground($magenta),
            heading6:   Style::new()->bold()->foreground($green),
            paragraph:  Style::new(),
            bold:       Style::new()->bold(),
            italic:     Style::new()->italic(),
            code:       Style::new()->foreground($accent),
            codeBlock:  Style::new()->faint(),
            link:       Style::new()->underline()->foreground($blue),
            blockquote: Style::new()->italic()->foreground($grey),
            listMarker: Style::new()->foreground($accent),
            rule:       Style::new()->foreground($grey),
        );
    }

    /** Plain-text theme: every Style is a no-op. Useful for snapshot tests. */
    public static function plain(): self
    {
        $s = Style::new();
        return new self(
            heading1: $s, heading2: $s, heading3: $s,
            heading4: $s, heading5: $s, heading6: $s,
            paragraph: $s, bold: $s, italic: $s,
            code: $s, codeBlock: $s, link: $s,
            blockquote: $s, listMarker: $s, rule: $s,
        );
    }
}

<?php

declare(strict_types=1);

namespace SugarCraft\Sprinkles;

/**
 * The 13 corner / edge / interior runes that make up a rectangular box
 * border. Outer runes drive Style boxes; the five middle-* runes drive
 * Table separators (column splits, row separators, cross intersections).
 *
 * Mirrors lipgloss `Border`. All runes must occupy a single terminal cell.
 */
final class Border
{
    public function __construct(
        public readonly string $top,
        public readonly string $bottom,
        public readonly string $left,
        public readonly string $right,
        public readonly string $topLeft,
        public readonly string $topRight,
        public readonly string $bottomLeft,
        public readonly string $bottomRight,
        public readonly string $middleLeft = ' ',
        public readonly string $middleRight = ' ',
        public readonly string $middle = ' ',
        public readonly string $middleTop = ' ',
        public readonly string $middleBottom = ' ',
    ) {}

    public static function normal(): self
    {
        return new self(
            '─', '─', '│', '│', '┌', '┐', '└', '┘',
            middleLeft: '├', middleRight: '┤', middle: '┼',
            middleTop: '┬', middleBottom: '┴',
        );
    }

    public static function rounded(): self
    {
        return new self(
            '─', '─', '│', '│', '╭', '╮', '╰', '╯',
            middleLeft: '├', middleRight: '┤', middle: '┼',
            middleTop: '┬', middleBottom: '┴',
        );
    }

    public static function thick(): self
    {
        return new self(
            '━', '━', '┃', '┃', '┏', '┓', '┗', '┛',
            middleLeft: '┣', middleRight: '┫', middle: '╋',
            middleTop: '┳', middleBottom: '┻',
        );
    }

    public static function double(): self
    {
        return new self(
            '═', '═', '║', '║', '╔', '╗', '╚', '╝',
            middleLeft: '╠', middleRight: '╣', middle: '╬',
            middleTop: '╦', middleBottom: '╩',
        );
    }

    public static function block(): self
    {
        return new self('█', '█', '█', '█', '█', '█', '█', '█');
    }

    public static function ascii(): self
    {
        return new self(
            '-', '-', '|', '|', '+', '+', '+', '+',
            middleLeft: '+', middleRight: '+', middle: '+',
            middleTop: '+', middleBottom: '+',
        );
    }

    public static function hidden(): self
    {
        return new self(' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ');
    }

    /**
     * GitHub-flavored Markdown table border. Mirrors lipgloss's
     * `Border::markdownBorder()` — pipes for the verticals, dashes
     * for the horizontals, plain `|` corners. Useful when an
     * already-rendered Sprinkles\Table needs to round-trip through a
     * Markdown reader without losing its grid.
     */
    public static function markdownBorder(): self
    {
        return new self(
            '-', '-', '|', '|', '|', '|', '|', '|',
            middleLeft: '|', middleRight: '|', middle: '|',
            middleTop: '|', middleBottom: '|',
        );
    }
}

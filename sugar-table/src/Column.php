<?php

declare(strict_types=1);

namespace CandyCore\Table;

/**
 * A table column with key, title, width, and optional style.
 *
 * Port of Evertras/bubble-table Column.
 *
 * @see https://github.com/Evertras/bubble-table
 */
final class Column
{
    public readonly string $key;        // unique identifier
    public readonly string $title;      // display header
    public readonly int $width;         // total cell width

    /** 0 = fixed, >0 = flexible width share */
    public readonly int $flexibleWidth;

    /** Hard cap for horizontal scrolling. 0 = no max. */
    public readonly int $maxWidth;

    /** Whether this column participates in filtering. */
    public readonly bool $filterable;

    /** Left-align instead of right-align. */
    public readonly bool $alignLeft;

    /** Column-level ANSI style. */
    public readonly string $style;

    private function __construct(
        string $key,
        string $title,
        int $width,
        int $flexibleWidth = 0,
        int $maxWidth = 0,
        bool $filterable = false,
        bool $alignLeft = false,
        string $style = '',
    ) {
        $this->key           = $key;
        $this->title         = $title;
        $this->width         = $width;
        $this->flexibleWidth = $flexibleWidth;
        $this->maxWidth      = $maxWidth;
        $this->filterable    = $filterable;
        $this->alignLeft     = $alignLeft;
        $this->style         = $style;
    }

    public static function new(string $key, string $title, int $width): self
    {
        return new self($key, $title, $width);
    }

    public function withFlexibleWidth(int $share): self
    {
        return new self($this->key, $this->title, $this->width, $share, $this->maxWidth, $this->filterable, $this->alignLeft, $this->style);
    }

    public function withMaxWidth(int $max): self
    {
        return new self($this->key, $this->title, $this->width, $this->flexibleWidth, $max, $this->filterable, $this->alignLeft, $this->style);
    }

    public function withFilterable(bool $v = true): self
    {
        return new self($this->key, $this->title, $this->width, $this->flexibleWidth, $this->maxWidth, $v, $this->alignLeft, $this->style);
    }

    public function withAlignLeft(bool $v = true): self
    {
        return new self($this->key, $this->title, $this->width, $this->flexibleWidth, $this->maxWidth, $this->filterable, $v, $this->style);
    }

    public function withStyle(string $ansiStyle): self
    {
        return new self($this->key, $this->title, $this->width, $this->flexibleWidth, $this->maxWidth, $this->filterable, $this->alignLeft, $ansiStyle);
    }

    /**
     * Build the header cell content, padded to $totalWidth.
     */
    public function renderHeader(int $totalWidth = 0): string
    {
        $w = $totalWidth > 0 ? $totalWidth : $this->width;
        $title = \substr($this->title, 0, $w);
        return $this->pad($title, $w, $this->alignLeft);
    }

    /**
     * Render a cell value with styling precedence: cell > row > column > base.
     *
     * @param mixed $value
     */
    public function renderCell(mixed $value, int $width = 0): string
    {
        $w = $width > 0 ? $width : $this->width;
        $str = \is_object($value) && method_exists($value, '__toString') ? (string) $value : (\is_scalar($value) ? (string) $value : '');

        $cellStr = $this->pad($str, $w, $this->alignLeft);
        if ($this->style !== '') {
            $cellStr = $this->ansi($cellStr, $this->style);
        }
        return $cellStr;
    }

    private function pad(string $text, int $width, bool $leftAlign): string
    {
        $len = \strlen($text);
        if ($len >= $width) return \substr($text, 0, $width);
        $pad = $width - $len;
        return $leftAlign
            ? $text . \str_repeat(' ', $pad)
            : \str_repeat(' ', $pad) . $text;
    }

    private function ansi(string $text, string $codes): string
    {
        if ($codes === '') return $text;
        return "\x1b[{$codes}m{$text}\x1b[0m";
    }
}

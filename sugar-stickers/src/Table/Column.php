<?php

declare(strict_types=1);

namespace SugarCraft\Stickers\Table;

/**
 * Column definition for a Table.
 *
 * Defines a single column: title, width, alignment, formatter, and optional sort.
 */
final class Column
{
    public readonly string $title;
    public readonly int $width;
    public readonly string $align;       // 'left' | 'center' | 'right'
    public readonly string $ansiStyle;   // ANSI style for header cells

    /** @var callable(string $value, int $rowIndex): string|null */
    private $formatter;

    /** Sort direction: +1 = asc, -1 = desc, 0 = none */
    private int $sortDir = 0;
    private int $sortPriority = 0;

    private function __construct(
        string $title,
        int $width,
        string $align = 'left',
        string $ansiStyle = '',
        ?callable $formatter = null,
    ) {
        $this->title     = $title;
        $this->width     = $width;
        $this->align     = $align;
        $this->ansiStyle = $ansiStyle;
        $this->formatter = $formatter;
    }

    public static function make(string $title, int $width): self
    {
        return new self($title, $width);
    }

    public function withAlign(string $align): self
    {
        $clone = clone $this;
        $clone->align = $align;
        return $clone;
    }

    public function withStyle(string $ansiStyle): self
    {
        $clone = clone $this;
        $clone->ansiStyle = $ansiStyle;
        return $clone;
    }

    public function withFormatter(callable $fn): self
    {
        $clone = clone $this;
        $clone->formatter = $fn;
        return $clone;
    }

    public function sorted(int $direction = 1, int $priority = 0): self
    {
        $clone = clone $this;
        $clone->sortDir     = $direction;
        $clone->sortPriority = $priority;
        return $clone;
    }

    public function unsorted(): self
    {
        $clone = clone $this;
        $clone->sortDir     = 0;
        $clone->sortPriority = 0;
        return $clone;
    }

    public function format(string $value, int $rowIndex): string
    {
        $result = ($this->formatter !== null)
            ? ($this->formatter)($value, $rowIndex)
            : $value;

        if ($result === null) {
            $result = $value;
        }

        return \substr((string) $result, 0, $this->width);
    }

    public function padded(string $value, int $rowIndex): string
    {
        $v = $this->format($value, $rowIndex);
        $len = \strlen($v);
        if ($len >= $this->width) {
            return \substr($v, 0, $this->width);
        }
        $pad = $this->width - $len;
        return match ($this->align) {
            'right'  => \str_repeat(' ', $pad) . $v,
            'center' => \str_repeat(' ', (int) \floor($pad / 2)) . $v . \str_repeat(' ', (int) \ceil($pad / 2)),
            default  => $v . \str_repeat(' ', $pad),
        };
    }

    public function sortDir(): int  { return $this->sortDir; }
    public function sortPriority(): int { return $this->sortPriority; }
}

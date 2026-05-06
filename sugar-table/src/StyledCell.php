<?php

declare(strict_types=1);

namespace CandyCore\Table;

/**
 * A cell with explicit ANSI style, overriding column/row styles.
 */
final class StyledCell
{
    public readonly mixed $value;
    public readonly string $style;

    public function __construct(mixed $value, string $ansiStyle = '')
    {
        $this->value = $value;
        $this->style = $ansiStyle;
    }

    public static function new(mixed $value, string $ansiStyle = ''): self
    {
        return new self($value, $ansiStyle);
    }

    public function withStyle(string $ansiStyle): self
    {
        return new self($this->value, $ansiStyle);
    }

    public function __toString(): string
    {
        $str = \is_object($this->value) && method_exists($this->value, '__toString')
            ? (string) $this->value
            : (\is_scalar($this->value) ? (string) $this->value : '');

        if ($this->style !== '') {
            return "\x1b[{$this->style}m{$str}\x1b[0m";
        }
        return $str;
    }
}

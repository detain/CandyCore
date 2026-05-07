<?php

declare(strict_types=1);

namespace SugarCraft\Table;

/**
 * A single table row wrapping RowData + optional styles.
 *
 * Port of Evertras/bubble-table Row.
 *
 * @see https://github.com/Evertras/bubble-table
 */
final class Row
{
    public readonly RowData $data;
    public readonly string $style;       // row-level ANSI style
    public readonly bool $zebra;         // apply zebra striping
    public readonly ?int $zebraStyleIndex; // which alternating style

    private function __construct(
        RowData $data,
        string $style = '',
        bool $zebra = false,
        ?int $zebraStyleIndex = null,
    ) {
        $this->data             = $data;
        $this->style            = $style;
        $this->zebra            = $zebra;
        $this->zebraStyleIndex  = $zebraStyleIndex;
    }

    public static function new(RowData $data): self
    {
        return new self($data);
    }

    public function withStyle(string $ansiStyle): self
    {
        return new self($this->data, $ansiStyle, $this->zebra, $this->zebraStyleIndex);
    }

    public function withZebra(int $styleIndex = 0): self
    {
        return new self($this->data, $this->style, true, $styleIndex);
    }
}

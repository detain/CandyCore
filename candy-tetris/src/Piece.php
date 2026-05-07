<?php

declare(strict_types=1);

namespace SugarCraft\Tetris;

/**
 * A live (falling) Tetris piece. Immutable: every transform —
 * `withX()`, `rotated()`, `dropped()` — returns a fresh instance,
 * which keeps the {@see Game} update loop pure and trivially
 * testable.
 *
 * Cells are computed lazily by deferring to
 * {@see Tetromino::cells()} and offsetting by `$x` / `$y`.
 */
final class Piece
{
    public function __construct(
        public readonly Tetromino $kind,
        public readonly int       $rotation = 0,
        public readonly int       $x = 3,
        public readonly int       $y = 0,
    ) {}

    public function withX(int $x): self
    {
        return new self($this->kind, $this->rotation, $x, $this->y);
    }

    public function withY(int $y): self
    {
        return new self($this->kind, $this->rotation, $this->x, $y);
    }

    public function rotated(int $delta = 1): self
    {
        return new self($this->kind, ((($this->rotation + $delta) % 4) + 4) % 4, $this->x, $this->y);
    }

    public function moved(int $dx, int $dy): self
    {
        return new self($this->kind, $this->rotation, $this->x + $dx, $this->y + $dy);
    }

    /**
     * Cells of this piece in absolute board coordinates.
     *
     * @return list<array{int,int}>
     */
    public function cells(): array
    {
        $out = [];
        foreach ($this->kind->cells($this->rotation) as [$dx, $dy]) {
            $out[] = [$this->x + $dx, $this->y + $dy];
        }
        return $out;
    }
}

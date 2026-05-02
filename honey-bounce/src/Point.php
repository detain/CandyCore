<?php

declare(strict_types=1);

namespace CandyCore\Bounce;

/**
 * 2D point. Same shape as {@see Vector}; kept as a distinct type for
 * the same reason harmonica does — semantically a position vs. a delta.
 */
final class Point
{
    public function __construct(
        public readonly float $x,
        public readonly float $y,
    ) {}

    public static function zero(): self
    {
        return new self(0.0, 0.0);
    }

    /** Add a {@see Vector} delta to this point, returning a new Point. */
    public function add(Vector $v): self
    {
        return new self($this->x + $v->x, $this->y + $v->y);
    }
}

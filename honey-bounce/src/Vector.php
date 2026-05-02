<?php

declare(strict_types=1);

namespace CandyCore\Bounce;

/**
 * 2D vector for {@see Projectile}. Immutable.
 *
 * Mirrors the harmonica `Vector` value type — used both for positions
 * (X / Y in arbitrary units) and for velocities / accelerations (units
 * per simulation step).
 */
final class Vector
{
    public function __construct(
        public readonly float $x,
        public readonly float $y,
    ) {}

    public static function zero(): self
    {
        return new self(0.0, 0.0);
    }

    public function add(self $other): self
    {
        return new self($this->x + $other->x, $this->y + $other->y);
    }

    public function sub(self $other): self
    {
        return new self($this->x - $other->x, $this->y - $other->y);
    }

    /** Scale both components by `$s`. */
    public function scale(float $s): self
    {
        return new self($this->x * $s, $this->y * $s);
    }

    /** Euclidean length. */
    public function length(): float
    {
        return sqrt($this->x * $this->x + $this->y * $this->y);
    }
}

<?php

declare(strict_types=1);

namespace CandyCore\Bounce;

/**
 * Newtonian-physics projectile. Ports `harmonica.NewProjectile`.
 *
 * Each call to {@see update()} advances position by velocity and
 * velocity by acceleration; acceleration usually carries the gravity
 * vector. Use this for arcs / bouncing balls / particle-like animation.
 *
 * `$deltaTime` is the simulation step bake-in matching {@see Spring}'s
 * convention — pass the same value for every update().
 *
 * ```php
 * $p = Projectile::new(
 *     deltaTime: Spring::fps(60),
 *     position:  Point::zero(),
 *     velocity:  new Vector(5.0, -10.0),
 *     accel:     Projectile::gravity(),
 * );
 * for ($i = 0; $i < 60; $i++) {
 *     $p = $p->update();
 * }
 * ```
 */
final class Projectile
{
    /** Standard gravity (m/s² × cell-units, scaled). Mirrors `harmonica.Gravity`. */
    public const GRAVITY = 9.81;

    /** Approximate skydiver terminal velocity. Mirrors `harmonica.TerminalGravity`. */
    public const TERMINAL_GRAVITY = 53.0;

    public function __construct(
        public readonly float  $deltaTime,
        public readonly Point  $position,
        public readonly Vector $velocity,
        public readonly Vector $acceleration,
    ) {}

    public static function new(
        float  $deltaTime,
        Point  $position,
        Vector $velocity,
        Vector $acceleration,
    ): self {
        return new self($deltaTime, $position, $velocity, $acceleration);
    }

    /**
     * Advance the simulation by one step. Returns a new Projectile —
     * the original is untouched. Position += velocity·dt; velocity +=
     * acceleration·dt.
     */
    public function update(): self
    {
        $newPos = $this->position->add($this->velocity->scale($this->deltaTime));
        $newVel = $this->velocity->add($this->acceleration->scale($this->deltaTime));
        return new self($this->deltaTime, $newPos, $newVel, $this->acceleration);
    }

    public function position(): Point      { return $this->position; }
    public function velocity(): Vector     { return $this->velocity; }
    public function acceleration(): Vector { return $this->acceleration; }

    /** Convenience: gravity vector pointing in `+Y` (standard "down"). */
    public static function gravity(): Vector
    {
        return new Vector(0.0, self::GRAVITY);
    }

    /** Convenience: terminal-velocity gravity vector. */
    public static function terminalGravity(): Vector
    {
        return new Vector(0.0, self::TERMINAL_GRAVITY);
    }
}

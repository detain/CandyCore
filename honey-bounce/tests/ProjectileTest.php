<?php

declare(strict_types=1);

namespace CandyCore\Bounce\Tests;

use CandyCore\Bounce\Point;
use CandyCore\Bounce\Projectile;
use CandyCore\Bounce\Spring;
use CandyCore\Bounce\Vector;
use PHPUnit\Framework\TestCase;

final class ProjectileTest extends TestCase
{
    public function testZeroVelocityZeroAccelerationStaysPut(): void
    {
        $p = Projectile::new(
            deltaTime:    1.0,
            position:     new Point(5.0, 10.0),
            velocity:     Vector::zero(),
            acceleration: Vector::zero(),
        );
        $next = $p->update();
        $this->assertSame(5.0,  $next->position->x);
        $this->assertSame(10.0, $next->position->y);
        $this->assertSame(0.0,  $next->velocity->x);
        $this->assertSame(0.0,  $next->velocity->y);
    }

    public function testConstantVelocityAdvancesByVelocityTimesDt(): void
    {
        $p = Projectile::new(
            deltaTime:    0.5,
            position:     Point::zero(),
            velocity:     new Vector(2.0, 4.0),
            acceleration: Vector::zero(),
        );
        $next = $p->update();
        $this->assertSame(1.0, $next->position->x);  // 0 + 2 * 0.5
        $this->assertSame(2.0, $next->position->y);  // 0 + 4 * 0.5
        // Velocity unchanged because acceleration is zero.
        $this->assertSame(2.0, $next->velocity->x);
        $this->assertSame(4.0, $next->velocity->y);
    }

    public function testGravityAccelerationAddsToVelocity(): void
    {
        $p = Projectile::new(
            deltaTime:    1.0,
            position:     Point::zero(),
            velocity:     Vector::zero(),
            acceleration: Projectile::gravity(),
        );
        $next = $p->update();
        // After 1s of gravity: velocity y = 9.81, position y = 0 (vel was 0 at start of step).
        $this->assertSame(0.0, $next->position->y);
        $this->assertEqualsWithDelta(9.81, $next->velocity->y, 1e-6);
    }

    public function testTerminalGravity(): void
    {
        $tg = Projectile::terminalGravity();
        $this->assertEqualsWithDelta(53.0, $tg->y, 1e-9);
    }

    public function testProjectileFiveStepsAtFps60(): void
    {
        $dt = Spring::fps(60);
        $p = Projectile::new(
            deltaTime:    $dt,
            position:     Point::zero(),
            velocity:     new Vector(0.0, -10.0),  // upward
            acceleration: Projectile::gravity(),    // pulls down
        );
        // Walk 60 frames (1 simulated second) and check the final
        // velocity has approached zero (net deceleration ~9.81 over 1s).
        for ($i = 0; $i < 60; $i++) {
            $p = $p->update();
        }
        // Initial v=-10, after 1s of +9.81 accel: v ≈ -0.19
        $this->assertEqualsWithDelta(-0.19, $p->velocity->y, 0.05);
    }

    public function testVectorMath(): void
    {
        $a = new Vector(1.0, 2.0);
        $b = new Vector(3.0, 4.0);
        $sum = $a->add($b);
        $this->assertSame(4.0, $sum->x);
        $this->assertSame(6.0, $sum->y);
        $diff = $b->sub($a);
        $this->assertSame(2.0, $diff->x);
        $this->assertSame(2.0, $diff->y);
        $scaled = $a->scale(3.0);
        $this->assertSame(3.0, $scaled->x);
        $this->assertSame(6.0, $scaled->y);
        $this->assertEqualsWithDelta(5.0, $b->length(), 1e-9);
    }

    public function testPointAddVector(): void
    {
        $p = new Point(10.0, 20.0);
        $v = new Vector(1.0, 2.0);
        $result = $p->add($v);
        $this->assertSame(11.0, $result->x);
        $this->assertSame(22.0, $result->y);
    }

    public function testGravityConstants(): void
    {
        $this->assertEqualsWithDelta(9.81, Projectile::GRAVITY,          1e-9);
        $this->assertEqualsWithDelta(53.0, Projectile::TERMINAL_GRAVITY, 1e-9);
    }
}

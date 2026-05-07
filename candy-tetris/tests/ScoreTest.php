<?php

declare(strict_types=1);

namespace SugarCraft\Tetris\Tests;

use SugarCraft\Tetris\Score;
use PHPUnit\Framework\TestCase;

final class ScoreTest extends TestCase
{
    public function testInitialState(): void
    {
        $s = new Score();
        $this->assertSame(0, $s->points);
        $this->assertSame(0, $s->lines);
        $this->assertSame(0, $s->level);
    }

    public function testSingleLineScoreAtLevelZero(): void
    {
        $s = (new Score())->withLines(1);
        $this->assertSame(40, $s->points);
        $this->assertSame(1, $s->lines);
    }

    public function testTetrisScoreAtLevelZero(): void
    {
        $s = (new Score())->withLines(4);
        $this->assertSame(1200, $s->points);
        $this->assertSame(4, $s->lines);
    }

    public function testScoreScalesWithLevel(): void
    {
        $s = (new Score(0, 0, 5))->withLines(2);
        // 100 × (5+1) = 600
        $this->assertSame(600, $s->points);
    }

    public function testLevelRisesEveryTenLines(): void
    {
        $s = (new Score())->withLines(4)->withLines(4)->withLines(4);
        // 12 lines → level 1
        $this->assertSame(12, $s->lines);
        $this->assertSame(1, $s->level);
    }

    public function testWithLinesZeroIsNoop(): void
    {
        $s = new Score(100, 5, 0);
        $this->assertSame($s, $s->withLines(0));
    }

    public function testGravityAcceleratesWithLevel(): void
    {
        $level0 = new Score(0, 0, 0);
        $level9 = new Score(0, 0, 9);
        $level29 = new Score(0, 0, 29);
        $this->assertGreaterThan(
            $level9->framesPerRow(),
            $level0->framesPerRow(),
        );
        $this->assertSame(1, $level29->framesPerRow());
    }
}

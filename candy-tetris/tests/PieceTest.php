<?php

declare(strict_types=1);

namespace CandyCore\Tetris\Tests;

use CandyCore\Tetris\Piece;
use CandyCore\Tetris\Tetromino;
use PHPUnit\Framework\TestCase;

final class PieceTest extends TestCase
{
    public function testCellsAreOffsetByPiecePosition(): void
    {
        $p = new Piece(Tetromino::T, 0, x: 5, y: 3);
        $cells = $p->cells();
        $this->assertContains([6, 3], $cells); // [1,0] + (5,3) = (6,3)
        $this->assertContains([5, 4], $cells); // [0,1] + (5,3)
    }

    public function testWithersReturnNewInstance(): void
    {
        $a = new Piece(Tetromino::T, x: 0, y: 0);
        $b = $a->withX(7);
        $c = $b->withY(2);
        $d = $c->rotated();
        $this->assertSame(0, $a->x, 'original Piece must remain unchanged');
        $this->assertSame(7, $b->x);
        $this->assertSame(2, $c->y);
        $this->assertSame(1, $d->rotation);
        $this->assertSame(7, $d->x);
    }

    public function testRotationWrapsModulo4(): void
    {
        $p = new Piece(Tetromino::T);
        $this->assertSame(0, $p->rotation);
        $this->assertSame(1, $p->rotated(1)->rotation);
        $this->assertSame(0, $p->rotated(4)->rotation);
        $this->assertSame(3, $p->rotated(-1)->rotation);
        $this->assertSame(0, $p->rotated(-4)->rotation);
    }

    public function testMovedAddsDelta(): void
    {
        $p = new Piece(Tetromino::T, 0, 5, 3);
        $q = $p->moved(-2, 4);
        $this->assertSame(3, $q->x);
        $this->assertSame(7, $q->y);
    }
}

<?php

declare(strict_types=1);

namespace CandyCore\Tetris\Tests;

use CandyCore\Tetris\Bag;
use CandyCore\Tetris\Tetromino;
use PHPUnit\Framework\TestCase;

final class BagTest extends TestCase
{
    public function testFirstSevenPiecesContainEachKindExactlyOnce(): void
    {
        // Deterministic RNG: always pick the smallest index to make the test
        // independent of random_int's runtime sequence.
        $bag = new Bag(static fn(int $max): int => 0);
        $seen = [];
        for ($i = 0; $i < 7; $i++) {
            $seen[] = $bag->next()->value;
        }
        sort($seen);
        $this->assertSame(['I', 'J', 'L', 'O', 'S', 'T', 'Z'], $seen);
    }

    public function testTwoBagsContainEachKindTwice(): void
    {
        $bag = new Bag(static fn(int $max): int => 0);
        $seen = [];
        for ($i = 0; $i < 14; $i++) {
            $seen[] = $bag->next()->value;
        }
        $counts = array_count_values($seen);
        foreach (Tetromino::cases() as $t) {
            $this->assertSame(2, $counts[$t->value], "expected exactly two of {$t->value}");
        }
    }

    public function testPeekDoesNotConsume(): void
    {
        $bag = new Bag(static fn(int $max): int => 0);
        $peeked = $bag->peek(3);
        $this->assertCount(3, $peeked);
        $drawn = [$bag->next(), $bag->next(), $bag->next()];
        $this->assertSame($peeked, $drawn);
    }

    public function testPeekRefillsAcrossBagBoundary(): void
    {
        $bag = new Bag(static fn(int $max): int => 0);
        // Drain one bag.
        for ($i = 0; $i < 7; $i++) {
            $bag->next();
        }
        $next3 = $bag->peek(3);
        $this->assertCount(3, $next3);
        foreach ($next3 as $t) {
            $this->assertInstanceOf(Tetromino::class, $t);
        }
    }
}

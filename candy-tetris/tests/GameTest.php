<?php

declare(strict_types=1);

namespace CandyCore\Tetris\Tests;

use CandyCore\Core\KeyType;
use CandyCore\Core\Msg\KeyMsg;
use CandyCore\Tetris\Bag;
use CandyCore\Tetris\Game;
use CandyCore\Tetris\GravityMsg;
use PHPUnit\Framework\TestCase;

final class GameTest extends TestCase
{
    private function deterministicGame(): Game
    {
        return Game::start(new Bag(static fn(int $max): int => 0));
    }

    public function testStartSpawnsFirstPiece(): void
    {
        $g = $this->deterministicGame();
        $this->assertNotNull($g->piece);
        $this->assertFalse($g->over);
        $this->assertFalse($g->paused);
    }

    public function testInitReturnsTickClosure(): void
    {
        $g = $this->deterministicGame();
        $cmd = $g->init();
        $this->assertInstanceOf(\Closure::class, $cmd);
    }

    public function testQuitKeyDispatchesQuit(): void
    {
        $g = $this->deterministicGame();
        [, $cmd] = $g->update(new KeyMsg(KeyType::Char, 'q'));
        $this->assertInstanceOf(\Closure::class, $cmd, 'q must dispatch a quit Cmd');
    }

    public function testLeftKeyMovesPieceLeft(): void
    {
        $g = $this->deterministicGame();
        $startX = $g->piece->x;
        [$next] = $g->update(new KeyMsg(KeyType::Left, ''));
        $this->assertSame($startX - 1, $next->piece->x);
    }

    public function testRightKeyMovesPieceRight(): void
    {
        $g = $this->deterministicGame();
        $startX = $g->piece->x;
        [$next] = $g->update(new KeyMsg(KeyType::Right, ''));
        $this->assertSame($startX + 1, $next->piece->x);
    }

    public function testUpKeyRotatesPiece(): void
    {
        $g = $this->deterministicGame();
        $startRot = $g->piece->rotation;
        [$next] = $g->update(new KeyMsg(KeyType::Up, ''));
        $this->assertSame(($startRot + 1) % 4, $next->piece->rotation);
    }

    public function testGravityAdvancesPieceDownOneRow(): void
    {
        $g = $this->deterministicGame();
        $startY = $g->piece->y;
        [$next, $cmd] = $g->update(new GravityMsg());
        $this->assertSame($startY + 1, $next->piece->y);
        $this->assertInstanceOf(\Closure::class, $cmd, 'gravity must reschedule the next tick');
    }

    public function testHardDropDispatchesNextTick(): void
    {
        $g = $this->deterministicGame();
        [$next, $cmd] = $g->update(new KeyMsg(KeyType::Char, ' '));
        // Piece locked + new piece spawned. Next-tick Cmd reschedules gravity.
        $this->assertNotSame($g->piece, $next->piece);
        $this->assertInstanceOf(\Closure::class, $cmd);
    }

    public function testPauseTogglesAndIgnoresMovementUntilUnpaused(): void
    {
        $g = $this->deterministicGame();
        [$paused] = $g->update(new KeyMsg(KeyType::Char, 'p'));
        $this->assertTrue($paused->paused);

        $startX = $paused->piece->x;
        [$stillPaused] = $paused->update(new KeyMsg(KeyType::Left, ''));
        $this->assertSame($startX, $stillPaused->piece->x, 'paused game ignores movement');

        [$resumed] = $paused->update(new KeyMsg(KeyType::Char, 'p'));
        $this->assertFalse($resumed->paused);
    }

    public function testGameOverOnlyHonorsQuit(): void
    {
        // Force game-over by hand: build a Game with over=true.
        $g = $this->deterministicGame();
        $over = new Game($g->board, $g->piece, $g->bag, $g->score, over: true);
        [$samePiece1] = $over->update(new KeyMsg(KeyType::Left, ''));
        $this->assertSame($over->piece, $samePiece1->piece);
        [, $cmd] = $over->update(new KeyMsg(KeyType::Char, 'q'));
        $this->assertInstanceOf(\Closure::class, $cmd);
    }
}

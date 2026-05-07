<?php

declare(strict_types=1);

namespace SugarCraft\Tetris;

use SugarCraft\Core\Cmd;
use SugarCraft\Core\KeyType;
use SugarCraft\Core\Model;
use SugarCraft\Core\Msg;
use SugarCraft\Core\Msg\KeyMsg;

/**
 * Tetris as a SugarCraft {@see Model}.
 *
 * Architecture:
 *
 *   - Pure state machine. Every transition (`update()`) returns
 *     `[nextGame, ?Cmd]` with no I/O. The Cmd here is always
 *     either `Cmd::quit()` (on `q`) or a `tick()` for the next
 *     gravity drop.
 *   - Time is driven by `TickMsg`. The first tick is scheduled
 *     from `init()`; each tick handler returns the next tick at
 *     the level-appropriate interval, so gravity ramps with the
 *     score automatically.
 *   - Game-over is detected when a freshly-spawned piece doesn't
 *     {@see Board::fits()}. After that, only `q` is honoured.
 *
 * The `Renderer` is a separate pure function that takes a Game
 * and returns the frame string — keeps `view()` tiny and the
 * game loop testable in isolation.
 */
final class Game implements Model
{
    public function __construct(
        public readonly Board  $board,
        public readonly Piece  $piece,
        public readonly Bag    $bag,
        public readonly Score  $score,
        public readonly bool   $over = false,
        public readonly bool   $paused = false,
    ) {}

    public static function start(?Bag $bag = null): self
    {
        $bag ??= new Bag();
        $first = $bag->next();
        return new self(new Board(), self::spawn($first), $bag, new Score());
    }

    public function init(): ?\Closure
    {
        return self::scheduleGravity($this->score);
    }

    private static function scheduleGravity(Score $score): \Closure
    {
        return Cmd::tick(
            $score->gravityIntervalUs() / 1_000_000,
            static fn(): Msg => new GravityMsg(),
        );
    }

    public function update(Msg $msg): array
    {
        if ($this->over) {
            if ($msg instanceof KeyMsg && $msg->type === KeyType::Char && $msg->rune === 'q') {
                return [$this, Cmd::quit()];
            }
            return [$this, null];
        }

        if ($msg instanceof KeyMsg) {
            return $this->handleKey($msg);
        }

        if ($msg instanceof GravityMsg) {
            if ($this->paused) {
                return [$this, self::scheduleGravity($this->score)];
            }
            return $this->gravityStep();
        }

        return [$this, null];
    }

    public function view(): string
    {
        return Renderer::render($this);
    }

    /**
     * @return array{0:Game,1:?\Closure}
     */
    private function handleKey(KeyMsg $msg): array
    {
        if ($msg->type === KeyType::Char && $msg->rune === 'q') {
            return [$this, Cmd::quit()];
        }
        if ($msg->type === KeyType::Char && $msg->rune === 'p') {
            return [$this->withPaused(!$this->paused), null];
        }
        if ($this->paused) {
            return [$this, null];
        }

        return match (true) {
            $msg->type === KeyType::Left
                => [$this->tryMove(-1, 0), null],
            $msg->type === KeyType::Right
                => [$this->tryMove(1, 0), null],
            $msg->type === KeyType::Down
                => [$this->softDrop(), null],
            $msg->type === KeyType::Up,
            $msg->type === KeyType::Char && $msg->rune === 'x'
                => [$this->tryRotate(1), null],
            $msg->type === KeyType::Char && $msg->rune === 'z'
                => [$this->tryRotate(-1), null],
            $msg->type === KeyType::Char && $msg->rune === ' '
                => $this->hardDrop(),
            default => [$this, null],
        };
    }

    /**
     * @return array{0:Game,1:?\Closure}
     */
    private function gravityStep(): array
    {
        $next = $this->piece->moved(0, 1);
        if ($this->board->fits($next)) {
            $game = new self($this->board, $next, $this->bag, $this->score);
        } else {
            $game = $this->lockAndSpawn();
        }
        if ($game->over) {
            return [$game, null];
        }
        return [$game, self::scheduleGravity($game->score)];
    }

    private function tryMove(int $dx, int $dy): self
    {
        $next = $this->piece->moved($dx, $dy);
        return $this->board->fits($next)
            ? new self($this->board, $next, $this->bag, $this->score)
            : $this;
    }

    private function tryRotate(int $delta): self
    {
        $candidate = $this->piece->rotated($delta);
        // Tiny wall-kick: try ±1 / ±2 horizontal nudges if the
        // bare rotation collides. Not full SRS but covers the
        // most common stuck-in-corner cases.
        foreach ([0, -1, 1, -2, 2] as $kick) {
            $kicked = $candidate->moved($kick, 0);
            if ($this->board->fits($kicked)) {
                return new self($this->board, $kicked, $this->bag, $this->score);
            }
        }
        return $this;
    }

    private function softDrop(): self
    {
        $next = $this->piece->moved(0, 1);
        return $this->board->fits($next)
            ? new self($this->board, $next, $this->bag, $this->score)
            : $this;
    }

    /**
     * @return array{0:Game,1:?\Closure}
     */
    private function hardDrop(): array
    {
        $resting = $this->board->dropPiece($this->piece);
        $game = new self($this->board, $resting, $this->bag, $this->score);
        $game = $game->lockAndSpawn();
        if ($game->over) {
            return [$game, null];
        }
        return [$game, self::scheduleGravity($game->score)];
    }

    private function lockAndSpawn(): self
    {
        $boardWithPiece = $this->board->place($this->piece);
        [$cleared, $count] = $boardWithPiece->clearLines();
        $score = $this->score->withLines($count);
        $newPiece = self::spawn($this->bag->next());
        if (!$cleared->fits($newPiece)) {
            return new self($cleared, $newPiece, $this->bag, $score, over: true);
        }
        return new self($cleared, $newPiece, $this->bag, $score);
    }

    private function withPaused(bool $paused): self
    {
        return new self($this->board, $this->piece, $this->bag, $this->score, $this->over, $paused);
    }

    private static function spawn(Tetromino $kind): Piece
    {
        // Spawn so the bounding box is centred horizontally and
        // sits in the hidden buffer rows so the player gets a
        // tick or two before pieces appear in the visible area.
        $x = (int) ((Board::COLS - 4) / 2);
        return new Piece($kind, 0, $x, Board::HIDDEN_ROWS - 4);
    }
}

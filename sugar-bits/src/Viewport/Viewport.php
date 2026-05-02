<?php

declare(strict_types=1);

namespace CandyCore\Bits\Viewport;

use CandyCore\Core\KeyType;
use CandyCore\Core\Model;
use CandyCore\Core\Msg;
use CandyCore\Core\Msg\KeyMsg;

/**
 * Scrollable text area. Holds a fixed-size window over arbitrarily long
 * content; navigation methods (lineUp/lineDown/pageUp/…) advance the
 * window, clamped so the viewport never goes past the start or end.
 *
 * `update()` recognises the standard navigation keys: ↑/k, ↓/j, PgUp/b,
 * PgDn/space/f, Ctrl+U / Ctrl+D (half page), Home/g, End/G.
 */
final class Viewport implements Model
{
    private function __construct(
        public readonly int $width,
        public readonly int $height,
        /** @var list<string> */
        public readonly array $lines,
        public readonly int $yOffset,
    ) {}

    public static function new(int $width = 80, int $height = 24): self
    {
        if ($width < 0 || $height < 0) {
            throw new \InvalidArgumentException('viewport width/height must be >= 0');
        }
        return new self($width, $height, [''], 0);
    }

    public function init(): ?\Closure
    {
        return null;
    }

    /**
     * @return array{0:Model, 1:?\Closure}
     */
    public function update(Msg $msg): array
    {
        if (!$msg instanceof KeyMsg) {
            return [$this, null];
        }
        return match (true) {
            $msg->type === KeyType::Up
                || ($msg->type === KeyType::Char && $msg->rune === 'k')
                => [$this->lineUp(1), null],
            $msg->type === KeyType::Down
                || ($msg->type === KeyType::Char && $msg->rune === 'j')
                => [$this->lineDown(1), null],
            $msg->type === KeyType::PageUp
                || ($msg->type === KeyType::Char && $msg->rune === 'b')
                => [$this->pageUp(), null],
            $msg->type === KeyType::PageDown
                || $msg->type === KeyType::Space
                || ($msg->type === KeyType::Char && $msg->rune === 'f')
                => [$this->pageDown(), null],
            $msg->ctrl && $msg->rune === 'u'
                => [$this->halfPageUp(), null],
            $msg->ctrl && $msg->rune === 'd'
                => [$this->halfPageDown(), null],
            $msg->type === KeyType::Home
                || ($msg->type === KeyType::Char && $msg->rune === 'g')
                => [$this->gotoTop(), null],
            $msg->type === KeyType::End
                || ($msg->type === KeyType::Char && $msg->rune === 'G')
                => [$this->gotoBottom(), null],
            default => [$this, null],
        };
    }

    public function view(): string
    {
        $top    = max(0, $this->yOffset);
        $window = array_slice($this->lines, $top, $this->height);
        return implode("\n", $window);
    }

    // ---- content + dimensions ----------------------------------------

    public function setContent(string $content): self
    {
        $lines = $content === '' ? [''] : explode("\n", $content);
        $clone = new self($this->width, $this->height, $lines, $this->yOffset);
        return $clone->clamp();
    }

    public function withSize(int $width, int $height): self
    {
        if ($width < 0 || $height < 0) {
            throw new \InvalidArgumentException('viewport width/height must be >= 0');
        }
        return (new self($width, $height, $this->lines, $this->yOffset))->clamp();
    }

    // ---- navigation --------------------------------------------------

    public function lineUp(int $n = 1): self
    {
        return (new self($this->width, $this->height, $this->lines, $this->yOffset - max(0, $n)))->clamp();
    }

    public function lineDown(int $n = 1): self
    {
        return (new self($this->width, $this->height, $this->lines, $this->yOffset + max(0, $n)))->clamp();
    }

    public function halfPageUp(): self   { return $this->lineUp((int) max(1, intdiv($this->height, 2))); }
    public function halfPageDown(): self { return $this->lineDown((int) max(1, intdiv($this->height, 2))); }

    public function pageUp(): self   { return $this->lineUp(max(1, $this->height)); }
    public function pageDown(): self { return $this->lineDown(max(1, $this->height)); }

    public function gotoTop(): self
    {
        return new self($this->width, $this->height, $this->lines, 0);
    }

    public function gotoBottom(): self
    {
        return (new self(
            $this->width, $this->height, $this->lines, $this->maxOffset(),
        ))->clamp();
    }

    // ---- queries -----------------------------------------------------

    public function totalLineCount(): int
    {
        return count($this->lines);
    }

    public function visibleLineCount(): int
    {
        return min($this->height, max(0, $this->totalLineCount() - $this->yOffset));
    }

    public function atTop(): bool    { return $this->yOffset <= 0; }
    public function atBottom(): bool { return $this->yOffset >= $this->maxOffset(); }

    /** Scroll position 0.0 (top) → 1.0 (bottom). 1.0 when content fits. */
    public function scrollPercent(): float
    {
        $max = $this->maxOffset();
        if ($max <= 0) {
            return 1.0;
        }
        return min(1.0, max(0.0, $this->yOffset / $max));
    }

    private function maxOffset(): int
    {
        return max(0, $this->totalLineCount() - $this->height);
    }

    private function clamp(): self
    {
        $offset = max(0, min($this->yOffset, $this->maxOffset()));
        if ($offset === $this->yOffset) {
            return $this;
        }
        return new self($this->width, $this->height, $this->lines, $offset);
    }
}

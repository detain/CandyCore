<?php

declare(strict_types=1);

namespace CandyCore\Bits\Paginator;

use CandyCore\Core\KeyType;
use CandyCore\Core\Model;
use CandyCore\Core\Msg;
use CandyCore\Core\Msg\KeyMsg;

/**
 * Pagination state + renderer.
 *
 * ```php
 * $p = Paginator::new()->withTotalItems(42)->withPerPage(10); // 5 pages
 * [$start, $end] = $p->sliceBounds();                          // [0, 10]
 * echo $p->view();                                             // "● ○ ○ ○ ○"
 * ```
 *
 * The component is a {@see Model} so it can absorb left/right arrow,
 * h/l (vim), pgup/pgdn key presses directly. Pages are 0-indexed
 * internally; the Arabic view shows them 1-indexed.
 */
final class Paginator implements Model
{
    private function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly int $totalItems,
        public readonly Type $type,
        public readonly string $activeDot,
        public readonly string $inactiveDot,
    ) {}

    public static function new(): self
    {
        return new self(
            page: 0,
            perPage: 10,
            totalItems: 0,
            type: Type::Dots,
            activeDot: '●',
            inactiveDot: '○',
        );
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
            $msg->type === KeyType::Right
                || ($msg->type === KeyType::Char && $msg->rune === 'l')
                || $msg->type === KeyType::PageDown
                => [$this->nextPage(), null],
            $msg->type === KeyType::Left
                || ($msg->type === KeyType::Char && $msg->rune === 'h')
                || $msg->type === KeyType::PageUp
                => [$this->prevPage(), null],
            default => [$this, null],
        };
    }

    public function view(): string
    {
        $total = max(1, $this->totalPages());
        return match ($this->type) {
            Type::Dots => $this->renderDots($total),
            Type::Arabic => sprintf('%d/%d', $this->page + 1, $total),
        };
    }

    public function totalPages(): int
    {
        if ($this->perPage <= 0 || $this->totalItems <= 0) {
            return 0;
        }
        return (int) ceil($this->totalItems / $this->perPage);
    }

    public function nextPage(): self
    {
        $last = max(0, $this->totalPages() - 1);
        return $this->mutate(page: min($this->page + 1, $last));
    }

    public function prevPage(): self
    {
        return $this->mutate(page: max(0, $this->page - 1));
    }

    public function onFirstPage(): bool
    {
        return $this->page === 0;
    }

    public function onLastPage(): bool
    {
        return $this->page >= max(0, $this->totalPages() - 1);
    }

    /** Item index range for the current page: [start (incl), end (excl)]. */
    public function sliceBounds(): array
    {
        if ($this->totalItems <= 0) {
            return [0, 0];
        }
        $start = $this->page * $this->perPage;
        $end   = min($this->totalItems, $start + $this->perPage);
        return [$start, $end];
    }

    public function withTotalItems(int $n): self
    {
        $n = max(0, $n);
        $clone = $this->mutate(totalItems: $n);
        // Clamp page if the new total moved us past the last page.
        $last = max(0, $clone->totalPages() - 1);
        if ($clone->page > $last) {
            $clone = $clone->mutate(page: $last);
        }
        return $clone;
    }

    public function withPerPage(int $n): self    { return $this->mutate(perPage: max(1, $n)); }
    public function withPage(int $p): self
    {
        $last = max(0, $this->totalPages() - 1);
        return $this->mutate(page: max(0, min($p, $last)));
    }
    public function withType(Type $t): self      { return $this->mutate(type: $t); }
    public function withDots(string $active, string $inactive): self
    {
        return $this->mutate(activeDot: $active, inactiveDot: $inactive);
    }

    private function renderDots(int $total): string
    {
        $out = [];
        for ($i = 0; $i < $total; $i++) {
            $out[] = $i === $this->page ? $this->activeDot : $this->inactiveDot;
        }
        return implode(' ', $out);
    }

    private function mutate(
        ?int $page = null,
        ?int $perPage = null,
        ?int $totalItems = null,
        ?Type $type = null,
        ?string $activeDot = null,
        ?string $inactiveDot = null,
    ): self {
        return new self(
            page:        $page        ?? $this->page,
            perPage:     $perPage     ?? $this->perPage,
            totalItems:  $totalItems  ?? $this->totalItems,
            type:        $type        ?? $this->type,
            activeDot:   $activeDot   ?? $this->activeDot,
            inactiveDot: $inactiveDot ?? $this->inactiveDot,
        );
    }
}

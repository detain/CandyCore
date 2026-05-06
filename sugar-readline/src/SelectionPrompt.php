<?php

declare(strict_types=1);

namespace CandyCore\Readline;

/**
 * Filterable selection prompt with cursor navigation and pagination.
 *
 * Port of erikgeiser/promptkit Selection.
 *
 * @see https://github.com/erikgeiser/promptkit
 */
final class SelectionPrompt
{
    private string $label;
    private array $allItems = [];
    private array $filteredItems = [];
    private string $filterText = '';
    private int $cursor = 0;     // cursor in filtered list
    private int $page    = 0;
    private int $perPage = 10;
    private bool $multi  = false;

    /** @var array<int, true> Selected indices in filteredItems */
    private array $selected = [];

    private bool $confirmed  = false;
    private bool $cancelled  = false;

    private string $cursorStyle      = '7';    // reverse
    private string $selectedStyle    = '32';   // green
    private string $labelStyle       = '1;36'; // bold cyan

    // -------------------------------------------------------------------------
    // Factory
    // -------------------------------------------------------------------------

    public function __construct(string $label, array $items)
    {
        $this->label        = $label;
        $this->allItems     = $items;
        $this->filteredItems = $items;
    }

    public static function new(string $label, array $items): self
    {
        return new self($label, $items);
    }

    // -------------------------------------------------------------------------
    // Configuration
    // -------------------------------------------------------------------------

    public function WithMultiSelect(bool $multi = true): self
    {
        $clone = clone $this;
        $clone->multi = $multi;
        return $clone;
    }

    public function WithPerPage(int $n): self
    {
        $clone = clone $this;
        $clone->perPage = $n;
        return $clone;
    }

    // -------------------------------------------------------------------------
    // Input handling
    // -------------------------------------------------------------------------

    public function Filter(string $text): self
    {
        $clone = clone $this;
        $clone->filterText   = $text;
        $clone->cursor       = 0;
        $clone->page         = 0;
        $clone->selected     = [];
        $clone->filteredItems = $text === ''
            ? $clone->allItems
            : \array_values(
                \array_filter($clone->allItems, fn(string $item): bool =>
                    \stripos($item, $text) !== false
                )
              );
        return $clone;
    }

    public function HandleKey(string $key): self
    {
        if ($this->confirmed || $this->cancelled) return $this;

        return match ($key) {
            'up', 'k'   => $this->moveCursor(-1),
            'down', 'j' => $this->moveCursor(1),
            'pageup'    => $this->prevPage(),
            'pagedown'  => $this->nextPage(),
            'home'      => $this->moveCursorToStart(),
            'end'       => $this->moveCursorToEnd(),
            'enter'     => $this->confirm(),
            'space'     => $this->toggleSelect(),
            'esc', 'ctrl_c' => $this->cancel(),
            default     => $this,
        };
    }

    public function Confirm(): self
    {
        if ($this->confirmed || $this->cancelled) return $this;
        $clone = clone $this;
        $clone->confirmed = true;
        return $clone;
    }

    public function Cancel(): self
    {
        $clone = clone $this;
        $clone->cancelled = true;
        return $clone;
    }

    // -------------------------------------------------------------------------
    // Queries
    // -------------------------------------------------------------------------

    public function SelectedValue(): ?string
    {
        if ($this->cancelled || $this->filteredItems === []) return null;
        $idx = $this->filteredCursor();
        return $this->filteredItems[$idx] ?? null;
    }

    /**
     * @return list<string>  All selected values (useful in multi-select mode)
     */
    public function SelectedValues(): array
    {
        if ($this->cancelled) return [];
        if ($this->multi) {
            return \array_values(
                \array_filter($this->filteredItems, fn($_, $i): bool =>
                    isset($this->selected[$i]), ARRAY_FILTER_USE_BOTH
                )
            );
        }
        $v = $this->SelectedValue();
        return $v !== null ? [$v] : [];
    }

    public function IsConfirmed(): bool  { return $this->confirmed; }
    public function IsCancelled(): bool  { return $this->cancelled; }

    public function CurrentPageItems(): array
    {
        $offset = $this->page * $this->perPage;
        return \array_slice($this->filteredItems, $offset, $this->perPage);
    }

    public function TotalPages(): int
    {
        return (int) \ceil(\count($this->filteredItems) / $this->perPage);
    }

    public function CurrentPage(): int { return $this->page; }

    // -------------------------------------------------------------------------
    // Rendering
    // -------------------------------------------------------------------------

    public function View(): string
    {
        $lines = [];

        // Label
        $lines[] = $this->ansi($this->label, $this->labelStyle);

        // Filter bar
        $filterBar = '[filter] ' . $this->filterText . ' ';
        if ($this->filterText !== '') {
            $filterBar .= \sprintf(' (%d matches)', \count($this->filteredItems));
        }
        $lines[] = $filterBar;

        if ($this->filteredItems === []) {
            $lines[] = '(no matches)';
            return \implode("\n", $lines);
        }

        // Items
        $offset = $this->page * $this->perPage;
        for ($i = 0; $i < $this->perPage; $i++) {
            $idx = $offset + $i;
            if (!isset($this->filteredItems[$idx])) break;

            $item   = $this->filteredItems[$idx];
            $marker = isset($this->selected[$idx]) ? '◉' : '○';
            $prefix = $marker . ' ';

            $isCursor = ($idx === $this->filteredCursor());

            $itemStr = $prefix . $item;
            if ($isCursor) {
                $itemStr = $this->ansi($itemStr, $this->cursorStyle);
            } elseif (isset($this->selected[$idx])) {
                $itemStr = $this->ansi($itemStr, $this->selectedStyle);
            }

            $lines[] = $itemStr;
        }

        // Pagination indicator
        $total = $this->TotalPages();
        if ($total > 1) {
            $lines[] = \sprintf('Page %d/%d', $this->page + 1, $total);
        }

        return \implode("\n", $lines);
    }

    // -------------------------------------------------------------------------
    // Internal
    // -------------------------------------------------------------------------

    private function moveCursor(int $delta): self
    {
        $count = \count($this->filteredItems);
        if ($count === 0) return $this;

        $clone = clone $this;
        $cursor = $clone->cursor + $delta;

        if ($cursor < 0) $cursor = 0;
        if ($cursor >= $count) $cursor = $count - 1;

        $clone->cursor = $cursor;

        // Auto-scroll page
        $clone->page = \min($clone->TotalPages() - 1, (int) \floor($cursor / $clone->perPage));

        return $clone;
    }

    private function moveCursorToStart(): self
    {
        $clone = clone $this;
        $clone->cursor = 0;
        $clone->page   = 0;
        return $clone;
    }

    private function moveCursorToEnd(): self
    {
        $clone = clone $this;
        $clone->cursor = \max(0, \count($clone->filteredItems) - 1);
        $clone->page   = $clone->TotalPages() - 1;
        return $clone;
    }

    private function prevPage(): self
    {
        $clone = clone $this;
        $clone->page = \max(0, $clone->page - 1);
        return $clone;
    }

    private function nextPage(): self
    {
        $clone = clone $this;
        $clone->page = \min($clone->TotalPages() - 1, $clone->page + 1);
        return $clone;
    }

    private function toggleSelect(): self
    {
        if (!$this->multi) return $this->Confirm();

        $idx = $this->filteredCursor();
        $clone = clone $this;

        if (isset($clone->selected[$idx])) {
            unset($clone->selected[$idx]);
        } else {
            $clone->selected[$idx] = true;
        }

        return $clone;
    }

    private function confirm(): self
    {
        return $this->Confirm();
    }

    private function cancel(): self
    {
        return $this->Cancel();
    }

    /**
     * Map our flat cursor (across all filtered items) to the item's index in filteredItems.
     */
    private function filteredCursor(): int
    {
        return $this->cursor;
    }

    private function ansi(string $text, string $codes): string
    {
        if ($codes === '') return $text;
        return "\x1b[{$codes}m{$text}\x1b[0m";
    }
}

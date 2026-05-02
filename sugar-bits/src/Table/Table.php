<?php

declare(strict_types=1);

namespace CandyCore\Bits\Table;

use CandyCore\Core\KeyType;
use CandyCore\Core\Model;
use CandyCore\Core\Msg;
use CandyCore\Core\Msg\KeyMsg;
use CandyCore\Core\Util\Ansi;
use CandyCore\Core\Util\Width;

/**
 * Selectable, scrollable data table.
 *
 * Distinct from {@see \CandyCore\Sprinkles\Table\Table}, which is a static
 * styled renderer. This component holds a moving selection cursor,
 * scrolls vertically when the row count exceeds {@see $height}, and
 * draws an underlined header.
 *
 * Column widths are computed from header + cell widths and capped at
 * {@see $width} (when > 0) by truncating individual cells.
 */
final class Table implements Model
{
    private function __construct(
        /** @var list<string> */ public readonly array $headers,
        /** @var list<list<string>> */ public readonly array $rows,
        public readonly int $cursor,
        public readonly int $offset,
        public readonly int $width,
        public readonly int $height,
        public readonly bool $focused,
    ) {}

    /**
     * @param list<string>       $headers
     * @param list<list<string>> $rows
     */
    public static function new(array $headers = [], array $rows = [], int $width = 0, int $height = 10): self
    {
        if ($width < 0 || $height < 0) {
            throw new \InvalidArgumentException('table width/height must be >= 0');
        }
        return new self(
            headers: array_values($headers),
            rows:    array_values(array_map('array_values', $rows)),
            cursor:  0,
            offset:  0,
            width:   $width,
            height:  $height,
            focused: false,
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
        if (!$msg instanceof KeyMsg || !$this->focused) {
            return [$this, null];
        }
        return match (true) {
            $msg->type === KeyType::Up
                || ($msg->type === KeyType::Char && $msg->rune === 'k')
                => [$this->moveCursor($this->cursor - 1), null],
            $msg->type === KeyType::Down
                || ($msg->type === KeyType::Char && $msg->rune === 'j')
                => [$this->moveCursor($this->cursor + 1), null],
            $msg->type === KeyType::Home
                || ($msg->type === KeyType::Char && $msg->rune === 'g')
                => [$this->moveCursor(0), null],
            $msg->type === KeyType::End
                || ($msg->type === KeyType::Char && $msg->rune === 'G')
                => [$this->moveCursor(PHP_INT_MAX), null],
            $msg->type === KeyType::PageUp
                => [$this->moveCursor($this->cursor - max(1, $this->height)), null],
            $msg->type === KeyType::PageDown
                => [$this->moveCursor($this->cursor + max(1, $this->height)), null],
            default => [$this, null],
        };
    }

    public function view(): string
    {
        $cols = $this->columnWidths();
        if ($cols === []) {
            return '';
        }

        $lines = [];
        if ($this->headers !== []) {
            $lines[] = Ansi::sgr(Ansi::UNDERLINE) . $this->renderRow($this->headers, $cols) . Ansi::reset();
        }

        $top    = max(0, $this->offset);
        $window = array_slice($this->rows, $top, $this->height);
        foreach ($window as $i => $row) {
            $idx = $top + $i;
            $line = $this->renderRow($row, $cols);
            if ($idx === $this->cursor && $this->focused) {
                $line = Ansi::sgr(Ansi::REVERSE) . $line . Ansi::reset();
            }
            $lines[] = $line;
        }
        return implode("\n", $lines);
    }

    /** @return list<string> */
    public function selectedRow(): array
    {
        return $this->rows[$this->cursor] ?? [];
    }

    public function index(): int
    {
        return $this->cursor;
    }

    /**
     * @return array{0:self, 1:?\Closure}
     */
    public function focus(): array
    {
        return [$this->mutate(focused: true), null];
    }

    public function blur(): self
    {
        return $this->mutate(focused: false);
    }

    /** @param list<list<string>> $rows */
    public function setRows(array $rows): self
    {
        return $this->mutate(rows: array_values(array_map('array_values', $rows)))->reclamp();
    }

    /** @param list<string> $headers */
    public function setHeaders(array $headers): self
    {
        return $this->mutate(headers: array_values($headers));
    }

    public function setSize(int $width, int $height): self
    {
        if ($width < 0 || $height < 0) {
            throw new \InvalidArgumentException('table width/height must be >= 0');
        }
        return $this->mutate(width: $width, height: $height)->reclamp();
    }

    // ---- internals ---------------------------------------------------

    /** @return list<int> */
    private function columnWidths(): array
    {
        $cols = max(
            count($this->headers),
            ...array_map('count', $this->rows ?: [[]]),
        );
        if ($cols === 0) {
            return [];
        }
        $widths = array_fill(0, $cols, 0);
        foreach (array_merge([$this->headers], $this->rows) as $row) {
            foreach ($row as $i => $cell) {
                $widths[$i] = max($widths[$i], Width::string($cell));
            }
        }
        // If a total width is constrained, shrink columns round-robin
        // (right-to-left) so every line of output fits the budget. The
        // earlier "always trim the rightmost column" rule could still
        // overflow once that column hit zero.
        if ($this->width > 0) {
            $gutter = $cols - 1;
            $budget = max(0, $this->width - $gutter);
            $total  = array_sum($widths);
            while ($total > $budget) {
                $shrunk = false;
                for ($i = $cols - 1; $i >= 0; $i--) {
                    if ($widths[$i] > 0) {
                        $widths[$i]--;
                        $total--;
                        $shrunk = true;
                        if ($total <= $budget) {
                            break;
                        }
                    }
                }
                if (!$shrunk) {
                    // Every column already at zero — nothing more to do.
                    break;
                }
            }
        }
        return $widths;
    }

    /**
     * @param list<string> $row
     * @param list<int>    $widths
     */
    private function renderRow(array $row, array $widths): string
    {
        $cells = [];
        foreach ($widths as $i => $w) {
            $cell = $row[$i] ?? '';
            $cell = Width::truncate($cell, $w);
            $pad  = $w - Width::string($cell);
            $cells[] = $cell . str_repeat(' ', max(0, $pad));
        }
        return implode(' ', $cells);
    }

    private function moveCursor(int $idx): self
    {
        $count = count($this->rows);
        if ($count === 0) {
            return $this->mutate(cursor: 0, offset: 0);
        }
        $cursor = max(0, min($count - 1, $idx));
        $offset = $this->offset;
        if ($cursor < $offset) {
            $offset = $cursor;
        }
        if ($this->height > 0 && $cursor >= $offset + $this->height) {
            $offset = $cursor - $this->height + 1;
        }
        return $this->mutate(cursor: $cursor, offset: max(0, $offset));
    }

    private function reclamp(): self
    {
        return $this->moveCursor($this->cursor);
    }

    private function mutate(
        ?array $headers = null,
        ?array $rows = null,
        ?int $cursor = null,
        ?int $offset = null,
        ?int $width = null,
        ?int $height = null,
        ?bool $focused = null,
    ): self {
        return new self(
            headers: $headers ?? $this->headers,
            rows:    $rows    ?? $this->rows,
            cursor:  $cursor  ?? $this->cursor,
            offset:  $offset  ?? $this->offset,
            width:   $width   ?? $this->width,
            height:  $height  ?? $this->height,
            focused: $focused ?? $this->focused,
        );
    }
}

<?php

declare(strict_types=1);

namespace CandyCore\Readline;

/**
 * Multi-line text input prompt.
 *
 * Port of erikgeiser/promptkit Textarea.
 *
 * @see https://github.com/erikgeiser/promptkit
 */
final class TextareaPrompt
{
    private string $label;
    private array $lines = [''];    // content lines
    private int $cursorLine = 0;
    private int $cursorCol  = 0;
    private int $scrollY    = 0;    // vertical scroll offset
    private bool $confirmed  = false;
    private bool $cancelled  = false;
    private int $maxLines    = 0;   // 0 = unlimited

    private string $labelStyle = '1;36';
    private string $cursorStyle = '7';

    public function __construct(string $label)
    {
        $this->label = $label;
    }

    public static function new(string $label): self
    {
        return new self($label);
    }

    public function WithDefault(string $text): self
    {
        $clone = clone $this;
        $clone->lines = $text === '' ? [''] : \explode("\n", $text);
        return $clone;
    }

    public function WithMaxLines(int $n): self
    {
        $clone = clone $this;
        $clone->maxLines = $n;
        return $clone;
    }

    public function HandleChar(string $char): self
    {
        if ($this->confirmed || $this->cancelled) return $this;
        if (\strlen($char) !== 1) return $this;

        $clone = clone $this;
        $line  = &$clone->lines[$clone->cursorLine];
        $line  = \substr($line, 0, $clone->cursorCol) . $char . \substr($line, $clone->cursorCol);
        $clone->cursorCol++;
        return $clone;
    }

    public function HandleKey(string $key): self
    {
        if ($this->confirmed || $this->cancelled) return $this;

        return match ($key) {
            'left'      => $this->moveCol(-1),
            'right'     => $this->moveCol(1),
            'up'        => $this->moveLine(-1),
            'down'      => $this->moveLine(1),
            'home'      => $this->moveColToStart(),
            'end'       => $this->moveColToEnd(),
            'enter'     => $this->insertNewline(),
            'backspace' => $this->handleBackspace(),
            'ctrl_c', 'esc' => $this->cancel(),
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

    public function Value(): string
    {
        if ($this->cancelled) return '';
        return \implode("\n", $this->lines);
    }

    public function IsConfirmed(): bool  { return $this->confirmed; }
    public function IsCancelled(): bool  { return $this->cancelled; }

    public function View(): string
    {
        $lines = [];
        $lines[] = $this->ansi($this->label, $this->labelStyle);

        $displayHeight = $this->maxLines > 0 ? $this->maxLines : \count($this->lines);
        $displayLines  = \array_slice($this->lines, $this->scrollY, $displayHeight);

        foreach ($displayLines as $di => $contentLine) {
            $lineIdx = $this->scrollY + $di;
            $isCursorLine = ($lineIdx === $this->cursorLine);
            $cursorInLine = $isCursorLine ? $this->cursorCol : -1;

            $display = \substr($contentLine, 0, $cursorInLine)
                     . $this->ansi(' ', $this->cursorStyle)
                     . \substr($contentLine, $cursorInLine);
            $lines[] = $display;
        }

        if ($this->maxLines > 0) {
            $lines[] = \sprintf('(%d/%d lines)', \count($this->lines), $this->maxLines);
        }

        return \implode("\n", $lines);
    }

    // -------------------------------------------------------------------------
    // Internal
    // -------------------------------------------------------------------------

    private function moveCol(int $delta): self
    {
        $clone = clone $this;
        $maxCol = \strlen($clone->lines[$clone->cursorLine] ?? '');
        $clone->cursorCol = \max(0, \min($maxCol, $clone->cursorCol + $delta));
        return $clone;
    }

    private function moveLine(int $delta): self
    {
        $clone = clone $this;
        $targetLine = $clone->cursorLine + $delta;
        $targetLine = \max(0, \min(\count($clone->lines) - 1, $targetLine));
        $clone->cursorLine = $targetLine;
        $maxCol = \strlen($clone->lines[$targetLine] ?? '');
        $clone->cursorCol = \min($clone->cursorCol, $maxCol);

        // Auto-scroll
        if ($clone->cursorLine < $clone->scrollY) {
            $clone->scrollY = $clone->cursorLine;
        } elseif ($clone->cursorLine >= $clone->scrollY + ($clone->maxLines > 0 ? $clone->maxLines : 10)) {
            $clone->scrollY = $clone->cursorLine - ($clone->maxLines > 0 ? $clone->maxLines - 1 : 9);
        }

        return $clone;
    }

    private function moveColToStart(): self
    {
        $clone = clone $this;
        $clone->cursorCol = 0;
        return $clone;
    }

    private function moveColToEnd(): self
    {
        $clone = clone $this;
        $clone->cursorCol = \strlen($clone->lines[$clone->cursorLine] ?? '');
        return $clone;
    }

    private function insertNewline(): self
    {
        if ($this->maxLines > 0 && \count($this->lines) >= $this->maxLines) {
            return $this->Confirm();
        }

        $clone = clone $this;
        $current = &$clone->lines[$clone->cursorLine];
        $after   = \substr($current, $clone->cursorCol);
        $current = \substr($current, 0, $clone->cursorCol);

        \array_splice($clone->lines, $clone->cursorLine + 1, 0, [$after]);
        $clone->cursorLine++;
        $clone->cursorCol = 0;

        return $clone;
    }

    private function handleBackspace(): self
    {
        if ($this->cursorCol > 0) {
            return $this->HandleKey('left')->HandleChar("\x7f");
        }

        // Merge with previous line
        if ($this->cursorLine > 0) {
            $clone = clone $this;
            $prevLen = \strlen($clone->lines[$clone->cursorLine - 1] ?? '');
            \array_splice($clone->lines, $clone->cursorLine, 1);
            $clone->cursorLine--;
            $clone->cursorCol = $prevLen;
            return $clone;
        }

        return $this;
    }

    private function cancel(): self
    {
        return $this->Cancel();
    }

    private function ansi(string $text, string $codes): string
    {
        if ($codes === '') return $text;
        return "\x1b[{$codes}m{$text}\x1b[0m";
    }
}

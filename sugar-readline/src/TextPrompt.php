<?php

declare(strict_types=1);

namespace SugarCraft\Readline;

/**
 * Single-line text input with cursor, optional validation, auto-completion,
 * and hidden (password) display mode.
 *
 * State machine: feed character input via {@see handleChar()} and named keys
 * via {@see handleKey()}. Each call returns a new immutable instance.
 *
 * Port-of-spirit (not literal) of erikgeiser/promptkit `textinput`.
 *
 * @see https://github.com/erikgeiser/promptkit
 */
final class TextPrompt
{
    /** Text typed by the user. The label is rendered separately by {@see view()}. */
    private string $buffer = '';

    /** Cursor column inside {@see $buffer} (in characters, not bytes). */
    private int $cursor = 0;

    private bool $hidden    = false;
    private int $charLimit  = 0;       // 0 = unlimited
    private bool $submitted = false;
    private bool $aborted   = false;
    private string $error   = '';

    /** @var list<string> */
    private array $completions = [];

    /** @var (callable(string): bool)|null */
    private $validator = null;

    private string $labelStyle      = '1;36';   // bold cyan
    private string $cursorStyle     = '7';      // reverse
    private string $errorStyle      = '31';     // red
    private string $completionStyle = '90';     // bright black
    private string $hideMask        = '*';

    public function __construct(private readonly string $label) {}

    public static function new(string $label): self
    {
        return new self($label);
    }

    // -------------------------------------------------------------------------
    // Configuration
    // -------------------------------------------------------------------------

    public function withDefault(string $value): self
    {
        $clone = clone $this;
        $clone->buffer = $value;
        $clone->cursor = self::charCount($value);
        return $clone;
    }

    public function withHidden(bool $hidden = true, string $mask = '*'): self
    {
        $clone = clone $this;
        $clone->hidden   = $hidden;
        $clone->hideMask = $mask;
        return $clone;
    }

    /** @param list<string> $completions */
    public function withCompletions(array $completions): self
    {
        $clone = clone $this;
        $clone->completions = array_values($completions);
        return $clone;
    }

    /** @param callable(string): bool $fn  Receives the user input; return false to reject. */
    public function withValidator(callable $fn): self
    {
        $clone = clone $this;
        $clone->validator = $fn;
        return $clone;
    }

    public function withCharLimit(int $limit): self
    {
        $clone = clone $this;
        $clone->charLimit = max(0, $limit);
        return $clone;
    }

    // -------------------------------------------------------------------------
    // Input
    // -------------------------------------------------------------------------

    public function handleChar(string $char): self
    {
        if ($this->submitted || $this->aborted) {
            return $this;
        }
        if ($char === '' || self::charCount($char) !== 1) {
            return $this;
        }
        if ($this->charLimit > 0 && self::charCount($this->buffer) >= $this->charLimit) {
            return $this;
        }

        $clone = clone $this;
        $clone->buffer = self::sliceChars($clone->buffer, 0, $clone->cursor)
                       . $char
                       . self::sliceChars($clone->buffer, $clone->cursor);
        $clone->cursor++;
        $clone->error = '';
        return $clone;
    }

    public function handleKey(string $key): self
    {
        if ($this->submitted || $this->aborted) {
            return $this;
        }

        return match ($key) {
            Key::Left      => $this->moveCursor(-1),
            Key::Right     => $this->moveCursor(1),
            Key::Home      => $this->moveCursorTo(0),
            Key::End       => $this->moveCursorTo(self::charCount($this->buffer)),
            Key::Backspace => $this->deleteBeforeCursor(),
            Key::Delete    => $this->deleteUnderCursor(),
            Key::CtrlU     => $this->deleteAllBeforeCursor(),
            Key::CtrlK     => $this->deleteAllAfterCursor(),
            Key::Tab       => $this->applyCompletion(),
            Key::Enter     => $this->submit(),
            Key::Escape, Key::CtrlC => $this->abort(),
            default        => $this,
        };
    }

    public function submit(): self
    {
        if ($this->submitted || $this->aborted) {
            return $this;
        }
        $clone = clone $this;
        if ($clone->validator !== null && !($clone->validator)($clone->buffer)) {
            $clone->error = 'Invalid input';
            return $clone;
        }
        $clone->submitted = true;
        $clone->error     = '';
        return $clone;
    }

    public function abort(): self
    {
        if ($this->submitted || $this->aborted) {
            return $this;
        }
        $clone = clone $this;
        $clone->aborted = true;
        return $clone;
    }

    // -------------------------------------------------------------------------
    // Queries
    // -------------------------------------------------------------------------

    /** The user's typed input. Empty string when aborted. */
    public function value(): string
    {
        return $this->aborted ? '' : $this->buffer;
    }

    /** Cursor column inside {@see value()} (0..length). */
    public function cursor(): int { return $this->cursor; }

    public function isSubmitted(): bool { return $this->submitted; }
    public function isAborted(): bool   { return $this->aborted; }
    public function error(): string     { return $this->error; }

    /** First completion that starts with the current input, or null. */
    public function suggestion(): ?string
    {
        if ($this->buffer === '') {
            return null;
        }
        foreach ($this->completions as $c) {
            if (str_starts_with($c, $this->buffer)) {
                return $c;
            }
        }
        return null;
    }

    // -------------------------------------------------------------------------
    // Rendering
    // -------------------------------------------------------------------------

    public function view(): string
    {
        $display = $this->hidden
            ? str_repeat($this->hideMask, self::charCount($this->buffer))
            : $this->buffer;

        $before = self::sliceChars($display, 0, $this->cursor);
        $under  = self::sliceChars($display, $this->cursor, 1);
        $after  = self::sliceChars($display, $this->cursor + 1);

        $line = Ansi::wrap($this->label, $this->labelStyle)
              . $before
              . Ansi::wrap($under === '' ? ' ' : $under, $this->cursorStyle)
              . $after;

        $lines = [$line];

        if ($this->error !== '') {
            $lines[] = Ansi::wrap($this->error, $this->errorStyle);
        }

        $hint = $this->suggestion();
        if ($hint !== null && $hint !== $this->buffer) {
            $tail = substr($hint, strlen($this->buffer));
            $lines[] = str_repeat(' ', self::charCount($this->label) + $this->cursor)
                     . Ansi::wrap($tail, $this->completionStyle);
        }

        return implode("\n", $lines);
    }

    // -------------------------------------------------------------------------
    // Internal
    // -------------------------------------------------------------------------

    private function moveCursor(int $delta): self
    {
        $target = $this->cursor + $delta;
        return $this->moveCursorTo($target);
    }

    private function moveCursorTo(int $position): self
    {
        $clamped = max(0, min(self::charCount($this->buffer), $position));
        if ($clamped === $this->cursor) {
            return $this;
        }
        $clone = clone $this;
        $clone->cursor = $clamped;
        return $clone;
    }

    private function deleteBeforeCursor(): self
    {
        if ($this->cursor === 0) {
            return $this;
        }
        $clone = clone $this;
        $clone->buffer = self::sliceChars($clone->buffer, 0, $clone->cursor - 1)
                       . self::sliceChars($clone->buffer, $clone->cursor);
        $clone->cursor--;
        $clone->error = '';
        return $clone;
    }

    private function deleteUnderCursor(): self
    {
        if ($this->cursor >= self::charCount($this->buffer)) {
            return $this;
        }
        $clone = clone $this;
        $clone->buffer = self::sliceChars($clone->buffer, 0, $clone->cursor)
                       . self::sliceChars($clone->buffer, $clone->cursor + 1);
        $clone->error = '';
        return $clone;
    }

    private function deleteAllBeforeCursor(): self
    {
        if ($this->cursor === 0) {
            return $this;
        }
        $clone = clone $this;
        $clone->buffer = self::sliceChars($clone->buffer, $clone->cursor);
        $clone->cursor = 0;
        $clone->error  = '';
        return $clone;
    }

    private function deleteAllAfterCursor(): self
    {
        if ($this->cursor >= self::charCount($this->buffer)) {
            return $this;
        }
        $clone = clone $this;
        $clone->buffer = self::sliceChars($clone->buffer, 0, $clone->cursor);
        $clone->error  = '';
        return $clone;
    }

    private function applyCompletion(): self
    {
        $hint = $this->suggestion();
        if ($hint === null || $hint === $this->buffer) {
            return $this;
        }
        $clone = clone $this;
        $clone->buffer = $hint;
        $clone->cursor = self::charCount($hint);
        $clone->error  = '';
        return $clone;
    }

    private static function charCount(string $s): int
    {
        return mb_strlen($s, 'UTF-8');
    }

    private static function sliceChars(string $s, int $start, ?int $length = null): string
    {
        return $length === null
            ? mb_substr($s, $start, null, 'UTF-8')
            : mb_substr($s, $start, $length, 'UTF-8');
    }
}

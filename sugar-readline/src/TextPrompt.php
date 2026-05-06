<?php

declare(strict_types=1);

namespace CandyCore\Readline;

/**
 * Line-editing text prompt with validation, auto-completion, and hidden/password mode.
 *
 * Port of erikgeiser/promptkit TextInput.
 *
 * @see https://github.com/erikgeiser/promptkit
 */
final class TextPrompt
{
    private string $label       = '';
    private string $buffer      = '';
    private string $default     = '';
    private int $cursor         = 0;
    private bool $hidden        = false;  // password mode
    private bool $confirmed     = false;
    private bool $cancelled     = false;
    private string $message     = '';
    private string $error       = '';

    /** @var list<string> */
    private array $completions  = [];

    /** @var callable|null (string): bool */
    private $validate          = null;

    // ANSI style codes
    private string $labelStyle    = '1;36';  // bold cyan
    private string $bufferStyle   = '';
    private string $cursorStyle   = '7';     // reverse
    private string $errorStyle    = '31';    // red
    private string $completionStyle = '90';  // bright black

    // -------------------------------------------------------------------------
    // Factory
    // -------------------------------------------------------------------------

    public function __construct(string $label)
    {
        $this->label = $label;
        $this->buffer = $label;
    }

    public static function new(string $label): self
    {
        return new self($label);
    }

    // -------------------------------------------------------------------------
    // Configuration
    // -------------------------------------------------------------------------

    public function WithDefault(string $default): self
    {
        $clone = clone $this;
        $clone->default = $default;
        if ($clone->buffer === $clone->label || $clone->buffer === '') {
            $clone->buffer = $default;
            $clone->cursor = \strlen($default);
        }
        return $clone;
    }

    public function WithHidden(bool $hidden = true): self
    {
        $clone = clone $this;
        $clone->hidden = $hidden;
        return $clone;
    }

    public function WithCompletions(array $completions): self
    {
        $clone = clone $this;
        $clone->completions = $completions;
        return $clone;
    }

    /**
     * @param callable(string): bool $fn  Return false to signal error
     */
    public function WithValidation(callable $fn): self
    {
        $clone = clone $this;
        $clone->validate = $fn;
        return $clone;
    }

    // -------------------------------------------------------------------------
    // Input handling
    // -------------------------------------------------------------------------

    /**
     * Handle a single character input.
     */
    public function HandleChar(string $char): self
    {
        if ($this->confirmed || $this->cancelled) return $this;
        if (\strlen($char) !== 1) return $this;

        $clone = clone $this;
        $clone->buffer = \substr($clone->buffer, 0, $clone->cursor)
                      . $char
                      . \substr($clone->buffer, $clone->cursor);
        $clone->cursor++;
        $clone->error = '';
        return $clone;
    }

    /**
     * Handle a backspace key.
     */
    public function HandleBackspace(): self
    {
        if ($this->cursor <= \strlen($this->label)) return $this;

        $clone = clone $this;
        if ($clone->cursor > \strlen($clone->label)) {
            $clone->buffer = \substr($clone->buffer, 0, $clone->cursor - 1)
                           . \substr($clone->buffer, $clone->cursor);
            $clone->cursor--;
        }
        $clone->error = '';
        return $clone;
    }

    /**
     * Handle an ANSI escape sequence or special key name.
     *
     * @param string $key  Key name e.g. 'left', 'right', 'tab', 'enter', 'esc'
     */
    public function HandleKey(string $key): self
    {
        if ($this->confirmed || $this->cancelled) return $this;

        return match ($key) {
            'left'  => $this->moveCursor(-1),
            'right' => $this->moveCursor(1),
            'home'  => $this->moveCursorToStart(),
            'end'   => $this->moveCursorToEnd(),
            'tab'   => $this->applyCompletion(),
            'enter' => $this->confirm(),
            'esc'   => $this->cancel(),
            'ctrl_c' => $this->cancel(),
            default => $this,
        };
    }

    /**
     * Submit the current input (confirm).
     */
    public function Confirm(): self
    {
        if ($this->confirmed || $this->cancelled) return $this;

        $clone = clone $this;
        $clone->confirmed = true;
        $clone->error     = '';

        if ($clone->validate !== null) {
            $ok = ($clone->validate)($clone->buffer);
            if (!$ok) {
                $clone->confirmed = false;
                $clone->error     = 'Invalid input';
            }
        }

        return $clone;
    }

    /**
     * Cancel the prompt.
     */
    public function Cancel(): self
    {
        $clone = clone $this;
        $clone->cancelled = true;
        return $clone;
    }

    // -------------------------------------------------------------------------
    // Queries
    // -------------------------------------------------------------------------

    public function Value(): string
    {
        if ($this->cancelled) return '';
        return $this->buffer !== '' ? $this->buffer : $this->default;
    }

    public function IsConfirmed(): bool  { return $this->confirmed; }
    public function IsCancelled(): bool  { return $this->cancelled; }
    public function Cursor(): int        { return $this->cursor; }
    public function Error(): string      { return $this->error; }

    /**
     * Get suggested completion at current cursor position.
     */
    public function suggestedCompletion(): ?string
    {
        $prefix = \substr($this->buffer, \strlen($this->label));
        if ($prefix === '') return null;

        foreach ($this->completions as $c) {
            if (\str_starts_with($c, $prefix)) {
                return $c;
            }
        }
        return null;
    }

    // -------------------------------------------------------------------------
    // Rendering
    // -------------------------------------------------------------------------

    /**
     * Render the prompt (label + input line + cursor + error).
     */
    public function View(): string
    {
        $prefix = $this->label;
        $display = $this->hidden
            ? \str_repeat('*', \strlen($this->buffer) - \strlen($prefix))
            : $this->buffer;

        $before = \substr($display, 0, $this->cursor);
        $after  = \substr($display, $this->cursor);

        // Cursor sits between before and after
        $line = $this->ansi($prefix, $this->labelStyle)
              . $before
              . $this->ansi(' ', $this->cursorStyle)
              . $after
              . ' ';

        $lines = [$line];

        if ($this->error !== '') {
            $lines[] = $this->ansi($this->error, $this->errorStyle);
        }

        // Show completions hint
        $suggestion = $this->suggestedCompletion();
        if ($suggestion !== null) {
            $tab = \str_repeat(' ', \strlen($this->label) + \strlen($prefix) + 1);
            $lines[] = $tab . $this->ansi($suggestion, $this->completionStyle);
        }

        return \implode("\n", $lines);
    }

    // -------------------------------------------------------------------------
    // Internal
    // -------------------------------------------------------------------------

    private function moveCursor(int $delta): self
    {
        $start = \strlen($this->label);
        $clone = clone $this;
        $clone->cursor = \max($start, \min(\strlen($clone->buffer), $clone->cursor + $delta));
        return $clone;
    }

    private function moveCursorToStart(): self
    {
        $clone = clone $this;
        $clone->cursor = \strlen($this->label);
        return $clone;
    }

    private function moveCursorToEnd(): self
    {
        $clone = clone $this;
        $clone->cursor = \strlen($clone->buffer);
        return $clone;
    }

    private function applyCompletion(): self
    {
        $suggestion = $this->suggestedCompletion();
        if ($suggestion === null) return $this;

        $clone = clone $this;
        $prefix = \substr($clone->buffer, \strlen($this->label));
        $diff   = \substr($suggestion, \strlen($prefix));

        $clone->buffer = $this->label . $suggestion;
        $clone->cursor = \strlen($clone->buffer);
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

    private function ansi(string $text, string $codes): string
    {
        if ($codes === '') return $text;
        return "\x1b[{$codes}m{$text}\x1b[0m";
    }
}

<?php

declare(strict_types=1);

namespace CandyCore\Readline;

/**
 * Yes/No confirmation prompt.
 *
 * Port of erikgeiser/promptkit Confirmation.
 *
 * @see https://github.com/erikgeiser/promptkit
 */
final class ConfirmationPrompt
{
    private string $label;
    private string $confirmLabel = 'Yes';
    private string $cancelLabel  = 'No';
    private string $hint         = '[y/n]';
    private bool $selected       = true;  // true = Yes (confirm), false = No (cancel)
    private bool $confirmed      = false;
    private bool $cancelled      = false;

    private string $selectedStyle = '1;32'; // bold green
    private string $labelStyle    = '1;36'; // bold cyan

    public function __construct(string $label)
    {
        $this->label = $label;
    }

    public static function new(string $label): self
    {
        return new self($label);
    }

    public function WithConfirmLabel(string $s): self
    {
        $clone = clone $this;
        $clone->confirmLabel = $s;
        return $clone;
    }

    public function WithCancelLabel(string $s): self
    {
        $clone = clone $this;
        $clone->cancelLabel = $s;
        return $clone;
    }

    public function HandleKey(string $key): self
    {
        if ($this->confirmed || $this->cancelled) return $this;

        return match ($key) {
            'left', 'up', 'h', 'k' => $this->selectConfirm(),
            'right', 'down', 'l', 'j' => $this->selectCancel(),
            'enter' => $this->confirm(),
            'y', 'Y' => $this->selectConfirm()->confirm(),
            'n', 'N' => $this->selectCancel()->confirm(),
            'esc', 'ctrl_c' => $this->cancel(),
            default => $this,
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

    public function Result(): bool
    {
        if ($this->cancelled) return false;
        if (!$this->confirmed) return false;
        return $this->selected;
    }

    public function IsConfirmed(): bool  { return $this->confirmed; }
    public function IsCancelled(): bool  { return $this->cancelled; }

    public function View(): string
    {
        $confirmStr = $this->selected ? $this->ansi($this->confirmLabel, $this->selectedStyle)
                                      : $this->confirmLabel;
        $cancelStr  = !$this->selected ? $this->ansi($this->cancelLabel, $this->selectedStyle)
                                       : $this->cancelLabel;

        return $this->label . ' ' . $confirmStr . ' / ' . $cancelStr . ' ' . $this->hint;
    }

    private function selectConfirm(): self
    {
        $clone = clone $this;
        $clone->selected = true;
        return $clone;
    }

    private function selectCancel(): self
    {
        $clone = clone $this;
        $clone->selected = false;
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

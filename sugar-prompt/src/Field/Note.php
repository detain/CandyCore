<?php

declare(strict_types=1);

namespace CandyCore\Prompt\Field;

use CandyCore\Core\Msg;
use CandyCore\Prompt\Field;
use CandyCore\Prompt\HasDynamicLabels;
use CandyCore\Prompt\HasHideFunc;

/**
 * Read-only paragraph. Renders title + description; tab navigation
 * skips over it.
 */
final class Note implements Field
{
    use HasHideFunc;
    use HasDynamicLabels;

    public function __construct(
        public readonly string $key,
        public readonly string $title = '',
        public readonly string $description = '',
    ) {}

    public static function new(string $key): self
    {
        return new self($key);
    }

    public function withTitle(string $t): self       { return new self($this->key, $t, $this->description); }
    public function withDescription(string $d): self { return new self($this->key, $this->title, $d); }

    public function key(): string         { return $this->key; }
    public function value(): mixed        { return null; }
    public function focus(): array        { return [$this, null]; }
    public function blur(): Field         { return $this; }
    public function update(Msg $msg): array { return [$this, null]; }

    public function view(): string
    {
        $parts = [];
        $title = $this->resolveTitle($this->title);
        $desc  = $this->resolveDescription($this->description);
        if ($title !== '') { $parts[] = $title; }
        if ($desc  !== '') { $parts[] = $desc; }
        return implode("\n", $parts);
    }

    public function isFocused(): bool         { return false; }
    public function getTitle(): string        { return $this->resolveTitle($this->title); }
    public function getDescription(): string  { return $this->resolveDescription($this->description); }
    public function getError(): ?string       { return null; }
    public function skippable(): bool         { return true; }
    public function consumes(Msg $msg): bool  { return false; }
}

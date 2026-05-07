<?php

declare(strict_types=1);

namespace SugarCraft\Prompt;

/**
 * One page of fields in a multi-page {@see Form}.
 *
 * Mirrors charmbracelet/huh's `huh.Group`. Carries a title, optional
 * description, and an optional `hideFunc` predicate that the runtime
 * evaluates on page transitions — when it returns true the group is
 * skipped entirely.
 *
 * Construct via {@see new()} (variadic Field) or {@see fromList()}.
 *
 * Use the chainable `withTitle` / `withDescription` / `withHideFunc`
 * setters to build up a group:
 *
 * ```php
 * $page1 = Group::new(
 *     Input::new('name')->withTitle('Your name'),
 *     Confirm::new('agree')->withTitle('Agree?'),
 * )
 *     ->withTitle('Step 1')
 *     ->withDescription('Tell us about yourself.');
 *
 * $page2 = Group::new(
 *     Note::new('thanks')->withTitle('Thank you!'),
 * )
 *     ->withHideFunc(static fn(array $values): bool => empty($values['agree']));
 * ```
 */
final class Group
{
    /**
     * @param list<Field>                            $fields
     * @param ?\Closure(array<string,mixed>): bool   $hideFunc
     */
    private function __construct(
        public readonly array $fields,
        public readonly string $title,
        public readonly string $description,
        public readonly ?\Closure $hideFunc,
        public readonly bool $showHelp = true,
        public readonly ?Theme $theme = null,
    ) {}

    public static function new(Field ...$fields): self
    {
        return new self(array_values($fields), '', '', null);
    }

    /** @param list<Field> $fields */
    public static function fromList(array $fields): self
    {
        return new self(array_values($fields), '', '', null);
    }

    public function withTitle(string $title): self
    {
        return new self($this->fields, $title, $this->description, $this->hideFunc, $this->showHelp, $this->theme);
    }

    public function withDescription(string $desc): self
    {
        return new self($this->fields, $this->title, $desc, $this->hideFunc, $this->showHelp, $this->theme);
    }

    /**
     * Per-group help-bar toggle. When false, the {@see Form} suppresses
     * the help line while this group is active. Default true. Mirrors
     * huh's `Group.WithShowHelp`.
     */
    public function withShowHelp(bool $on = true): self
    {
        return new self($this->fields, $this->title, $this->description, $this->hideFunc, $on, $this->theme);
    }

    /**
     * Override the {@see Form} theme while this group is active. Useful
     * when a particular page wants a different palette (e.g. a "danger"
     * page in red). Pass null to inherit the form-level theme. Mirrors
     * huh's `Group.WithTheme`.
     */
    public function withTheme(?Theme $theme): self
    {
        return new self($this->fields, $this->title, $this->description, $this->hideFunc, $this->showHelp, $theme);
    }

    /**
     * Predicate evaluated on page transitions; receives the values
     * collected so far (keyed by field key) and returns true to hide
     * the group. Hidden groups are skipped — both their fields and
     * their values are excluded from the final form result.
     *
     * @param ?\Closure(array<string,mixed>): bool $fn
     */
    public function withHideFunc(?\Closure $fn): self
    {
        return new self($this->fields, $this->title, $this->description, $fn, $this->showHelp, $this->theme);
    }

    /** @param array<string,mixed> $values */
    public function isHidden(array $values): bool
    {
        return $this->hideFunc !== null && ($this->hideFunc)($values) === true;
    }

    /** @param list<Field>|null $fields */
    public function withFields(array $fields): self
    {
        return new self(array_values($fields), $this->title, $this->description, $this->hideFunc, $this->showHelp, $this->theme);
    }
}

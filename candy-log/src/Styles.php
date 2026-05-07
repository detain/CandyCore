<?php

declare(strict_types=1);

namespace SugarCraft\Log;

use SugarCraft\Core\Util\Color;
use SugarCraft\Sprinkles\Style;

/**
 * Styles for log level rendering in text output.
 * Mirrors charmbracelet/log's Styles / DefaultStyles.
 */
final class Styles
{
    /** @var array<int, Style> keyed by Level->value */
    public array $levels = [];

    /** @var array<string, Style> */
    public array $keys = [];

    /** @var array<string, Style> */
    public array $values = [];

    public Style $timestamp;
    public Style $prefix;
    public Style $caller;
    public Style $message;

    public function __construct()
    {
        $this->timestamp = Style::new()->foreground(Color::ansi(8));
        $this->prefix = Style::new()->foreground(Color::ansi(5));
        $this->caller = Style::new()->foreground(Color::ansi(8));
        $this->message = Style::new();

        foreach (Level::cases() as $level) {
            $this->levels[$level->value] = match ($level) {
                Level::Debug => Style::new()->foreground(Color::ansi(8)),
                Level::Info  => Style::new()->foreground(Color::ansi(4)),
                Level::Warn  => Style::new()->foreground(Color::ansi(3)),
                Level::Error => Style::new()->foreground(Color::ansi(1))->bold(),
                Level::Fatal => Style::new()->foreground(Color::ansi(7))->background(Color::ansi(1))->bold(),
            };
        }
    }

    public static function default(): self
    {
        return new self();
    }
}

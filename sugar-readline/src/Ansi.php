<?php

declare(strict_types=1);

namespace SugarCraft\Readline;

/**
 * Tiny ANSI helper. Wraps `text` in `\x1b[<codes>m...\x1b[0m`.
 * Empty `codes` is a no-op so callers can pass a configurable style.
 */
final class Ansi
{
    public static function wrap(string $text, string $codes): string
    {
        if ($codes === '') {
            return $text;
        }
        return "\x1b[{$codes}m{$text}\x1b[0m";
    }

    private function __construct() {}
}

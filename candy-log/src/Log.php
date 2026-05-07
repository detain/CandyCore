<?php

declare(strict_types=1);

namespace SugarCraft\Log;

/**
 * Static facade over a process-wide default {@see Logger}.
 *
 * `Log::debug('hi')` is equivalent to `Log::default()->debug('hi')`.
 * Swap the default logger with `Log::setLogger($custom)` (handy for tests).
 *
 * Lives in its own class because PHP can't have static and instance methods
 * sharing a name on `Logger` itself.
 */
final class Log
{
    private static ?Logger $default = null;

    /** Get (or lazily create) the process-wide default logger. */
    public static function default(): Logger
    {
        return self::$default ??= Logger::new();
    }

    /** Replace the process-wide default logger. */
    public static function setLogger(Logger $logger): void
    {
        self::$default = $logger;
    }

    /** Reset the default logger so the next call rebuilds it. */
    public static function reset(): void
    {
        self::$default = null;
    }

    public static function debug(string $message, array $context = []): void
    {
        self::default()->log(Level::Debug, $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::default()->log(Level::Info, $message, $context);
    }

    public static function warn(string $message, array $context = []): void
    {
        self::default()->log(Level::Warn, $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::default()->log(Level::Error, $message, $context);
    }

    public static function fatal(string $message, array $context = []): void
    {
        self::default()->log(Level::Fatal, $message, $context);
    }

    /** Always print, ignoring level filters (uses Info under the hood). */
    public static function print(string $message, array $context = []): void
    {
        self::default()->log(Level::Info, $message, $context);
    }
}

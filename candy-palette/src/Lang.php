<?php

declare(strict_types=1);

namespace SugarCraft\Palette;

use SugarCraft\Core\I18n\T;

/**
 * Per-library translation facade for candy-palette.
 *
 * Wraps the shared {@see \SugarCraft\Core\I18n\T} registry with the
 * `'palette'` namespace baked in. Translated strings live in
 * {@see ../lang/en.php}.
 *
 * @see \SugarCraft\Core\Lang for the same pattern in candy-core.
 */
final class Lang
{
    private const NAMESPACE = 'palette';
    private const DIR       = __DIR__ . '/../lang';

    /**
     * @param array<string, string|int|float> $params Placeholder values.
     */
    public static function t(string $key, array $params = []): string
    {
        T::register(self::NAMESPACE, self::DIR);
        return T::translate(self::NAMESPACE . '.' . $key, $params);
    }
}

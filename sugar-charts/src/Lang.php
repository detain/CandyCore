<?php

declare(strict_types=1);

namespace SugarCraft\Charts;

use SugarCraft\Core\I18n\T;

/**
 * Per-library translation facade for sugar-charts.
 *
 * Wraps the shared {@see \SugarCraft\Core\I18n\T} registry with the
 * `'charts'` namespace baked in. Translated strings live in
 * {@see ../lang/en.php} (and any sibling `<locale>.php` files).
 *
 * @see \SugarCraft\Core\Lang for the same pattern in candy-core.
 */
final class Lang
{
    private const NAMESPACE = 'charts';
    private const DIR       = __DIR__ . '/../lang';

    /**
     * Translate a sugar-charts key.
     *
     * @param string                          $key    Sub-key without the
     *                                                `charts.` prefix
     *                                                (e.g. `'heatmap.coords_nonneg'`).
     * @param array<string, string|int|float> $params Placeholder values for
     *                                                `{name}` substitution.
     */
    public static function t(string $key, array $params = []): string
    {
        T::register(self::NAMESPACE, self::DIR);
        return T::translate(self::NAMESPACE . '.' . $key, $params);
    }
}

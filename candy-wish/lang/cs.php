<?php

/**
 * Czech translations for candy-wish.
 *
 * @return array<string, string>
 */

declare(strict_types=1);

return [
    'middleware.cannot_open_stderr' => 'nelze otevřít php://stderr',
    'middleware.stderr_not_resource' => 'stderr musí být prostředek',
    'logger.cannot_open_target'      => 'nelze otevřít cíl logu: {target}',
    'logger.invalid_target'          => 'Cíl loggeru musí být cesta, prostředek nebo null',
    'bubbletea.bad_factory'          => 'BubbleTea factory musí vracet objekt s metodou run(); obdrženo: {got}',
];

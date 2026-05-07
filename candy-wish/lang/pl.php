<?php

/**
 * Polish translations for candy-wish.
 *
 * @return array<string, string>
 */

declare(strict_types=1);

return [
    'middleware.cannot_open_stderr' => 'nie można otworzyć php://stderr',
    'middleware.stderr_not_resource' => 'stderr musi być zasobem',
    'logger.cannot_open_target'      => 'nie można otworzyć celu logowania: {target}',
    'logger.invalid_target'          => 'Cel loggera musi być ścieżką, zasobem lub null',
    'bubbletea.bad_factory'          => 'Fabryka BubbleTea musi zwracać obiekt z metodą run(); otrzymano: {got}',
];

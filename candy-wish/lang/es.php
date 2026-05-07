<?php

/**
 * Spanish translations for candy-wish.
 *
 * @return array<string, string>
 */

declare(strict_types=1);

return [
    'middleware.cannot_open_stderr' => 'no se puede abrir php://stderr',
    'middleware.stderr_not_resource' => 'stderr debe ser un recurso',
    'logger.cannot_open_target'      => 'no se puede abrir el destino del log: {target}',
    'logger.invalid_target'          => 'El destino del logger debe ser ruta, recurso o null',
    'bubbletea.bad_factory'          => 'La fábrica de BubbleTea debe devolver un objeto con un método run(); obtenido: {got}',
];

<?php

/**
 * Spanish translations for sugar-wishlist.
 *
 * @return array<string, string>
 */

declare(strict_types=1);

return [
    'launcher.no_pcntl'        => 'pcntl_exec no disponible; se requiere ext-pcntl',
    'launcher.exec_failed'     => 'falló la ejecución de {bin}',
    'config.not_found'         => 'Configuración de wishlist no encontrada: {path}',
    'config.json_top_level'    => 'wishlist json: el valor de nivel superior debe ser un array',
    'config.json_entry_object' => 'wishlist json: cada entrada debe ser un objeto',
    'config.yaml_continuation' => "wishlist yaml: continuación antes de cualquier bloque '- name:'",
    'config.yaml_unparseable'  => 'wishlist yaml: línea no analizable: {line}',
    'config.entry_missing_field' => 'entrada wishlist sin campo requerido: name o host',

    // bin/wishlist
    'cli.usage'         => 'Uso: wishlist [--config <ruta>] [--ssh <binario-ssh>]',
    'cli.unknown_arg'   => 'wishlist: argumento desconocido: {arg}',
    'cli.no_config'     => 'wishlist: no se encontró configuración. Pase --config <ruta>.',
    'cli.error'         => 'wishlist: {message}',
    'cli.cancelled'     => 'wishlist: cancelado.',
];

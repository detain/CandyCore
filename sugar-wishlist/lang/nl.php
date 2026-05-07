<?php

/**
 * Dutch translations for sugar-wishlist.
 *
 * @return array<string, string>
 */

declare(strict_types=1);

return [
    'launcher.no_pcntl'        => 'pcntl_exec niet beschikbaar; ext-pcntl vereist',
    'launcher.exec_failed'     => 'uitvoeren van {bin} mislukt',
    'config.not_found'         => 'wishlist-configuratie niet gevonden: {path}',
    'config.json_top_level'    => 'wishlist json: bovenste waarde moet een array zijn',
    'config.json_entry_object' => 'wishlist json: elke entry moet een object zijn',
    'config.yaml_continuation' => "wishlist yaml: Continuation voor elk '- name:'-blok",
    'config.yaml_unparseable'  => 'wishlist yaml: onleesbare regel: {line}',
    'config.entry_missing_field' => 'wishlist-entry met ontbrekend verplicht veld: name of host',
    'cli.usage'         => 'Gebruik: wishlist [--config <pad>] [--ssh <ssh-binair>]',
    'cli.unknown_arg'   => 'wishlist: onbekend argument: {arg}',
    'cli.no_config'     => 'wishlist: geen configuratie gevonden. Geef --config <pad> door.',
    'cli.error'         => 'wishlist: {message}',
    'cli.cancelled'     => 'wishlist: geannuleerd.',
];

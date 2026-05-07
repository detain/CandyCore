<?php

/**
 * Russian translations for sugar-wishlist.
 *
 * @return array<string, string>
 */

declare(strict_types=1);

return [
    'launcher.no_pcntl'        => 'pcntl_exec недоступен; требуется ext-pcntl',
    'launcher.exec_failed'     => 'не удалось выполнить {bin}',
    'config.not_found'         => 'Конфигурация wishlist не найдена: {path}',
    'config.json_top_level'    => 'wishlist json: значение верхнего уровня должно быть массивом',
    'config.json_entry_object' => 'wishlist json: каждая запись должна быть объектом',
    'config.yaml_continuation' => "wishlist yaml: продолжение перед любым блоком '- name:'",
    'config.yaml_unparseable'  => 'wishlist yaml: неразбираемая строка: {line}',
    'config.entry_missing_field' => 'запись wishlist с отсутствующим обязательным полем: name или host',
    'cli.usage'         => 'Использование: wishlist [--config <путь>] [--ssh <ssh-бинарник>]',
    'cli.unknown_arg'   => 'wishlist: неизвестный аргумент: {arg}',
    'cli.no_config'     => 'wishlist: конфигурация не найдена. Передайте --config <путь>.',
    'cli.error'         => 'wishlist: {message}',
    'cli.cancelled'     => 'wishlist: отменено.',
];

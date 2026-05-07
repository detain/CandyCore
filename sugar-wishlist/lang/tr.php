<?php

/**
 * Turkish translations for sugar-wishlist.
 *
 * @return array<string, string>
 */

declare(strict_types=1);

return [
    'launcher.no_pcntl'        => 'pcntl_exec kullanılamıyor; ext-pcntl gerekli',
    'launcher.exec_failed'     => '{bin} çalıştırılamadı',
    'config.not_found'         => 'wishlist yapılandırması bulunamadı: {path}',
    'config.json_top_level'    => 'wishlist json: üst düzey değer bir dizi olmalıdır',
    'config.json_entry_object' => 'wishlist json: her giriş bir nesne olmalıdır',
    'config.yaml_continuation' => "wishlist yaml: herhangi bir '- name:' bloğundan önce Devamlılık ayarlanamaz",
    'config.yaml_unparseable'  => 'wishlist yaml: ayrıştırılamayan satır: {line}',
    'config.entry_missing_field' => 'eksik zorunlu alana sahip wishlist girişi: name veya host',
    'cli.usage'         => 'Kullanım: wishlist [--config <yol>] [--ssh <ssh-ikili>]',
    'cli.unknown_arg'   => 'wishlist: bilinmeyen argüman: {arg}',
    'cli.no_config'     => 'wishlist: yapılandırma bulunamadı. --config <yol> iletin.',
    'cli.error'         => 'wishlist: {message}',
    'cli.cancelled'     => 'wishlist: iptal edildi.',
];

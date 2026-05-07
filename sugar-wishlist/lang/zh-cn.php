<?php

/**
 * Simplified Chinese translations for sugar-wishlist.
 *
 * @return array<string, string>
 */

declare(strict_types=1);

return [
    'launcher.no_pcntl'        => 'pcntl_exec 不可用；需要 ext-pcntl',
    'launcher.exec_failed'     => '执行 {bin} 失败',
    'config.not_found'         => '未找到 wishlist 配置：{path}',
    'config.json_top_level'    => 'wishlist json：顶层值必须是数组',
    'config.json_entry_object' => 'wishlist json：每个条目必须是对象',
    'config.yaml_continuation' => "wishlist yaml：在任何 '- name:' 块之前不能有续行",
    'config.yaml_unparseable'  => 'wishlist yaml：无法解析的行：{line}',
    'config.entry_missing_field' => 'wishlist 条目缺少必需字段：name 或 host',
    'cli.usage'                => '用法：wishlist [--config <路径>] [--ssh <ssh二进制>]',
    'cli.unknown_arg'          => 'wishlist：未知参数：{arg}',
    'cli.no_config'            => 'wishlist：未找到配置。请传递 --config <路径>。',
    'cli.error'                => 'wishlist：{message}',
    'cli.cancelled'            => 'wishlist：已取消。',
];

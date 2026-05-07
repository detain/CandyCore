<?php

/**
 * English (default) translations for candy-serve.
 *
 * @return array<string, string>
 */

declare(strict_types=1);

return [
    'user.invalid_ssh_key'     => 'Invalid SSH public key format',
    'repo.create_dir_failed'   => 'Failed to create repo directory: {path}',
    'repo.git_init_failed'     => 'git init failed: {output}',
    'config.not_found'         => 'Config file not found: {path}',
    'config.read_failed'       => 'Failed to read config: {path}',
    'ssh.user_cannot_create'   => 'User {viewer} cannot create repos',
];

<?php

declare(strict_types=1);

namespace SugarCraft\Wishlist;

/**
 * Replaces the current PHP process with `ssh(1)` connecting to the
 * chosen {@see Endpoint}. Uses `pcntl_exec()` so file descriptors,
 * environment, controlling tty, and exit status all flow through
 * unchanged — the user sees a normal `ssh` session, including
 * host-key prompts, agent forwarding, and the standard MOTD.
 *
 * The `dispatch()` method does not return on success; it only
 * returns when `pcntl_exec` itself fails (typically because the
 * `ssh` binary isn't on `$PATH`).
 *
 * For tests, `executor` is a callable receiving the argv list —
 * defaults to `pcntl_exec` but tests can swap it for a recorder.
 */
final class Launcher
{
    /** @var callable(string,list<string>): void */
    private $executor;

    /**
     * @param callable(string,list<string>): void|null $executor
     */
    public function __construct(?callable $executor = null)
    {
        $this->executor = $executor ?? static function (string $bin, array $args): void {
            // pcntl_exec wants the binary path + arg list (without
            // argv[0]). On success it never returns.
            if (!function_exists('pcntl_exec')) {
                throw new \RuntimeException('pcntl_exec unavailable; ext-pcntl required');
            }
            \pcntl_exec($bin, $args);
            // If we got here, exec failed.
            throw new \RuntimeException("failed to exec {$bin}");
        };
    }

    /**
     * Dispatch into the chosen endpoint. On success, the PHP
     * process is replaced and this method does not return.
     */
    public function dispatch(Endpoint $e, string $sshBinary = '/usr/bin/ssh'): void
    {
        $argv = $e->toSshArgv($sshBinary);
        ($this->executor)($argv[0], array_slice($argv, 1));
    }
}

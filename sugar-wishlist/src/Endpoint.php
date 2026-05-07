<?php

declare(strict_types=1);

namespace SugarCraft\Wishlist;

/**
 * One row in a wishlist directory — a host the user can pick to
 * `ssh` into. Held as an immutable value object; build via
 * `new Endpoint(...)` or via {@see Config::load()}.
 *
 * `name` is what's shown in the picker; everything else feeds the
 * `ssh(1)` argv when {@see Launcher} dispatches.
 */
final class Endpoint
{
    public function __construct(
        public readonly string  $name,
        public readonly string  $host,
        public readonly int     $port = 22,
        public readonly ?string $user = null,
        public readonly ?string $identityFile = null,
        public readonly ?string $description = null,
        /** @var list<string> Extra `-o KEY=VALUE` options for ssh */
        public readonly array   $options = [],
    ) {}

    /**
     * Build the argv list for `ssh(1)`. The first element is the
     * binary name (`ssh`); the rest is the flags + destination.
     * Suitable for `pcntl_exec($argv[0], array_slice($argv, 1))`.
     *
     * @return list<string>
     */
    public function toSshArgv(string $binary = 'ssh'): array
    {
        $argv = [$binary];
        if ($this->port !== 22) {
            $argv[] = '-p';
            $argv[] = (string) $this->port;
        }
        if ($this->identityFile !== null && $this->identityFile !== '') {
            $argv[] = '-i';
            $argv[] = $this->identityFile;
        }
        foreach ($this->options as $opt) {
            $argv[] = '-o';
            $argv[] = $opt;
        }
        $argv[] = $this->user !== null && $this->user !== ''
            ? "{$this->user}@{$this->host}"
            : $this->host;
        return $argv;
    }

    public function displayLine(): string
    {
        $dest = ($this->user !== null && $this->user !== '' ? "{$this->user}@" : '') . $this->host;
        if ($this->port !== 22) {
            $dest .= ":{$this->port}";
        }
        return "{$this->name}  ─  {$dest}";
    }
}

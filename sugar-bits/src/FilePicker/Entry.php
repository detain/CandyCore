<?php

declare(strict_types=1);

namespace CandyCore\Bits\FilePicker;

/**
 * One filesystem entry the {@see FilePicker} has discovered.
 */
final class Entry
{
    public function __construct(
        public readonly string $name,
        public readonly bool $isDir,
        public readonly bool $isHidden,
    ) {}

    public function path(string $cwd): string
    {
        return rtrim($cwd, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $this->name;
    }

    public function display(): string
    {
        return $this->isDir ? $this->name . '/' : $this->name;
    }
}

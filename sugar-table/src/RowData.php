<?php

declare(strict_types=1);

namespace CandyCore\Table;

/**
 * Row data as a key-value map.
 *
 * Keys correspond to Column keys. Values may be raw scalars or StyledCell.
 *
 * Port of Evertras/bubble-table RowData.
 *
 * @see https://github.com/Evertras/bubble-table
 */
final class RowData
{
    /** @var array<string, mixed> */
    private array $data;

    private function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function from(array $data): self
    {
        return new self($data);
    }

    /**
     * Get a cell value by column key.
     *
     * @return mixed|null  null if key not present
     */
    public function get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    /**
     * Check if a key is present in this row.
     */
    public function has(string $key): bool
    {
        return \array_key_exists($key, $this->data);
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return $this->data;
    }

    public function with(string $key, mixed $value): self
    {
        $clone = clone $this;
        $clone->data[$key] = $value;
        return $clone;
    }
}

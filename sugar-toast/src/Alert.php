<?php

declare(strict_types=1);

namespace CandyCore\Toast;

/**
 * A single alert entry in the queue.
 */
final class Alert
{
    public function __construct(
        public readonly ToastType $type,
        public readonly string $message,
        public readonly ?float $expiresAt = null,  // seconds since epoch
    ) {}

    public function isExpired(): bool
    {
        if ($this->expiresAt === null) return false;
        return \microtime(true) >= $this->expiresAt;
    }

    public function withExpiry(float $duration): self
    {
        return new self($this->type, $this->message, \microtime(true) + $duration);
    }
}

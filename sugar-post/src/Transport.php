<?php

declare(strict_types=1);

namespace CandyCore\Post;

/**
 * Pluggable transport for sending Email messages.
 */
interface Transport
{
    /**
     * Send the email.
     * Throws \RuntimeException on failure.
     */
    public function send(Email $email): void;

    /**
     * Name of this transport for logging/debugging.
     */
    public function name(): string;
}

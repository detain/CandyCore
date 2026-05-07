<?php

declare(strict_types=1);

namespace SugarCraft\Post;

/**
 * High-level email sender wrapping a Transport.
 */
final class Mailer
{
    private Transport $transport;

    public function __construct(Transport $transport)
    {
        $this->transport = $transport;
    }

    /**
     * Send an email via the configured transport.
     *
     * @throws \RuntimeException if the transport fails.
     */
    public function send(Email $email): void
    {
        if ($email->to === [] && $email->cc === [] && $email->bcc === []) {
            throw new \InvalidArgumentException('Email must have at least one recipient (to, cc, or bcc)');
        }
        if ($email->from === []) {
            throw new \InvalidArgumentException('Email must have a from address');
        }

        $this->transport->send($email);
    }

    /**
     * Get the transport name for logging.
     */
    public function transportName(): string
    {
        return $this->transport->name();
    }
}

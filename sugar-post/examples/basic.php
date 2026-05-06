<?php

declare(strict_types=1);

/**
 * SugarPost basic usage — Resend API.
 *
 * Run: RESEND_API_KEY=re_xxx php examples/basic.php
 */

require __DIR__ . '/../vendor/autoload.php';

use CandyCore\Post\{Email, Mailer, ResendTransport};

$apiKey = \getenv('RESEND_API_KEY') ?: 're_placeholder';
$transport = new ResendTransport($apiKey);
$mailer = new Mailer($transport);

$email = new Email(
    from:    ['sender@example.com'],
    to:      ['recipient@example.com'],
    subject: 'Hello from SugarPost',
    body:    <<<BODY
Hello!

This email was sent via the Resend API using SugarPost.

Best,
The SugarPost Team
BODY,
);

try {
    $mailer->send($email);
    echo "✓ Email sent successfully.\n";
} catch (\Throwable $e) {
    echo "✗ Send failed: {$e->getMessage()}\n";
}

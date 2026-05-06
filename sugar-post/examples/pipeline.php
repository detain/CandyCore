<?php

declare(strict_types=1);

/**
 * SugarPost CLI pipeline demo — pipe markdown → email.
 *
 * Run: cat message.md | php examples/pipeline.php
 * Or:  echo "Hello world" | php examples/pipeline.php
 */

require __DIR__ . '/../vendor/autoload.php';

use CandyCore\Post\{Email, Mailer, ResendTransport};

// Read body from STDIN
$body = '';
while (!\feof(STDIN)) {
    $body .= \fgets(STDIN);
}
$body = \trim($body);

if ($body === '') {
    echo "No body from STDIN — try: echo 'Hello' | php examples/pipeline.php\n";
    exit(1);
}

$transport = new ResendTransport(\getenv('RESEND_API_KEY') ?: 're_placeholder');
$mailer = new Mailer($transport);

$email = new Email(
    from:    [\getenv('POP_FROM') ?: 'sender@example.com'],
    to:      [$argv[1] ?? 'recipient@example.com'],
    subject: $argv[2] ?? 'Hello',
    body:    $body,
);

try {
    $mailer->send($email);
    echo "✓ Sent.\n";
} catch (\Throwable $e) {
    echo "✗ {$e->getMessage()}\n";
}

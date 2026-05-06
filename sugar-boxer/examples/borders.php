<?php

declare(strict_types=1);

/**
 * SugarBoxer — no border and padding variations.
 *
 * Run: php examples/borders.php
 */

require __DIR__ . '/../vendor/autoload.php';

use CandyCore\Boxer\{Node, SugarBoxer};

$boxer = SugarBoxer::new();

// No border between panels
$flat = $boxer->horizontal(
    Node::leaf('NO')->withBorder(false)->withMinWidth(5),
    Node::leaf('BORDER')->withBorder(false)->withMinWidth(10),
    Node::leaf('HERE')->withBorder(false)->withMinWidth(8),
)->withBorder(false)->withSpacing(1);

echo "=== No-border layout ===\n";
echo $boxer->render($flat, 40, 5);

// With padding
$padded = Node::leaf('PADDED CONTENT')->withPadding(3)->withBorder(true)->withMinWidth(15);
echo "\n=== With padding (3 cells) ===\n";
echo $boxer->render($padded, 30, 8);

// NoBorder wrapper
$nested = Node::noBorder(Node::leaf('Inside noborder wrapper')->withBorder(false));
echo "\n=== NoBorder wrapper ===\n";
echo $boxer->render($nested, 35, 5);

<?php

declare(strict_types=1);

/**
 * SugarBoxer basic demo — H/V panels with borders.
 *
 * Run: php examples/basic.php
 */

require __DIR__ . '/../vendor/autoload.php';

use SugarCraft\Boxer\{Node, SugarBoxer};

$boxer = SugarBoxer::new();

// Simple horizontal split
$h = $boxer->horizontal(
    Node::leaf('Apple')->withMinWidth(8),
    Node::leaf('Banana')->withMinWidth(8),
    Node::leaf('Cherry')->withMinWidth(8),
)->withBorder(true)->withSpacing(1);

echo "=== Horizontal split (3 panels) ===\n";
echo $boxer->render($h, 50, 5);

// Simple vertical split
$v = $boxer->vertical(
    Node::leaf('TOP SECTION')->withMinWidth(20)->withMinHeight(3),
    Node::leaf('BOTTOM SECTION')->withMinWidth(20)->withMinHeight(3),
)->withBorder(true);

echo "\n=== Vertical split (2 panels) ===\n";
echo $boxer->render($v, 30, 10);

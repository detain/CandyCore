<?php

declare(strict_types=1);

/**
 * SugarReadline — interactive prompts demo.
 *
 * Run: php examples/basic.php
 */

require __DIR__ . '/../vendor/autoload.php';

use SugarCraft\Readline\{ConfirmationPrompt, SelectionPrompt, TextPrompt, TextareaPrompt};

// ---- Text Prompt ----
echo "=== Text Prompt ===\n";
$p = TextPrompt::new('Your name: ')
    ->WithDefault('Anonymous')
    ->WithCompletions(['Alice', 'Bob', 'Carol', 'Dave']);

echo $p->View() . "\n\n";

// Simulate typing
$p = $p->HandleChar('A')->HandleChar('l');
echo "After typing 'Al':\n" . $p->View() . "\n\n";

// Tab to complete
$p = $p->HandleKey('tab');
echo "After Tab-completion:\n" . $p->View() . "\n\n";

// ---- Selection Prompt ----
echo "=== Selection Prompt ===\n";
$s = SelectionPrompt::new('Pick a fruit:', ['Apple', 'Banana', 'Cherry', 'Date', 'Elderberry']);
echo $s->View() . "\n\n";

// Filter
$s = $s->Filter('er');
echo "After filter 'er':\n" . $s->View() . "\n\n";

// Navigate down
$s = $s->HandleKey('down');
echo "After down arrow:\n" . $s->View() . "\n\n";

// Confirm
$s = $s->Confirm();
echo "Selected: " . $s->SelectedValue() . "\n\n";

// ---- Confirmation Prompt ----
echo "=== Confirmation Prompt ===\n";
$c = ConfirmationPrompt::new('Delete the file?')
    ->WithConfirmLabel('Yes, delete')
    ->WithCancelLabel('No, keep');

echo $c->View() . "\n";
$c = $c->HandleKey('n');
echo "After pressing 'n':\n" . $c->View() . "\n";
$c = $c->Confirm();
echo "Result: " . ($c->Result() ? 'confirmed' : 'cancelled') . "\n\n";

// ---- Textarea Prompt ----
echo "=== Textarea Prompt ===\n";
$t = TextareaPrompt::new('Description:')
    ->WithMaxLines(5)
    ->WithDefault("Line one\nLine two");

echo $t->View() . "\n";
echo "Value:\n" . $t->Value() . "\n";

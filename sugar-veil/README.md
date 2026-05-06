# SugarVeil

PHP port of [rmhubbert/bubbletea-overlay](https://github.com/rmhubbert/bubbletea-overlay) — modal/overlay compositing for terminal UIs. Composite one string (foreground) over another (background) at any position with optional pixel offsets.

## Features

- **9 position modes**: Top, Right, Bottom, Left, Center, and the 4 corners (TopRight, BottomRight, BottomLeft, TopLeft)
- **Pixel-precise offsets**: X/Y offsets fine-tune any position
- **Pure rendering**: composites any background + foreground strings
- **Works with any TUI framework**: render your models first, then composite
- **No dependencies**: pure PHP, no FFI

## Install

```bash
composer require candycore/sugar-veil
```

## Quick Start

```php
use CandyCore\Veil\Veil;

$veil = Veil::new();

// Background: a 40x10 box
$bg = "┌──────────────────────────────────────┐\n" .
      "│         Main Application             │\n" .
      "│                                      │\n" .
      "│   [content]                          │\n" .
      "└──────────────────────────────────────┘";

// Foreground: a smaller overlay
$fg = "╔════════╗\n║ MODAL  ║\n╚════════╝";

// Composite fg centered over bg
$output = $veil->composite($fg, $bg, Position::CENTER, Position::CENTER);
echo $output;
```

## Positioning

```php
$veil->composite(
    string  $foreground,
    string  $background,
    Position $vertical,    // TOP | CENTER | BOTTOM
    Position $horizontal,  // LEFT | CENTER | RIGHT
    int      $xOffset = 0, // shift right (+N) or left (-N) cells
    int      $yOffset = 0  // shift down  (+N) or up   (-N) lines
): string
```

## Corner positions

```php
// Top-right corner
$veil->composite($fg, $bg, Position::TOP, Position::RIGHT);

// Bottom-left corner with offset
$veil->composite($fg, $bg, Position::BOTTOM, Position::LEFT, xOffset: 2, yOffset: -1);
```

## License

[MIT](LICENSE)

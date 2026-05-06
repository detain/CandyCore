# SugarBoxer

PHP port of [treilik/bubbleboxer](https://github.com/treilik/bubbleboxer) — box-drawing layout engine for composing terminal content into H/V panel layouts with borders and padding.

## Features

- **H/V composition** — build arbitrary layouts by nesting `horizontal()` and `vertical()` panels
- **Box-drawing borders** — classic ANSI box characters (╭ ╮ ╰ ╯ │ ─ ├ ┤ ┬ ┴ ┼)
- **No-border mode** — render adjacent panels without separators
- **Per-panel padding** — inner whitespace around content
- **Width/Height hints** — nodes can specify min/max dimensions
- **Dynamic dimension calculation** — boxer computes total viewport from children
- **Leaf content** — any stringable content at leaf nodes
- **Pure renderer** — outputs ANSI box-drawing strings; works with any TUI framework

## Install

```bash
composer require candycore/sugar-boxer
```

## Quick Start

```php
use CandyCore\Boxer\SugarBoxer;

$boxer = SugarBoxer::new();

$layout = $boxer->vertical(
    $boxer->horizontal(
        $boxer->leaf("Left panel"),
        $boxer->leaf("Right panel"),
    ),
    $boxer->leaf("Bottom bar"),
);

echo $boxer->render($layout, 60, 20);
```

## Layout API

```php
// Leaf node with string content
$boxer->leaf('Hello, World!');

// Horizontal split (side by side)
$boxer->horizontal(Node ...$children): Node

// Vertical split (stacked)
$boxer->vertical(Node ...$children): Node

// Node with explicit dimensions
$node->withMinWidth(20)
     ->withMaxWidth(80)
     ->withMinHeight(5)
     ->withMaxHeight(40)
     ->withPadding(1)           // inner padding
     ->withBorder(true)         // show box border
     ->withSpacing(1);          // gap between children

// No-border (flat) layout
$boxer->noBorder(Node): Node
```

## Border Characters

```
╭────┬────╮   ← top-left, top horiz, top-right, cross
│    │    │   ← vert bar
├────┼────┤   ← left-join, cross, right-join
╰────┴────╯   ← bottom-left, bottom horiz, bottom-right
```

## License

[MIT](LICENSE)

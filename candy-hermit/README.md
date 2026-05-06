# CandyHermit

PHP port of [Genekkion/theHermit](https://github.com/Genekkion/theHermit) — fuzzy finder / quick-fix overlay for terminal UIs. Renders a filterable list overlay on top of a background view while the background continues to update.

## Features

- **Fuzzy filtering** — filter list items as you type
- **Overlay compositing** — background view renders underneath; overlay chars replace background at specified positions
- **Background continues updating** — The Hermit doesn't block the underlying view
- **Fully styleable** — custom filter prompt, item format, matching highlight
- **Pure renderer** — no terminal I/O; output is strings you manage

## Install

```bash
composer require candycore/candy-hermit
```

## Quick Start

```php
use CandyCore\Hermit\Hermit;

// Items to filter
$items = ['apple', 'banana', 'cherry', 'date', 'elderberry'];

// Create hermit with items
$h = Hermit::new($items)
    ->setPrompt('> ')
    ->setItemFormatter(fn($item, $selected) => ($selected ? '*' : ' ') . " $item");

// Show and type to filter
$h = $h->show();
$h = $h->type('ba');  // filter by 'ba'

echo $h->View("background content\nmore background");

// Navigate
$h = $h->cursorDown();
$h = $h->cursorUp();

// Select
$selected = $h->selected();  // currently selected item

// Hide
$h = $h->hide();
```

## Model Interface

Implement the `Model` interface to use Hermit inside a larger Bubble-Tea-style application:

```php
use CandyCore\Hermit\Model;

class MyModel implements Model {
    public function update(Hermit $hermit, string $msg): Model { ... }
    public function view(Hermit $hermit): string { ... }
}
```

## License

[MIT](LICENSE)

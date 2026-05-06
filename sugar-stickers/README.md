# SugarStickers

PHP port of [76creates/stickers](https://github.com/76creates/stickers) — Lipgloss utility building blocks. Provides FlexBox layout and Table components for terminal UIs.

## Features

### FlexBox
- **CSS flexbox-like** responsive grid layout for terminal UIs
- **Direction**: row (horizontal) or column (vertical)
- **Justify** content: start/center/end/space-between/space-around
- **Align** items: start/center/end/stretch
- **Gap** between items
- **Ratio-based sizing** — items with grow ratios fill available space
- **Wrap** — items wrap to next line/column when they exceed available space

### Table
- **Sortable columns** — click to sort by any column, ascending/descending
- **Filterable** — filter rows by cell content
- **Configurable columns** — title, width, alignment, formatter
- **Cell styling** — per-column ANSI style support
- **Cursor tracking** — get current row/cell on selection

## Install

```bash
composer require candycore/sugar-stickers
```

## FlexBox Quick Start

```php
use CandyCore\Stickers\Flex\{FlexBox, FlexItem};

$box = FlexBox::row(
    FlexItem::new('Panel A')->withRatio(1),
    FlexItem::new('Panel B')->withRatio(2),
    FlexItem::new('Panel C')->withRatio(1),
)->withGap(1);

echo $box->render(80, 24);
```

## Table Quick Start

```php
use CandyCore\Stickers\Table\{Table, Column};

$table = new Table();
$table->addColumn(Column::make('Name', 20));
$table->addColumn(Column::make('Age', 5)->withAlign('right'));
$table->addColumn(Column::make('City', 15));

$table->addRow(['Alice', 30, 'NYC']);
$table->addRow(['Bob',   25, 'LA']);
$table->addRow(['Carol', 35, 'Chicago']);

$table->sortBy(0);  // sort by Name column
$table->filter('a'); // filter rows

echo $table->render();
```

## License

[MIT](LICENSE)

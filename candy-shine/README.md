# CandyShine

PHP port of [charmbracelet/glamour](https://github.com/charmbracelet/glamour) —
Markdown → ANSI renderer built on top of `league/commonmark` and CandySprinkles.

```php
use CandyCore\Shine\Renderer;

echo (new Renderer())->render(<<<MD
# Welcome

A few **bold** and _italic_ words, with `inline code` and a [link](https://example.com).

- one
- two
- three
MD);
```

## Components

- **`Renderer`** — parses Markdown via `league/commonmark` and walks the
  AST producing styled ANSI text. Block-level nodes emit their own
  trailing newlines; inline nodes return inline strings.
- **`Theme`** — table of per-element `Sprinkles\Style` objects:
  `heading1..6`, `paragraph`, `bold`, `italic`, `code`, `codeBlock`,
  `link`, `blockquote`, `listMarker`, `rule`. Two factories ship out of
  the box: `Theme::ansi()` (colourful) and `Theme::plain()` (no colour).

## Test

```sh
cd candy-shine && composer install && vendor/bin/phpunit
```

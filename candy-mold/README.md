<img src=".assets/icon.png" alt="candy-mold" width="160" align="right">

# CandyMold

<!-- BADGES:BEGIN -->
[![CI](https://github.com/detain/sugarcraft/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/detain/sugarcraft/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/detain/sugarcraft/branch/master/graph/badge.svg?flag=candy-mold)](https://app.codecov.io/gh/detain/sugarcraft?flags%5B0%5D=candy-mold)
[![Packagist Version](https://img.shields.io/packagist/v/sugarcraft/candy-mold?label=packagist)](https://packagist.org/packages/sugarcraft/candy-mold)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/php-%E2%89%A58.1-8892bf.svg)](https://www.php.net/)
<!-- BADGES:END -->


![demo](.vhs/start.gif)

Skeleton repo for bootstrapping a SugarCraft TUI app. Pour your model into the mold and you've got a working app.

```bash
composer create-project sugarcraft/candy-mold my-app
cd my-app
./bin/start
```

and you'll see a working counter. Replace `src/Counter.php` with your own `Model`, keep editing.

## What you get

```
my-app/
├── composer.json     # requires candy-core + candy-sprinkles
├── phpunit.xml
├── bin/start         # entry point — runs Program(new Counter())
├── src/
│   └── Counter.php   # demo Model with up/down/quit, styled border
└── tests/
    └── CounterTest.php
```

`bin/start` is just three meaningful lines: load the autoloader, instantiate your `Model`, hand it to `Program::run()`. The Program harness owns the event loop, render tick, signal handling, raw-mode setup, and alt-screen lifecycle — you only write Models.

## Anatomy of a SugarCraft Model

A `Model` is three pure methods:

```php
public function init(): ?\Closure;            // optional startup Cmd (timers, fetch...)
public function update(Msg $msg): array;       // [nextModel, ?Cmd]
public function view(): string;                // current frame
```

The shape is borrowed verbatim from Bubble Tea / The Elm Architecture. State lives on the value object, transitions are pure functions, side effects (timers, HTTP, file I/O) get *scheduled* as Cmds rather than executed inline.

`update()` always returns a new `Model` rather than mutating `$this`. That's why the demo declares `public readonly int $n` — the only way to "change" the count is to construct a fresh Counter with the new value.

## Common next steps

| Want to…                             | Reach for…                                 |
|--------------------------------------|---------------------------------------------|
| Add a text input                     | `sugarcraft/sugar-bits` — `TextInput`        |
| Show a spinner while loading         | `sugarcraft/sugar-bits` — `Spinner`          |
| Render Markdown help text            | `sugarcraft/candy-shine` — `Renderer`        |
| Tail a log into a scrollable pane    | `sugarcraft/sugar-bits` — `Viewport`         |
| Build a multi-page wizard            | `sugarcraft/sugar-prompt` — `Group`          |
| Plot a sparkline                     | `sugarcraft/sugar-charts` — `Sparkline`      |
| Make it `ssh`-accessible             | `sugarcraft/candy-wish`                      |

Add the dep, import its classes, return them from `view()`. They're all pure renderers on the same `Style`-based vocabulary.

## Testing

```bash
composer install
vendor/bin/phpunit
```

The included `tests/CounterTest.php` shows how to test `update()` deterministically by constructing `Msg` objects directly. No event loop, no terminal, no mocking — just call methods and assert the returned tuple.

## License

MIT.

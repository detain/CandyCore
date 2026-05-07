<img src=".assets/icon.png" alt="honey-flap" width="160" align="right">

# HoneyFlap

<!-- BADGES:BEGIN -->
[![CI](https://github.com/detain/sugarcraft/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/detain/sugarcraft/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/detain/sugarcraft/branch/master/graph/badge.svg?flag=honey-flap)](https://app.codecov.io/gh/detain/sugarcraft?flags%5B0%5D=honey-flap)
[![Packagist Version](https://img.shields.io/packagist/v/sugarcraft/honey-flap?label=packagist)](https://packagist.org/packages/sugarcraft/honey-flap)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/php-%E2%89%A58.1-8892bf.svg)](https://www.php.net/)
<!-- BADGES:END -->


![demo](.vhs/play.gif)

Flappy-Bird-style game on the SugarCraft stack — port of [`kbrgl/flapioca`](https://github.com/kbrgl/flapioca). The bird's vertical motion is a HoneyBounce projectile (gravity + an upward velocity kick on each tap), pipes scroll left at a fixed cell rate, collision is per-cell.

## Run it

```bash
composer install
./bin/honey-flap
```

## Keys

| Key                | Action  |
|--------------------|---------|
| `Space` / `↑` / `w`| Flap    |
| `r`                | Restart |
| `q` / `Esc`        | Quit    |

## Architecture

| File            | Role                                                                           |
|-----------------|--------------------------------------------------------------------------------|
| `Bird`          | Wraps a HoneyBounce `Projectile` — gravity pulls it down, `flap()` resets vertical velocity to a fixed kick. |
| `Pipe`          | Single-column pipe pair with a centred gap. Slides left one cell per tick.     |
| `TickMsg`       | Frame-tick message scheduled by `Cmd::tick(0.033, …)` ≈ 30 fps.                |
| `Game` (Model)  | Pure-state world: bird + pipes + score + crashed flag. Injects PRNG closure for deterministic gap placements in tests. |
| `Renderer`      | Pure view — single playfield walk, ANSI-styled glyphs, rounded border.         |

The PRNG is injected as a `Closure(int $maxInclusive): int` so unit tests can pin the pipe layout to a specific sequence — the standard SugarCraft pattern.

## Test

```bash
composer install
vendor/bin/phpunit
```

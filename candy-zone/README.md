<img src=".assets/icon.png" alt="candy-zone" width="160" align="right">

# CandyZone

<!-- BADGES:BEGIN -->
[![CI](https://github.com/detain/sugarcraft/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/detain/sugarcraft/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/detain/sugarcraft/branch/master/graph/badge.svg?flag=candy-zone)](https://app.codecov.io/gh/detain/sugarcraft?flags%5B0%5D=candy-zone)
[![Packagist Version](https://img.shields.io/packagist/v/candycore/candy-zone?label=packagist)](https://packagist.org/packages/candycore/candy-zone)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/php-%E2%89%A58.1-8892bf.svg)](https://www.php.net/)
<!-- BADGES:END -->


PHP port of [lrstanley/bubblezone](https://github.com/lrstanley/bubblezone) —
mouse-zone tracker for TUI apps. Wrap rendered chunks with named markers,
let CandyZone discover their bounding boxes, then ask zones whether a
{@see \CandyCore\Core\Msg\MouseMsg} fell inside them.

```php
use CandyCore\Zone\Manager;
use CandyCore\Sprinkles\Style;

$z = Manager::newGlobal();

// Build a frame
$btnOk     = $z->mark('btn:ok',     Style::new()->padding(0, 2)->render('OK'));
$btnCancel = $z->mark('btn:cancel', Style::new()->padding(0, 2)->render('Cancel'));
$frame     = $btnOk . '   ' . $btnCancel;

// Scan once before printing — Manager records marker positions and strips them.
$displayable = $z->scan($frame);
echo $displayable;

// Later, when a MouseMsg arrives:
if ($z->get('btn:ok')?->inBounds($mouseMsg)) {
    // ...
}
```

Markers are APC escape sequences (`ESC _ ... ESC \`) — terminals ignore them,
so they don't affect layout. {@see Manager::scan()} computes each zone's
bounding box in 1-based terminal cells, accounting for ANSI styling and
Unicode width.

## Test

```sh
cd candy-zone && composer install && vendor/bin/phpunit
```

<img src=".assets/icon.png" alt="sugar-prompt" width="160" align="right">

# SugarPrompt

<!-- BADGES:BEGIN -->
[![CI](https://github.com/detain/sugarcraft/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/detain/sugarcraft/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/detain/sugarcraft/branch/master/graph/badge.svg?flag=sugar-prompt)](https://app.codecov.io/gh/detain/sugarcraft?flags%5B0%5D=sugar-prompt)
[![Packagist Version](https://img.shields.io/packagist/v/candycore/sugar-prompt?label=packagist)](https://packagist.org/packages/candycore/sugar-prompt)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/php-%E2%89%A58.1-8892bf.svg)](https://www.php.net/)
<!-- BADGES:END -->


![demo](.vhs/form.gif)

PHP port of [charmbracelet/huh](https://github.com/charmbracelet/huh) —
interactive form library built on top of CandyCore + SugarBits.

```php
use CandyCore\Prompt\Form;
use CandyCore\Prompt\Field\{Input, Confirm, Select, Note};

$form = Form::new(
    Note::new('welcome')->withTitle('Onboarding')
        ->withDescription('A few quick questions.'),
    Input::new('name')->withTitle('Your name?')->withPlaceholder('Ada Lovelace'),
    Confirm::new('newsletter')->withTitle('Subscribe to the newsletter?'),
    Select::new('lang')
        ->withTitle('Favorite language?')
        ->withOptions('PHP', 'Go', 'Rust', 'Python'),
);
// $form is a CandyCore Model — drop it into a Program.
```

## Field types (round 1)

- `Input`  — single-line text (wraps `SugarBits\TextInput`).
- `Note`   — read-only paragraph; navigated past with Tab.
- `Confirm`— y/n boolean.
- `Select` — single-choice list (wraps `SugarBits\ItemList`).

Forms navigate with `Tab` / `Shift+Tab`. `Enter` on the last field
submits; `Esc` / `Ctrl+C` aborts.

## Test

```sh
cd sugar-prompt && composer install && vendor/bin/phpunit
```

## Demos

### Confirm

![confirm](.vhs/confirm.gif)

### Multi-page form

![form](.vhs/form.gif)

### Input

![input](.vhs/input.gif)

### Multi-select

![multi-select](.vhs/multi-select.gif)

### Select

![select](.vhs/select.gif)

### Text (multi-line)

![text](.vhs/text.gif)

### Themes

![themes](.vhs/themes.gif)


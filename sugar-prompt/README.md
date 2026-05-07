<img src=".assets/icon.png" alt="sugar-prompt" width="160" align="right">

# SugarPrompt

<!-- BADGES:BEGIN -->
[![CI](https://github.com/detain/sugarcraft/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/detain/sugarcraft/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/detain/sugarcraft/branch/master/graph/badge.svg?flag=sugar-prompt)](https://app.codecov.io/gh/detain/sugarcraft?flags%5B0%5D=sugar-prompt)
[![Packagist Version](https://img.shields.io/packagist/v/sugarcraft/sugar-prompt?label=packagist)](https://packagist.org/packages/sugarcraft/sugar-prompt)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/php-%E2%89%A58.1-8892bf.svg)](https://www.php.net/)
<!-- BADGES:END -->


![demo](.vhs/form.gif)

PHP port of [charmbracelet/huh](https://github.com/charmbracelet/huh) —
interactive form library built on top of SugarCraft + SugarBits.

```php
use SugarCraft\Prompt\Form;
use SugarCraft\Prompt\Field\{Input, Confirm, Select, Note};

$form = Form::new(
    Note::new('welcome')->withTitle('Onboarding')
        ->withDescription('A few quick questions.'),
    Input::new('name')->withTitle('Your name?')->withPlaceholder('Ada Lovelace'),
    Confirm::new('newsletter')->withTitle('Subscribe to the newsletter?'),
    Select::new('lang')
        ->withTitle('Favorite language?')
        ->withOptions('PHP', 'Go', 'Rust', 'Python'),
);
// $form is a SugarCraft Model — drop it into a Program.
```

## Field types

| Field         | Description                                                 | Notable knobs |
| ------------- | ----------------------------------------------------------- | ------------- |
| `Input`       | Single-line text (wraps `SugarBits\TextInput`)              | `withPlaceholder`, `withCharLimit`, `withWidth`, `withPrompt`, `withValidator(\Closure)`, `withTitleFunc` / `withDescriptionFunc`, `withPassword(bool, string $echo = '*')`, `withSuggestions(list<string>)`, `withSuggestionsFunc(\Closure(string):list<string>)` |
| `Text`        | Multi-line text editor                                      | `withCharLimit`, `withMaxLines`, `withShowLineNumbers`, `withValidator` |
| `Confirm`     | Yes/no boolean                                              | `withAffirmative`/`withNegative`, `withValidator(\Closure(bool):?string)`, `withTitleFunc`, `withDescriptionFunc` |
| `Select`      | Single-choice list (wraps `SugarBits\ItemList`)             | `withOptions(...)`, `withTitleFunc`, `withDescriptionFunc` |
| `MultiSelect` | Multi-choice list                                           | `withOptions(...)`, `withLimit(int)` |
| `Note`        | Read-only paragraph; skipped by tab navigation              | `withTitle`, `withDescription`, `withHeight(int)`, `withNext(bool)`, `withNextLabel(string)` (turns it into an interactive button page) |
| `FilePicker`  | Filesystem picker (wraps `SugarBits\FileTree`)              | `withCwd`, `withAllowDirs`, `withAllowFiles`, `withShowSize`, `withShowHidden` |

All fields share a common navigation contract: `Tab` / `↓` advances,
`Shift+Tab` / `↑` retreats, `Enter` on the last interactive field
submits, `Esc` / `Ctrl+C` aborts. Skippable fields (e.g. plain
`Note`s) are passed over silently.

## Forms and groups

`Form::new(...$fields)` is a single-page form. For multi-page flows
build with `Form::groups(Group::new(...$fields), …)`. Each group
carries its own title / description / hide-predicate / theme override:

```php
Form::groups(
    Group::new(
        Input::new('name')->withTitle('Your name?'),
        Confirm::new('proceed')->withTitle('Continue?'),
    )->withTitle('Step 1'),
    Group::new(
        Note::new('done')->withTitle('Thanks!')->withNext()->withNextLabel('Finish'),
    )
        ->withTheme(Theme::dracula())
        ->withShowHelp(false)
        ->withHideFunc(fn (array $v) => $v['proceed'] !== true),
);
```

### Form-level chainables

| Method | What it does |
| ------ | ------------ |
| `withTheme(Theme)` | Switch the colour palette. |
| `withAccessible(bool)` | Render plain `label: value` text — for screen readers / dumb terminals. |
| `withShowHelp(bool)` | Toggle the help footer. |
| `withShowErrors(bool)` | Toggle the inline `! error` line on validation failures. |
| `withWidth(int)`, `withHeight(int)` | Pin the rendered geometry. |
| `withTimeout(int $ms)` | Auto-abort after `$ms` of wall clock. |

### Reading values after submit

`values()` returns every visible field keyed by `key()`. For typed
access call `getString`, `getInt`, `getBool`, `getArray`. For
inspecting validation state during a run use `errors()`,
`hasErrors()`, `getFocusedField()`, `keyBinds()`, `help()`.

### Themes & accessibility

Stock themes ship as static factories on `SugarCraft\Prompt\Theme`:
`ansi()` (default), `plain()`, `charm()`, `dracula()`, `catppuccin()`,
`base16()`. Pass one to `Form::withTheme(...)`. The accessibility
mode flips the entire form to plain-text rendering — useful when you
detect `NO_COLOR=1` or `TERM=dumb`.

### Validators and dynamic labels

Every value-producing field supports `withValidator(\Closure)`. The
closure runs on every keystroke (or every value flip for `Confirm`)
and returns `null` for valid or an error string. The error renders
inline beneath the field and shows up in `Form::errors()`.

Use `withTitleFunc(\Closure(): string)` / `withDescriptionFunc(...)`
on any field to compute labels lazily — handy when the label depends
on values from a previous group.

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


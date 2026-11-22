# Quick Start

A five-minute tour of the public API. Every snippet runs as-is against the
released package.

## 1. Create a language file

A language file is a plain PHP file that **returns an associative array**:

```php
<?php
// languages/en.php
return [
    'hello'   => 'Hello',
    'welcome' => 'Welcome {user}',
];
```

```php
<?php
// languages/tr.php
return [
    'hello'   => 'Merhaba',
    'welcome' => 'Hoşgeldin {user}',
];
```

## 2. Configure the translator

Three calls get you ready: where the packs live, which layout to use, and the
default language. They all return `$this`, so they chain.

```php
use InitPHP\Translator\Translator;

$lang = new Translator();
$lang->useFile()                       // optional — file mode is the default
    ->setDir(__DIR__ . '/languages/')  // base directory of the packs
    ->setDefault('en');                // load 'en' and make it active
```

> **Order matters.** Choose the layout (`useFile()` / `useDirectory()`) *before*
> the first language loads, and call `setDir()` before `setDefault()`/`change()`.
> See [Troubleshooting](Troubleshooting.md) if you hit a `TranslatorException`.

## 3. Read a translation

`translate()` returns a string:

```php
echo $lang->translate('hello');                            // "Hello"
echo $lang->translate('welcome', null, ['user' => 'Ada']); // "Welcome Ada"
```

`render()` is the same thing but echoes for you:

```php
$lang->render('welcome', null, ['user' => 'Ada']);        // prints "Welcome Ada"
```

## 4. Switch the active language

`change()` activates a language, loading it the first time it is used:

```php
$lang->change('tr');
echo $lang->translate('hello'); // "Merhaba"
```

## 5. Fallback when a key is missing

`translate()` resolves a key through a fixed chain and returns the **first**
match:

```php
$lang->setDefault('en')->change('tr');

// present in tr
$lang->translate('hello');                      // "Merhaba"

// missing in tr → falls back to the default language (en)
$lang->translate('only_in_english');            // English value, if defined there

// missing everywhere, but you passed an inline fallback
$lang->translate('greeting', 'Hi {user}', [
    'user' => 'Ada',
]);                                             // "Hi Ada"

// missing everywhere, no fallback → the key itself
$lang->translate('totally.unknown');            // "totally.unknown"
```

The full order is **active language → inline fallback → default language → the
key**. See [Keys & Fallback](Keys-and-Fallback.md).

## 6. Nested keys

Use dots to reach nested values. In [Directory Mode](Directory-Mode.md) the first
segment is the file name:

```php
// en.php => ['errors' => ['e404' => 'Not Found']]
echo $lang->translate('errors.e404'); // "Not Found"
```

## A complete example

```php
require 'vendor/autoload.php';

use InitPHP\Translator\Translator;

$lang = new Translator();
$lang->setDir(__DIR__ . '/languages/')
    ->setDefault('en');

// Per request, choose a language (with a safe default):
$lang->change($_GET['lang'] ?? 'en');

echo $lang->translate('welcome', 'Welcome {user}', [
    'user' => 'Ada',
]);
```

## Common mistakes

- **Setting the layout mode too late.** Calling `useDirectory()` after a language
  has loaded throws. Set the mode first — see [Troubleshooting](Troubleshooting.md).
- **Expecting a missing key to throw.** It does not; it returns the key (or your
  inline fallback). Missing *files/directories* throw — see [Exceptions](Exceptions.md).
- **Passing arrays as placeholder values.** They are ignored; only scalars and
  `Stringable` objects are interpolated — see [Placeholders](Placeholders.md).

## Where to go next

- [File Mode](File-Mode.md) — one `.php` file per language.
- [Directory Mode](Directory-Mode.md) — one directory of files per language.
- [Keys & Fallback](Keys-and-Fallback.md) — the exact resolution order.
- [Placeholders](Placeholders.md) — interpolating `{name}` markers.
- [API Reference](API-Reference.md) — every public member, listed.

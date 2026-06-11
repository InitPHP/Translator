# Getting started

## Install

```bash
composer require initphp/translator
```

Requires PHP 8.1 or newer. There are no runtime dependencies.

## The `Translator` object

A translator needs three things before you can read a translation:

1. A **base directory** that holds the language packs — `setDir()`.
2. A **layout mode** — `useFile()` (default) or `useDirectory()`.
3. A **default language** — `setDefault()`, which also loads it.

```php
use InitPHP\Translator\Translator;

$lang = new Translator();
$lang->useFile()                       // optional; file mode is the default
    ->setDir(__DIR__ . '/languages/')  // where the language packs live
    ->setDefault('en');                // load 'en' and make it active
```

Every configuration method returns the same instance, so the calls chain.

> **Order matters.** Pick the layout mode *before* a language is loaded (before
> `setDefault()`/`change()`), and call `setDir()` before either. Changing the
> mode after a language has loaded throws a
> [`TranslatorException`](exceptions.md).

## A language file

A language file is a plain PHP file that returns an associative array:

```php
<?php
// languages/en.php
return [
    'hello'   => 'Hello',
    'welcome' => 'Welcome {user}',
];
```

## Reading a translation

```php
echo $lang->translate('hello');                          // "Hello"
echo $lang->translate('welcome', null, ['user' => 'Ada']); // "Welcome Ada"
```

`translate()` returns a string. To echo it directly, use `render()`:

```php
$lang->render('welcome', null, ['user' => 'Ada']);       // prints "Welcome Ada"
```

## Switching languages

`change()` sets the active language, loading it the first time it is used:

```php
$lang->change('tr');
echo $lang->translate('hello'); // value from tr.php
```

If a key is missing in the active language, the translator falls back to the
default language (see [Key resolution & fallback](key-resolution-and-fallback.md)).

## A full example

```php
require 'vendor/autoload.php';

use InitPHP\Translator\Translator;

$lang = new Translator();
$lang->setDir(__DIR__ . '/languages/')
    ->setDefault('en');

// Later, per request:
$lang->change($_GET['lang'] ?? 'en');

echo $lang->translate('welcome', 'Welcome {user}', [
    'user' => 'Ada',
]);
```

Next: choose your layout — [File mode](usage/file-mode.md) or
[Directory mode](usage/directory-mode.md).

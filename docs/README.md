# InitPHP Translator — Documentation

InitPHP Translator turns plain PHP language files into a small, dependency-free
translation API. You point it at a directory, choose a layout, set a default
language, and read keys with `translate()` — with nested keys, `{name}`
placeholders and automatic fallback to the default language.

## Contents

| Guide | What it covers |
| ----- | -------------- |
| [Getting started](getting-started.md) | Install, configure, and read your first translation. |
| [File mode](usage/file-mode.md) | One `.php` file per language. |
| [Directory mode](usage/directory-mode.md) | One directory of files per language, addressed as `file.key`. |
| [Key resolution & fallback](key-resolution-and-fallback.md) | The exact order in which a key is resolved. |
| [Placeholders](placeholders.md) | Interpolating `{name}` markers from a context map. |
| [API reference](api-reference.md) | Every public method, its signature and behaviour. |
| [Exceptions](exceptions.md) | What is thrown, when, and how to handle it. |

## At a glance

```php
require 'vendor/autoload.php';

use InitPHP\Translator\Translator;

$lang = new Translator();
$lang->setDir(__DIR__ . '/languages/')
    ->setDefault('en');

$lang->change('tr');

echo $lang->translate('welcome', null, ['user' => 'Ada']);
```

```php
<?php
// languages/tr.php
return [
    'welcome' => 'Hoşgeldin {user}',
];
```

## Requirements

- PHP 8.1 or newer
- No runtime dependencies

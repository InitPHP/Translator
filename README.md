# InitPHP Translator

A micro multi-language (i18n) translation library for PHP. Load language packs
from plain PHP files, look up keys (including dot-delimited nested keys),
interpolate `{name}` placeholders, and fall back to a default language when a
translation is missing.

[![Latest Stable Version](http://poser.pugx.org/initphp/translator/v)](https://packagist.org/packages/initphp/translator)
[![Total Downloads](http://poser.pugx.org/initphp/translator/downloads)](https://packagist.org/packages/initphp/translator)
[![License](http://poser.pugx.org/initphp/translator/license)](https://packagist.org/packages/initphp/translator)
[![PHP Version Require](http://poser.pugx.org/initphp/translator/require/php)](https://packagist.org/packages/initphp/translator)

## Features

- Two on-disk layouts: **one file per language**, or **one directory per language**.
- **Nested keys** via dot notation (`admin.dashboard`, `errors.http.404`).
- **Placeholder interpolation** (`Welcome {user}`) from a context map.
- **Fallback chain**: active language → inline fallback → default language → the key itself.
- No runtime dependencies. Loaded languages are cached in memory.

## Requirements

- PHP 8.1 or higher

## Installation

```bash
composer require initphp/translator
```

## Quick start

```php
require_once 'vendor/autoload.php';

use InitPHP\Translator\Translator;

$lang = new Translator();
$lang->setDir(__DIR__ . '/languages/')
    ->setDefault('en');

$lang->change('tr'); // switch the active language

echo $lang->translate('hello');
```

A language file simply returns an array:

```php
<?php
// languages/en.php
return [
    'hello'   => 'Hello {user}',
    'today'   => "It's {day}",
];
```

```php
echo $lang->translate('hello', null, ['user' => 'Ada']); // "Hello Ada"
```

## File layout vs. directory layout

Choose how your language packs are stored. **Pick the mode before loading a
language** (i.e. before `setDefault()` / `change()`); the default is file mode.

### File mode — one file per language

```
languages/
    en.php
    tr.php
    fr.php
```

```php
$lang = new Translator();
$lang->useFile() // optional; this is the default
    ->setDir(__DIR__ . '/languages/')
    ->setDefault('en');

echo $lang->translate('hello');
```

### Directory mode — one directory per language

Each `*.php` file becomes a namespace keyed by its (lower-cased) file name, and
keys are addressed as `filename.key`:

```
languages/
    en/
        user.php
        admin.php
    tr/
        user.php
        admin.php
```

```php
$lang = new Translator();
$lang->useDirectory() // note: call before setDefault()/change()
    ->setDir(__DIR__ . '/languages/')
    ->setDefault('en');

echo $lang->translate('user.hello');   // user.php => ['hello' => '...']
echo $lang->translate('admin.dashboard');
```

## How a key is resolved

`translate()` resolves a key in this order and returns the first hit:

1. The **active** language (set by `setDefault()` or `change()`).
2. The **inline fallback** string, if you passed one (it is interpolated too).
3. The **default** language, when it differs from the active one.
4. The **key itself**, returned verbatim, if nothing matched.

```php
$lang->setDefault('en')->change('tr');

$lang->translate('errors.e404');                 // tr → (missing) → en → "Not Found"
$lang->translate('greeting', 'Hi {user}', [      // tr missing → inline fallback
    'user' => 'Ada',
]);                                              // "Hi Ada"
$lang->translate('unknown.key');                 // nowhere → "unknown.key"
```

> Note: a key that points to a nested array (a namespace) rather than a string
> is treated as a miss and falls through the chain — it never throws.

## Placeholders

Markers are written `{name}` and replaced from the context map. Scalar values
and objects implementing `__toString()` are stringified; arrays and other
objects are ignored, and unmatched markers are left untouched.

```php
$lang->translate('errorMsg', null, ['code' => 2005]);
// "Something went wrong. Code : 2005"
```

## Methods

| Method | Description |
| ------ | ----------- |
| `setDir(string $dir): self` | Sets the base directory of the language packs. Trailing slashes are trimmed. Throws if the path is not a directory. |
| `useFile(): self` | Selects file mode (default). Call before loading a language. |
| `useDirectory(): self` | Selects directory mode. Call before loading a language. |
| `setDefault(string $default): self` | Sets and loads the default (fallback) language; becomes the active one if none is set. |
| `change(string $current): self` | Switches the active language, loading it on first use. |
| `translate(string $key, ?string $fallback = null, array $context = []): string` | Returns the resolved, interpolated translation. |
| `render(string $key, ?string $fallback = null, array $context = []): void` | Echoes the value of `translate()`. |
| `_r(...)` | **Deprecated** alias of `translate()`, kept for backward compatibility. |
| `_e(...)` | **Deprecated** alias of `render()`, kept for backward compatibility. |

All configuration methods return `$this`, so calls can be chained.

## Documentation

Full guides with examples live in [`docs/`](docs/README.md):

- [Getting started](docs/getting-started.md)
- [File mode](docs/usage/file-mode.md) · [Directory mode](docs/usage/directory-mode.md)
- [Key resolution & fallback](docs/key-resolution-and-fallback.md)
- [Placeholders](docs/placeholders.md)
- [API reference](docs/api-reference.md)
- [Exceptions](docs/exceptions.md)

## Getting help

If you have questions, bug reports, or feature requests, please open an issue in
the [issue tracker](https://github.com/InitPHP/Translator/issues).

## Contributing

> All contributions to this project will be published under the MIT License. By
> submitting a pull request or filing a bug, issue, or feature request, you are
> agreeing to comply with this waiver of copyright interest.

Before opening a pull request, run the full local check bundle:

```bash
composer ci   # cs-check + stan + test
```

- Fork it ( https://github.com/InitPHP/Translator/fork )
- Create your feature branch (`git checkout -b feat/my-feature`)
- Commit your changes (`git commit -am 'Add some feature'`)
- Push to the branch (`git push origin feat/my-feature`)
- Create a new Pull Request

## Credits

- [Muhammet ŞAFAK](https://www.muhammetsafak.com.tr) &lt;<info@muhammetsafak.com.tr>&gt;

## License

Copyright &copy; 2022 InitPHP — released under the [MIT License](./LICENSE).

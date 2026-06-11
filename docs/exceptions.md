# Exceptions

The package raises a single exception type so you can catch everything it might
throw with one `catch`.

```php
namespace InitPHP\Translator;

class TranslatorException extends \Exception {}
```

## When it is thrown

| Situation | Method |
| --------- | ------ |
| `$dir` is not an existing directory | `setDir()` |
| A language is loaded before `setDir()` was called | `setDefault()` / `change()` |
| The layout mode is changed after a language is already loaded | `useFile()` / `useDirectory()` |
| A language **file** is missing (file mode) | `setDefault()` / `change()` |
| A language **directory** is missing (directory mode) | `setDefault()` / `change()` |
| A directory could not be read | `setDefault()` / `change()` |
| A language file does not `return` an array | `setDefault()` / `change()` |

Each message names the offending path or condition, for example:

```
The translation directory "/app/lang" does not exist.
The translation file was not found: "/app/languages/de.php".
The translation directory was not found: "/app/languages/de/".
The translation file "/app/languages/en.php" must return an array.
The file/directory mode cannot be changed after a language has been loaded. ...
```

## What does *not* throw

These are handled gracefully and never raise an exception:

- **A missing key** — `translate()` returns the inline fallback, the default
  language value, or the key itself. See
  [Key resolution & fallback](key-resolution-and-fallback.md).
- **A key that resolves to an array** (a namespace, not a string) — treated as a
  miss.
- **An empty language directory** (no `*.php` files) — loads as an empty
  language; lookups return the key.
- **Calling `translate()` before configuration** — returns the key (or inline
  fallback).

## Handling it

```php
use InitPHP\Translator\Translator;
use InitPHP\Translator\TranslatorException;

try {
    $lang = new Translator();
    $lang->useDirectory()
        ->setDir(__DIR__ . '/languages/')
        ->setDefault('en');
} catch (TranslatorException $e) {
    // Misconfiguration: wrong path, missing pack, invalid file, or wrong call order.
    error_log($e->getMessage());
}
```

Because configuration errors are deterministic (they depend on your filesystem
layout and call order, not on user input), the idiomatic approach is to let them
surface during development and fix the setup, rather than catching them on every
request.

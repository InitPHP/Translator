# Exceptions

The package raises a single exception type, so you can catch everything it might
throw with one `catch`.

```php
namespace InitPHP\Translator;

class TranslatorException extends \Exception {}
```

`TranslatorException` extends the SPL `\Exception`, so it also matches
`catch (\Exception $e)` and `catch (\Throwable $e)`.

## When it is thrown

| Situation | Raised by | Example message |
| --- | --- | --- |
| `$dir` is not an existing directory | [`setDir()`](API-Reference.md#setdir) | `The translation directory "/app/lang" does not exist.` |
| A language is loaded before `setDir()` was called | [`setDefault()`](API-Reference.md#setdefault) / [`change()`](API-Reference.md#change) | `The translation directory has not been set. Call setDir() before loading a language.` |
| The layout mode is changed after a language is already loaded | [`useFile()`](API-Reference.md#usefile) / [`useDirectory()`](API-Reference.md#usedirectory) | `The file/directory mode cannot be changed after a language has been loaded. ...` |
| A language **file** is missing (file mode) | `setDefault()` / `change()` | `The translation file was not found: "/app/languages/de.php".` |
| A language **directory** is missing (directory mode) | `setDefault()` / `change()` | `The translation directory was not found: "/app/languages/de/".` |
| A directory could not be read | `setDefault()` / `change()` | `The translation directory could not be read: "...".` |
| A language file does not `return` an array | `setDefault()` / `change()` | `The translation file "/app/languages/en.php" must return an array.` |

Every message names the offending path or condition.

## What does *not* throw

These are handled gracefully and never raise an exception:

- **A missing key** — [`translate()`](API-Reference.md#translate) returns the inline
  fallback, the default-language value, or the key itself. See
  [Keys & Fallback](Keys-and-Fallback.md).
- **A key that resolves to an array** (a namespace, not a string) — treated as a
  miss.
- **An empty language directory** (no `*.php` files) — loads as an empty
  language; lookups return the key.
- **Calling `translate()` before configuration** — returns the key (or the
  inline fallback).

This split is deliberate: **configuration mistakes fail loudly**, while
**missing content degrades gracefully** so a single absent string can never take
down a page.

## Handling it

Because configuration errors are deterministic — they depend on your filesystem
layout and call order, not on user input — the idiomatic approach is to let them
surface during development and fix the setup, rather than catching them on every
request.

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
    throw $e; // fail fast during boot
}
```

If you accept a language identifier from user input, validate it against a known
list **before** calling `change()`, so an unknown value falls back to a default
rather than throwing on a missing pack:

```php
$supported = ['en', 'tr', 'fr'];
$requested = $_GET['lang'] ?? 'en';

$lang->change(in_array($requested, $supported, true) ? $requested : 'en');
```

## Where to go next

- [Troubleshooting](Troubleshooting.md) — symptom-first fixes for common errors.
- [Keys & Fallback](Keys-and-Fallback.md) — why missing keys don't throw.
- [API Reference](API-Reference.md) — which method raises what.

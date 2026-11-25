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
| The language identifier is empty, contains `/`, `\` or a NUL byte (or `:` on Windows), or consists only of dots and spaces | `setDefault()` / `change()` | `The language code is invalid. It must not be empty, contain "/", "\" or a NUL byte (or ":" on Windows), or consist only of dots and spaces.` |
| A language file does not `return` an array | `setDefault()` / `change()` | `The translation file "/app/languages/en.php" must return an array.` |

Every message names the offending path or condition.

When `setDefault()` or `change()` throws — an invalid code, a missing or unreadable
pack, a file that does not return an array — the translator is left exactly as it
was: the active and default languages are unchanged, and nothing is cached for
the language that failed. In directory mode a language is cached only after every
file in it has loaded, so retrying a broken language throws again instead of
silently using the part that loaded.

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

## Validate user-supplied language codes

The identifier given to `setDefault()` and `change()` is appended to the directory
you gave `setDir()` (`<dir>/<identifier>.php` in file mode,
`<dir>/<identifier>/*.php` in directory mode) and the result is `require`d. It
must therefore be a single, ordinary path component, and these are rejected with
a `TranslatorException` **before** any file is touched:

- an empty identifier,
- a `/` or a `\` (both separate path components on Windows), so `../config/install`
  and `en/../../x` cannot leave the language directory,
- a NUL byte,
- a `:` on Windows, where it is not valid in a file name and could only address a
  drive-relative path or an alternate data stream,
- an identifier made only of dots and spaces: `.` and `..`, and the forms Windows
  normalises to them (`...`, `.. `, `. .`).

Everything else is an ordinary file name and works as before: `en`, `en_US`,
`pt-BR`, `sr@latin`, `zh.Hant`, `türkçe`, even a name with a space. Outside
Windows a `:` is an ordinary character, too; a drive letter or a stream-wrapper
prefix such as `phar:` or `php:` only counts at the very start of a path, and that
start is your `setDir()` path. Because a code cannot contain a separator it is one
path component and cannot leave the language directory. In
directory mode the `*`, `?` and `[` characters are matched literally, not as
`glob()` wildcards.

The check only keeps the lookup inside the language directory. It does not know
which languages you support: a well-formed code such as `xx` still throws
`TranslatorException` (file or directory not found), and any file that really
exists in the language directory can be loaded. If you accept a language
identifier from user input (query string, cookie, header, form field), validate
it against a known list **before** calling `change()` or `setDefault()`, so an
unknown value falls back to a default instead of raising an exception:

```php
$supported = ['en', 'tr', 'fr'];
$requested = $_GET['lang'] ?? 'en';

$lang->change(in_array($requested, $supported, true) ? $requested : 'en');
```

## Where to go next

- [Troubleshooting](Troubleshooting.md) — symptom-first fixes for common errors.
- [Keys & Fallback](Keys-and-Fallback.md) — why missing keys don't throw.
- [API Reference](API-Reference.md) — which method raises what.

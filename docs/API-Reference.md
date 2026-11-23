# API Reference

Every public member of `InitPHP\Translator\Translator` and
`InitPHP\Translator\TranslatorInterface`.

All configuration methods return the same instance, so calls chain
(`$lang->setDir(...)->setDefault(...)->change(...)`).

## `Translator`

```php
final class Translator implements TranslatorInterface
```

`Translator` is **`final`**. Depend on [`TranslatorInterface`](#translatorinterface)
and compose rather than subclass.

Internal state in brief:

| State | Meaning |
| --- | --- |
| base directory | set by [`setDir()`](#setdir); language packs live here |
| layout mode | file (default) or directory — [`useFile()`](#usefile) / [`useDirectory()`](#usedirectory) |
| default language | the fallback language — [`setDefault()`](#setdefault) |
| active language | the language read first — [`setDefault()`](#setdefault) / [`change()`](#change) |
| container | in-memory cache of loaded languages |

---

### `setDir()`

```php
public function setDir(string $dir): self;
```

Sets the base directory that holds the language packs. A trailing `/` or `\` is
trimmed. Call this **before** loading a language.

- **`$dir`** — path to the directory containing the language files/directories.
- **Throws** [`TranslatorException`](Exceptions.md) if `$dir` is not an existing
  directory.

```php
$lang->setDir(__DIR__ . '/languages/');
```

---

### `useFile()`

```php
public function useFile(): self;
```

Selects the one-file-per-language layout, where each language is a single
`<language>.php` file. This is the **default**, so the call is optional. Must run
**before** any language is loaded.

- **Throws** [`TranslatorException`](Exceptions.md) if the mode is changed after a
  language has already loaded.

See [File Mode](File-Mode.md).

---

### `useDirectory()`

```php
public function useDirectory(): self;
```

Selects the one-directory-per-language layout, where each `*.php` file becomes a
namespace keyed by its **lower-cased** file name. Must run **before** any
language is loaded.

- **Throws** [`TranslatorException`](Exceptions.md) if the mode is changed after a
  language has already loaded.

```php
$lang->useDirectory()->setDir(__DIR__ . '/languages/')->setDefault('en');
```

See [Directory Mode](Directory-Mode.md).

---

### `setDefault()`

```php
public function setDefault(string $default): self;
```

Sets and **eagerly loads** the default (fallback) language. If no active language
has been chosen yet, this language also becomes the active one.

- **`$default`** — language identifier (a file name without extension in file
  mode, or a sub-directory name in directory mode). It is joined into a path and
  the file is `require`d without any validation, so pass only values you
  control — see [Validate user-supplied language codes](Exceptions.md#validate-user-supplied-language-codes).
- **Throws** [`TranslatorException`](Exceptions.md) if the directory is unset, or
  the language pack is missing, unreadable, or does not return an array.

```php
$lang->setDefault('en');
```

---

### `change()`

```php
public function change(string $current): self;
```

Switches the active language, loading it on first use. If no default language has
been set yet, the given language also becomes the default.

- **`$current`** — language identifier to activate. Like `setDefault()`, it is
  joined into a path and the file is `require`d without any validation. Never
  pass request input directly; check it against an allow-list first — see
  [Validate user-supplied language codes](Exceptions.md#validate-user-supplied-language-codes).
- **Throws** [`TranslatorException`](Exceptions.md) under the same conditions as
  [`setDefault()`](#setdefault).

```php
$lang->change('tr');
```

---

### `translate()`

```php
public function translate(string $key, ?string $fallback = null, array $context = []): string;
```

Returns the resolved, interpolated translation for `$key`.

- **`$key`** — the translation key; use dots for nested values
  (`admin.dashboard`, `errors.http.404`).
- **`$fallback`** — text returned when the key is missing from the active
  language. `''` and `'0'` count as provided; only `null` means "no fallback".
- **`$context`** — `array<string, mixed>` of placeholder values. Scalars and
  `__toString()`-able objects are interpolated; arrays and other objects are
  ignored. See [Placeholders](Placeholders.md).
- **Returns** the translation, the interpolated fallback, or `$key` itself.

Resolution order: **active language → inline fallback → default language → the
key**. See [Keys & Fallback](Keys-and-Fallback.md).

```php
$lang->translate('welcome', 'Hi {user}', ['user' => 'Ada']);
```

---

### `render()`

```php
public function render(string $key, ?string $fallback = null, array $context = []): void;
```

Echoes the value that [`translate()`](#translate) would return. Same parameters.

```php
$lang->render('welcome', null, ['user' => 'Ada']); // prints "Welcome Ada"
```

---

### `_r()`

```php
public function _r(string $key, ?string $fallback = null, array $context = []): string;
```

> **Deprecated since 1.0.** Backward-compatible alias of
> [`translate()`](#translate). Prefer `translate()` in new code. Still fully
> functional.

---

### `_e()`

```php
public function _e(string $key, ?string $fallback = null, array $context = []): void;
```

> **Deprecated since 1.0.** Backward-compatible alias of [`render()`](#render).
> Prefer `render()` in new code. Still fully functional.

---

### `__debugInfo()`

```php
public function __debugInfo(): array;
```

Returns a debug snapshot used by `var_dump()`:

```php
[
    'system'    => 'file',  // or 'directory'
    'default'   => 'en',    // or null
    'current'   => 'tr',    // or null
    'container' => [...],    // the active language's loaded data (present only when a language is active)
]
```

---

## `TranslatorInterface`

```php
interface TranslatorInterface
```

The contract implemented by [`Translator`](#translator). Type-hint this in your
services so you can swap or decorate the implementation.

It declares every method above except `__debugInfo()`. The configuration methods
are declared to return `TranslatorInterface`; the concrete `Translator` narrows
that to `self`.

```php
use InitPHP\Translator\TranslatorInterface;

final class GreetingService
{
    public function __construct(private TranslatorInterface $lang)
    {
    }

    public function greet(string $user): string
    {
        return $this->lang->translate('welcome', 'Welcome {user}', ['user' => $user]);
    }
}
```

## Method summary

| Method | Returns | Loads from disk? |
| --- | --- | --- |
| [`setDir`](#setdir) | `self` | no |
| [`useFile`](#usefile) / [`useDirectory`](#usedirectory) | `self` | no |
| [`setDefault`](#setdefault) | `self` | yes (the default language) |
| [`change`](#change) | `self` | yes (on first use of a language) |
| [`translate`](#translate) | `string` | no (uses the in-memory cache) |
| [`render`](#render) | `void` (echoes) | no |
| [`_r`](#_r) / [`_e`](#_e) | as above | no |
| [`__debugInfo`](#__debuginfo) | `array` | no |

## Where to go next

- [Keys & Fallback](Keys-and-Fallback.md) — the resolution semantics of `translate()`.
- [Placeholders](Placeholders.md) — how `$context` is applied.
- [Exceptions](Exceptions.md) — every condition that throws.

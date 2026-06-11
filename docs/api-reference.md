# API reference

Every public member of `InitPHP\Translator\Translator` and
`InitPHP\Translator\TranslatorInterface`.

`Translator` is `final`. Depend on `TranslatorInterface` and compose rather than
subclass. All configuration methods return the same instance, so calls chain.

## `setDir`

```php
public function setDir(string $dir): self;
```

Sets the base directory that holds the language packs. A trailing `/` or `\` is
trimmed. Call this before loading a language.

- **Throws** `TranslatorException` if `$dir` is not an existing directory.

```php
$lang->setDir(__DIR__ . '/languages/');
```

## `useFile`

```php
public function useFile(): self;
```

Selects the one-file-per-language layout. This is the default, so the call is
optional. Must run before any language is loaded.

- **Throws** `TranslatorException` if the mode is changed after a language has
  already been loaded.

## `useDirectory`

```php
public function useDirectory(): self;
```

Selects the one-directory-per-language layout, where each `*.php` file becomes a
namespace keyed by its lower-cased file name. Must run before any language is
loaded.

- **Throws** `TranslatorException` if the mode is changed after a language has
  already been loaded.

```php
$lang->useDirectory()->setDir(__DIR__ . '/languages/')->setDefault('en');
```

## `setDefault`

```php
public function setDefault(string $default): self;
```

Sets and **eagerly loads** the default (fallback) language. If no active
language has been chosen yet, this language also becomes the active one.

- **Throws** `TranslatorException` if the directory is unset, or the language
  pack is missing, unreadable, or does not return an array.

```php
$lang->setDefault('en');
```

## `change`

```php
public function change(string $current): self;
```

Switches the active language, loading it on first use. If no default language
has been set yet, the given language also becomes the default.

- **Throws** `TranslatorException` under the same conditions as `setDefault()`.

```php
$lang->change('tr');
```

## `translate`

```php
public function translate(string $key, ?string $fallback = null, array $context = []): string;
```

Returns the resolved, interpolated translation for `$key`. See
[Key resolution & fallback](key-resolution-and-fallback.md) for the exact order
and [Placeholders](placeholders.md) for `$context` handling.

- **`$key`** — the translation key; use dots for nested values (`admin.dashboard`).
- **`$fallback`** — text returned when the key is missing from the active
  language. `''` and `'0'` count as provided; only `null` means "no fallback".
- **`$context`** — `array<string, mixed>` of placeholder values. Scalars and
  `__toString()`-able objects are interpolated; arrays and other objects are ignored.
- **Returns** the translation, the interpolated fallback, or `$key` itself.

```php
$lang->translate('welcome', 'Hi {user}', ['user' => 'Ada']);
```

## `render`

```php
public function render(string $key, ?string $fallback = null, array $context = []): void;
```

Echoes the value that `translate()` would return. Same parameters.

```php
$lang->render('welcome', null, ['user' => 'Ada']); // prints "Welcome Ada"
```

## `_r` (deprecated)

```php
public function _r(string $key, ?string $fallback = null, array $context = []): string;
```

Backward-compatible alias of [`translate()`](#translate). Prefer `translate()`.

## `_e` (deprecated)

```php
public function _e(string $key, ?string $fallback = null, array $context = []): void;
```

Backward-compatible alias of [`render()`](#render). Prefer `render()`.

## `__debugInfo`

```php
public function __debugInfo(): array;
```

Returns a debug snapshot used by `var_dump()`:

```php
[
    'system'    => 'file',   // or 'directory'
    'default'   => 'en',     // or null
    'current'   => 'tr',     // or null
    'container' => [...],     // the active language's loaded data (only when a language is active)
]
```

## Method summary

| Method | Returns | Loads from disk? |
| --- | --- | --- |
| `setDir` | `self` | no |
| `useFile` / `useDirectory` | `self` | no |
| `setDefault` | `self` | yes (the default language) |
| `change` | `self` | yes (on first use of a language) |
| `translate` | `string` | no (uses the in-memory cache) |
| `render` | `void` (echoes) | no |
| `_r` / `_e` | as above | no |

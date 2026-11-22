# Keys & Fallback

This page describes exactly how `translate()` (and its echoing twin `render()`)
turn a key into a string.

## The resolution order

```php
public function translate(string $key, ?string $fallback = null, array $context = []): string
```

A key is resolved through a fixed chain. The **first** match wins:

1. **Active language** — the language set by the most recent `setDefault()` or
   `change()`.
2. **Inline fallback** — the `$fallback` string, if you passed one. It is
   interpolated too, and it **short-circuits** the default-language lookup.
3. **Default language** — the language given to `setDefault()`, consulted only
   when it differs from the active language *and* no inline fallback was given.
4. **The key itself** — returned verbatim when nothing matched.

```php
$lang->setDefault('en')->change('tr');

// 1: present in the active language (tr)
$lang->translate('errors.e500');             // "Sunucu Hatası"

// 3: missing in tr, no inline fallback → default language (en)
$lang->translate('errors.e404');             // "Not Found"

// 2: inline fallback wins over the default language
$lang->translate('errors.e404', 'Oops');     // "Oops"

// 4: missing everywhere
$lang->translate('nope');                     // "nope"
```

## Nested keys

A key containing a dot (`.`) is split into segments. The first segment selects
the top-level entry — in [Directory Mode](Directory-Mode.md) that is the **file
name** — and each further segment walks one level deeper into nested arrays.

```php
// en.php => ['errors' => ['http' => ['unauthorized' => 'Unauthorized']]]
$lang->translate('errors.http.unauthorized'); // "Unauthorized"
```

The fallback chain applies to nested keys exactly as it does to flat keys: a
nested key missing from the active language is looked up in the default
language.

> **Why this matters.** In earlier (0.x) releases, nested keys were always
> resolved against the *active* language, so a nested key present only in the
> default language was never found. That bug is fixed in 1.0 — see
> [Migration (0.x → 1.0)](Migration-from-0.x.md).

## A key that points to an array

If a key resolves to an **array** (a namespace) instead of a string leaf, it is
treated as a miss — the chain continues, and you ultimately get the key back. It
never throws:

```php
// 'errors' and 'errors.http' are arrays, not translation strings
$lang->translate('errors');       // "errors"
$lang->translate('errors.http');  // "errors.http"
```

The same is true for any non-string leaf (for example an integer value): only
**string** leaves are treated as translations.

## The inline fallback in detail

- It is interpolated with the same `$context` as a real translation:

  ```php
  $lang->translate('greeting', 'Hi {user}', ['user' => 'Ada']); // "Hi Ada"
  ```

- An empty string (`''`) and the string `'0'` count as **provided** fallbacks
  and are returned as-is. Only `null` means "no inline fallback":

  ```php
  $lang->translate('missing', '');   // ""        (the empty fallback)
  $lang->translate('missing', '0');  // "0"
  $lang->translate('missing', null); // "missing" (the key)
  ```

- A provided fallback **stops** the chain before the default language is
  consulted. If you want default-language fallback, pass `null`:

  ```php
  $lang->translate('errors.e404', 'Oops'); // "Oops"      (fallback wins)
  $lang->translate('errors.e404', null);   // "Not Found" (default language)
  ```

## Default and active language interplay

- `setDefault()` loads the default language and, if no active language is set
  yet, makes it active too.
- `change()` activates a language and, if no default is set yet, makes it the
  default too.
- When the active and default languages are the **same**, step 3 is skipped
  (there is nothing to fall back to), so a missing key returns itself directly.

```php
$lang = new Translator();
$lang->setDir(__DIR__ . '/languages/')->setDefault('en'); // default = active = en
$lang->translate('does.not.exist'); // "does.not.exist"
```

## Before any language is configured

If you call `translate()` before a language has been loaded, it degrades
gracefully and returns the key (or the inline fallback, if given) — it does not
throw:

```php
$lang = new Translator();
$lang->translate('anything');         // "anything"
$lang->translate('anything', 'Hi');   // "Hi"
```

## Where to go next

- [Placeholders](Placeholders.md) — how `$context` values are interpolated.
- [File Mode](File-Mode.md) / [Directory Mode](Directory-Mode.md) — where keys come from.
- [Exceptions](Exceptions.md) — what *does* throw (missing files, bad config).

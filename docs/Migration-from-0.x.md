# Migration (0.x → 1.0)

Version 1.0 is the first stable release. It is **backward compatible** with the
0.2 public API — your existing calls keep working — while fixing several bugs and
modernizing the package. This page lists what changed and what (little) you may
want to update.

> This page describes the 0.x to 1.0 changes, where the PHP floor became 8.1.
> The development line after 1.0.0 raises it again to PHP 8.2 (see the
> [CHANGELOG](https://github.com/InitPHP/Translator/blob/main/CHANGELOG.md)),
> so check [Installation](Installation.md) for the current requirement.

## TL;DR

- **Nothing is required** beyond running PHP 8.1+. `_r()` / `_e()` and the fluent
  setters still work.
- Prefer the new, descriptive [`translate()`](API-Reference.md#translate) /
  [`render()`](API-Reference.md#render) methods in new code.
- A few long-standing **bugs are fixed** — if you worked around them, you can
  remove the workarounds.

## Requirement change

- **PHP 8.1 or newer is required** (was 7.4+). Update your environment/CI before
  upgrading.

## API: new primary methods, old ones deprecated

The cryptic `_r()` / `_e()` names are now **deprecated aliases**. They behave
identically and are not going away in the 1.x line, but new code should use the
readable names:

| 0.x (still works) | 1.0 (preferred) |
| --- | --- |
| `$lang->_r('key')` | `$lang->translate('key')` |
| `$lang->_e('key')` | `$lang->render('key')` |

```php
// before
echo $lang->_r('welcome', null, ['user' => 'Ada']);

// after
echo $lang->translate('welcome', null, ['user' => 'Ada']);
```

The fluent configuration methods — `setDir()`, `useFile()`, `useDirectory()`,
`setDefault()`, `change()` — are unchanged.

## Bug fixes that may change behavior

These make the library behave the way the documentation always implied. If your
code accidentally depended on the broken behavior, review these:

### Nested-key fallback now works

Previously, a dot-delimited key (e.g. `errors.e404`) was always resolved against
the **active** language, so a nested key present only in the default language was
never found — you got the key back. In 1.0 it correctly falls back to the
default language, in both file and directory modes.

```php
$lang->setDefault('en')->change('tr');
// en has errors.e404, tr does not
$lang->translate('errors.e404'); // 0.x: "errors.e404"   →   1.0: "Not Found"
```

### Requesting a namespace no longer throws

Asking for a key that points to a nested array (a namespace) used to raise a
`TypeError`. It is now treated as a graceful miss:

```php
$lang->translate('errors'); // 0.x: TypeError   →   1.0: "errors"
```

### `'0'` and `''` inline fallbacks are honored

The provided-fallback check used `empty()`, which discarded `'0'` and `''`. It
now checks for `null`, so those values are returned as given:

```php
$lang->translate('missing', '0'); // 0.x: "missing"   →   1.0: "0"
```

## Behavior made stricter

### Changing the mode after loading now throws

Calling `useFile()` / `useDirectory()` *after* a language had loaded used to
silently leave the translator in an inconsistent state. It now throws a
[`TranslatorException`](Exceptions.md) with a clear message. Set the mode **before**
`setDefault()` / `change()`:

```php
// ✅ correct order
$lang->useDirectory()->setDir($dir)->setDefault('en');
```

### Invalid language files fail loudly

A language file that does not `return` an array now throws a clear
`TranslatorException` instead of a generic type error.

## Structural changes

- **`Translator` is now `final`** and implements the documented
  [`TranslatorInterface`](API-Reference.md#translatorinterface). If you previously
  **subclassed** `Translator`, switch to composition: depend on
  `TranslatorInterface` and wrap an instance instead. The calling API is
  unchanged.

## Upgrade checklist

1. Ensure your runtime/CI is on **PHP 8.1+**.
2. `composer require initphp/translator:^1.0`.
3. Run your test suite. The bug fixes above are the only behavioral changes.
4. (Optional) Replace `_r()` → `translate()` and `_e()` → `render()` for clarity.
5. (Optional) If you subclassed `Translator`, refactor to compose around
   `TranslatorInterface`.

## Where to go next

- [Quick Start](Quick-Start.md) — the 1.0 API in five minutes.
- [Keys & Fallback](Keys-and-Fallback.md) — the now-correct resolution rules.
- [API Reference](API-Reference.md) — every method, old and new.

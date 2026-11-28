# FAQ

## Does it support plural rules, gender, or ICU MessageFormat?

No — that is intentionally out of scope. `initphp/translator` is a *micro*
library: key lookup, nested keys, `{name}` interpolation, and default-language
fallback. If you need CARDINAL/ORDINAL plurals or ICU patterns, format the value
yourself and pass the result through `$context`:

```php
$count = 3;
$word  = $count === 1 ? 'item' : 'items';
$lang->translate('cart.summary', '{n} {word} in your cart', [
    'n'    => $count,
    'word' => $word,
]);
```

## Why does a missing key return the key instead of throwing?

So a single absent translation can never take down a page. Missing **content**
degrades gracefully (you get the key, your inline fallback, or the
default-language value); missing **configuration** (bad path, missing file)
fails loudly. See [Exceptions](Exceptions.md) and [Keys & Fallback](Keys-and-Fallback.md).

## What's the difference between `translate()` and `_r()`?

None functionally — `_r()` is a **deprecated alias** of `translate()`, and `_e()`
is a deprecated alias of `render()`. They are kept so 0.x code keeps working.
Prefer `translate()` / `render()` in new code. See
[Migration (0.x → 1.0)](Migration-from-0.x.md).

## Do I have to call `useFile()`?

No. File mode is the default, so `useFile()` is optional. You only need
`useDirectory()` to opt into the multi-file layout — and it must be called
**before** the first language loads.

## Can I change the layout mode after loading a language?

No. Once a language is loaded, calling `useFile()`/`useDirectory()` with a
*different* mode throws a [`TranslatorException`](Exceptions.md). Re-applying the
**same** mode is a harmless no-op. Set the mode first, then `setDir()`, then
`setDefault()`/`change()`.

## Are language files re-read on every lookup?

No. Each language is read from disk **once** and cached in memory for the
lifetime of the `Translator` instance. Switching back to an already-loaded
language does not touch the disk.

## Is there an opcode/file cache?

The library relies on `require`, so your PHP **OPcache** caches the compiled
language files automatically in production. There is no separate translation
cache to configure.

## How do I store integers or other non-string values?

You can, but only **string** leaves are returned as translations — a non-string
leaf (array, int, etc.) is treated as a miss. Keep translation values as
strings; pass dynamic numbers through `$context` instead.

## Can two languages share keys?

Yes — that is exactly what the default-language fallback provides. Put shared or
canonical strings in your default language; any key a translation omits falls
back to it. See [Keys & Fallback](Keys-and-Fallback.md).

## Is `Translator` immutable / thread-safe?

`Translator` is mutable (it caches loaded languages and tracks the active
language) and is designed for the typical single-request PHP model. Use one
instance per request; do not share a single instance across concurrent requests
in a long-running worker without your own synchronization.

## Which PHP versions are supported?

PHP **8.2+**, tested in CI on 8.2, 8.3, 8.4, 8.5 and 8.6. See [Installation](Installation.md).

## Where do I report a bug or ask a question?

- Bugs / feature requests: [Issues](https://github.com/InitPHP/Translator/issues)
- Questions / ideas: [Discussions](https://github.com/orgs/InitPHP/discussions)

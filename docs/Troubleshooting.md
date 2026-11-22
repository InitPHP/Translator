# Troubleshooting

Symptom-first fixes for the most common problems. For the full list of error
conditions, see [Exceptions](Exceptions.md).

## `translate()` returns the key unchanged

You asked for `welcome` and got back `"welcome"`. The key was not found and no
fallback applied. Check, in order:

1. **Is the key actually in the file?** Open the language file and confirm the
   exact spelling and nesting.
2. **Right mode for the key shape?** In [Directory Mode](Directory-Mode.md) keys are
   `file.key` (e.g. `admin.dashboard`). In [File Mode](File-Mode.md) the first
   segment is a top-level array key, not a file name. Mixing them up is the most
   common cause.
3. **Is the language loaded?** A key only resolves in a language you have set as
   default or switched to with `change()`.
4. **Does the leaf hold a string?** A key that points to a nested array (a
   namespace) is treated as a miss. See
   [Keys & Fallback](Keys-and-Fallback.md#a-key-that-points-to-an-array).
5. **Expecting default-language fallback?** It only happens when you pass
   `null` as the fallback *and* the default differs from the active language.

## `TranslatorException: ... mode cannot be changed ...`

```
The file/directory mode cannot be changed after a language has been loaded.
Call useFile()/useDirectory() before setDefault()/change().
```

You called `useDirectory()` (or `useFile()`) **after** a language had already
loaded. Move the mode selection to the front:

```php
// ❌ wrong order
$lang->setDir($dir)->setDefault('en')->useDirectory();

// ✅ mode first
$lang->useDirectory()->setDir($dir)->setDefault('en');
```

## `TranslatorException: ... directory ... does not exist`

`setDir()` received a path that is not a directory. Verify the path resolves —
prefer absolute paths built from `__DIR__`:

```php
$lang->setDir(__DIR__ . '/languages/'); // not a relative 'languages/'
```

## `TranslatorException: ... file was not found` / `directory was not found`

The language pack for the identifier you loaded is missing:

- **File mode** expects `<dir>/<lang>.php` (e.g. `languages/de.php`).
- **Directory mode** expects `<dir>/<lang>/` (e.g. `languages/de/`).

Confirm the file/directory exists and the identifier matches its name. If the
identifier comes from user input, validate it against a known list first — see
[Recipes](Recipes.md#pick-the-language-per-request).

## `TranslatorException: ... must return an array`

A language file ran but did not `return` an array. Make sure the file ends with
a `return`:

```php
<?php
// ✅
return [
    'hello' => 'Hello',
];
```

```php
<?php
// ❌ assigns but never returns
$messages = ['hello' => 'Hello'];
```

## A placeholder isn't being replaced

`{user}` shows up literally in the output. Check:

1. **Key match.** The context key must equal the marker name exactly:
   `['user' => 'Ada']` for `{user}`. Names are case-sensitive.
2. **No spaces in the braces.** `{ user }` is a different marker than `{user}`.
3. **Supported value type.** Arrays and non-`Stringable` objects are ignored.
   See [Placeholders](Placeholders.md#which-values-are-interpolated).

Unmatched markers are intentionally left intact so you can spot missing context.

## Turkish/Unicode text looks garbled

The library does no encoding conversion — it returns the bytes from your files.
Make sure:

- Language files are saved as **UTF-8** (no BOM).
- Your response sends `Content-Type: text/html; charset=utf-8`.

## Directory-mode key uses the wrong file name

The namespace is the file name **lower-cased**. `Admin.php` is still addressed as
`admin.*`, but keep file names lower-case to avoid confusion across
case-sensitive (Linux) and case-insensitive (macOS/Windows) filesystems.

## Still stuck?

Open an issue with your PHP version (`php -v`), the directory layout, and a
minimal snippet: [Translator issues](https://github.com/InitPHP/Translator/issues/new).

## Where to go next

- [Exceptions](Exceptions.md) — every throw condition and its message.
- [Keys & Fallback](Keys-and-Fallback.md) — why a key resolves the way it does.
- [FAQ](FAQ.md) — scope and behavior questions.

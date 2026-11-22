# File Mode

In **file mode**, each language is a single PHP file named `<language>.php`
inside the base directory. This is the **default** layout, so `useFile()` is
optional.

```
languages/
    en.php
    tr.php
    fr.php
```

## Selecting file mode

File mode is active by default. Call `useFile()` explicitly when you want the
intent to be obvious — but always **before** the first language loads:

```php
use InitPHP\Translator\Translator;

$lang = new Translator();
$lang->useFile()                       // optional; this is the default
    ->setDir(__DIR__ . '/languages/')
    ->setDefault('en');
```

## The shape of a language file

Each file `return`s an associative array. Leaf values are strings; nested
arrays create [dot-delimited keys](Keys-and-Fallback.md#nested-keys):

```php
<?php
// languages/en.php
return [
    'hello'    => 'Hello',
    'welcome'  => 'Welcome {user}',
    'errors'   => [
        'e404' => 'Not Found',
        'e500' => 'Server Error',
        'http' => [
            'unauthorized' => 'Unauthorized',
        ],
    ],
];
```

A file that does **not** return an array throws a
[`TranslatorException`](Exceptions.md):

```php
<?php
// languages/broken.php — WRONG
$messages = ['hello' => 'Hi'];   // never returned → throws on load
```

## Reading keys

```php
echo $lang->translate('hello');                    // "Hello"
echo $lang->translate('errors.e404');              // "Not Found"  (nested)
echo $lang->translate('errors.http.unauthorized'); // "Unauthorized" (deeply nested)

echo $lang->translate('welcome', null, [           // with a placeholder
    'user' => 'Ada',
]);                                                // "Welcome Ada"
```

## Switching languages

```php
$lang->change('tr');
echo $lang->translate('hello'); // value from tr.php
```

A language file is read from disk **once**; later lookups use the in-memory
cache. Switching back to a language you already loaded does not re-read it.

## When a key points to a nested array

If a key resolves to an array (a "namespace") rather than a string leaf, it is
treated as a **miss** and falls through the resolution chain — it never throws:

```php
// 'errors' is an array, not a translation string
echo $lang->translate('errors');      // "errors"
echo $lang->translate('errors.http'); // "errors.http"
```

## When the file is missing or invalid

- A missing `<language>.php` throws a [`TranslatorException`](Exceptions.md).
- A file that does not `return` an array throws a `TranslatorException`.

Note that a missing **key** never throws — only a missing or invalid **file**
does. See [Exceptions](Exceptions.md) for the full list.

## File mode vs. directory mode

Use file mode when each language fits comfortably in one file. Reach for
[Directory Mode](Directory-Mode.md) when you want to split a language across
several files (e.g. `admin.php`, `user.php`, `emails.php`) addressed as
`admin.*`, `user.*`, `emails.*`.

## Where to go next

- [Keys & Fallback](Keys-and-Fallback.md) — how lookups resolve and fall back.
- [Placeholders](Placeholders.md) — `{name}` interpolation in detail.
- [Directory Mode](Directory-Mode.md) — the multi-file layout.

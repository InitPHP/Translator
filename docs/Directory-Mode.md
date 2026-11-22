# Directory Mode

In **directory mode**, each language is a **directory** named `<language>/`, and
every `*.php` file inside it becomes a namespace. Keys are addressed as
`filename.key`.

```
languages/
    en/
        main.php
        admin.php
    tr/
        main.php
        admin.php
```

This layout keeps large translation sets organized — split a language across
`admin.php`, `user.php`, `emails.php`, and so on.

## Selecting directory mode

Directory mode is **not** the default, so you must call `useDirectory()` —
**before** the first language loads:

```php
use InitPHP\Translator\Translator;

$lang = new Translator();
$lang->useDirectory()
    ->setDir(__DIR__ . '/languages/')
    ->setDefault('en');
```

> Calling `useDirectory()` *after* a language has already loaded throws a
> [`TranslatorException`](Exceptions.md). Set the mode first — see
> [Troubleshooting](Troubleshooting.md).

## Namespaces come from file names

The namespace is the file name without `.php`, **lower-cased**. So `admin.php`
is addressed as `admin.*`. The first segment of a key selects the file; the
remaining segments walk the nested array inside it.

```php
<?php
// languages/en/admin.php
return [
    'dashboard' => 'Dashboard',
    'menu'      => [
        'settings' => [
            'title' => 'Settings',
        ],
    ],
];
```

```php
echo $lang->translate('admin.dashboard');           // "Dashboard"
echo $lang->translate('admin.menu.settings.title');  // "Settings" (deeply nested)
```

```php
<?php
// languages/en/main.php
return [
    'hello_world' => 'Hello World',
];
```

```php
echo $lang->translate('main.hello_world'); // "Hello World"
```

## Switching languages

```php
$lang->change('tr');
echo $lang->translate('main.hello_world'); // value from tr/main.php
```

Each file in a language directory is loaded once and cached. Fallback to the
default language works for nested keys too — if `tr/admin.php` lacks a key that
`en/admin.php` defines, the English value is used. See
[Keys & Fallback](Keys-and-Fallback.md).

## Edge cases

- A missing `<language>/` directory throws a [`TranslatorException`](Exceptions.md).
- A directory containing **no** `*.php` files loads as an *empty* language:
  lookups simply return the key, and no exception is raised.
- Any `*.php` file that does not `return` an array throws a `TranslatorException`.

```php
// languages/empty/ exists but has no *.php files
$lang->change('empty');
echo $lang->translate('whatever.key'); // "whatever.key" — empty language, no error
```

## Choosing a key naming scheme

Because the first segment is always the file name, pick file names that read
well as prefixes:

| File | Example key |
| --- | --- |
| `admin.php` | `admin.dashboard` |
| `user.php` | `user.profile.title` |
| `emails.php` | `emails.welcome.subject` |
| `errors.php` | `errors.http.404` |

## Where to go next

- [Keys & Fallback](Keys-and-Fallback.md) — resolution order and nested keys.
- [Placeholders](Placeholders.md) — `{name}` interpolation.
- [File Mode](File-Mode.md) — the simpler one-file-per-language layout.

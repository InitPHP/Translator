# Directory mode

In directory mode each language is a **directory** named `<language>/`, and
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

## Selecting directory mode

Directory mode is **not** the default, so you must call `useDirectory()` —
**before** the first language is loaded:

```php
use InitPHP\Translator\Translator;

$lang = new Translator();
$lang->useDirectory()
    ->setDir(__DIR__ . '/languages/')
    ->setDefault('en');
```

> Calling `useDirectory()` after a language has already been loaded throws a
> [`TranslatorException`](../exceptions.md). Set the mode first.

## Namespaces come from file names

The namespace is the file name without `.php`, **lower-cased**. So
`admin.php` is addressed as `admin.*`:

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
echo $lang->translate('admin.dashboard');             // "Dashboard"
echo $lang->translate('admin.menu.settings.title');   // "Settings"  (deeply nested)
```

The first segment selects the file; the remaining segments walk the nested
array inside it.

## Switching languages

```php
$lang->change('tr');
echo $lang->translate('main.hello_world'); // value from tr/main.php
```

When a key is missing in the active language it falls back to the default
language, including for nested keys — see
[Key resolution & fallback](../key-resolution-and-fallback.md).

## Edge cases

- A missing `<language>/` directory throws a [`TranslatorException`](../exceptions.md).
- A directory containing no `*.php` files loads as an empty language: lookups
  simply return the key, and no exception is raised.
- Any `*.php` file that does not `return` an array throws a `TranslatorException`.

See [File mode](file-mode.md) for the simpler one-file-per-language layout.

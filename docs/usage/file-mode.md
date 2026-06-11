# File mode

In file mode each language is a single PHP file named `<language>.php` inside
the base directory. This is the **default** layout.

```
languages/
    en.php
    tr.php
    fr.php
```

## Selecting file mode

File mode is active by default, so `useFile()` is optional. Call it explicitly
when you want the intent to be obvious, but always **before** the first language
is loaded:

```php
use InitPHP\Translator\Translator;

$lang = new Translator();
$lang->useFile()
    ->setDir(__DIR__ . '/languages/')
    ->setDefault('en');
```

## Language file shape

Each file returns an associative array. Values are strings; nested arrays create
[dot-delimited keys](../key-resolution-and-fallback.md#nested-keys).

```php
<?php
// languages/en.php
return [
    'hello'    => 'Hello',
    'welcome'  => 'Welcome {user}',
    'errors'   => [
        'e404' => 'Not Found',
        'e500' => 'Server Error',
    ],
];
```

## Reading keys

```php
echo $lang->translate('hello');            // "Hello"
echo $lang->translate('errors.e404');      // "Not Found"  (nested key)

echo $lang->translate('welcome', null, [   // with a placeholder
    'user' => 'Ada',
]);                                         // "Welcome Ada"
```

## Switching languages

```php
$lang->change('tr');
echo $lang->translate('hello');            // value from tr.php
```

A language file is read from disk only once; subsequent lookups use the
in-memory cache.

## When the file is missing or invalid

- A missing `<language>.php` throws a [`TranslatorException`](../exceptions.md).
- A file that does not `return` an array throws a `TranslatorException`.

See [Directory mode](directory-mode.md) for grouping translations across
multiple files per language.

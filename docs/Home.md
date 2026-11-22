# InitPHP Translator — Wiki

Welcome to the official documentation for **`initphp/translator`** — a micro
multi-language (i18n) translation library for PHP. You keep your translations in
plain PHP files, point the translator at a directory, and read keys with one
call. It supports **nested keys**, **`{name}` placeholder interpolation**, and
an automatic **fallback to a default language** when a translation is missing.

No runtime dependencies. Loaded languages are cached in memory for the lifetime
of the instance.

The package ships three public types:

| Type | Purpose |
| --- | --- |
| [`Translator`](API-Reference.md#translator) | The default concrete implementation (`final`). |
| [`TranslatorInterface`](API-Reference.md#translatorinterface) | The contract — type-hint this in your services. |
| [`TranslatorException`](Exceptions.md) | Thrown on misconfiguration (bad path, missing/invalid pack, wrong call order). |

```bash
composer require initphp/translator
```

```php
use InitPHP\Translator\Translator;

$lang = new Translator();
$lang->setDir(__DIR__ . '/languages/')
    ->setDefault('en');

$lang->change('tr'); // switch the active language

echo $lang->translate('welcome', null, ['user' => 'Ada']);
```

A language file is just a PHP file that returns an array:

```php
<?php
// languages/tr.php
return [
    'welcome' => 'Hoşgeldin {user}',
];
```

## Start here

- **New to the package?** Read [Installation](Installation.md), then
  [Quick Start](Quick-Start.md).
- **One file or one directory per language?** Compare [File Mode](File-Mode.md) and
  [Directory Mode](Directory-Mode.md).
- **How is a key resolved, and when does fallback kick in?** Read
  [Keys & Fallback](Keys-and-Fallback.md).
- **Templating values into messages?** Read [Placeholders](Placeholders.md).
- **Looking for a specific method?** The full [API Reference](API-Reference.md)
  lists every public member.
- **Handling errors?** Read [Exceptions](Exceptions.md).
- **Practical patterns?** See [Recipes](Recipes.md) and [Testing](Testing.md).
- **Upgrading from 0.2?** Read [Migration (0.x → 1.0)](Migration-from-0.x.md).

## At a glance — capabilities

| Capability | Supported |
| --- | :---: |
| One file per language (`en.php`) | ✅ |
| One directory per language (`en/admin.php` → `admin.*`) | ✅ |
| Dot-delimited nested keys (`errors.http.404`) | ✅ |
| `{name}` placeholder interpolation | ✅ |
| Scalar & `Stringable` placeholder values | ✅ |
| Inline per-call fallback text | ✅ |
| Automatic fallback to the default language | ✅ |
| In-memory caching of loaded languages | ✅ |
| Graceful misses (missing key returns the key, never throws) | ✅ |
| Plural rules / gender / ICU MessageFormat | ❌ (not in scope — see [FAQ](FAQ.md)) |

## The resolution model in one line

`translate()` returns the **first** of: the active language → the inline
fallback you passed → the default language → the key itself. See
[Keys & Fallback](Keys-and-Fallback.md) for the full picture.

## Package metadata

- **License**: MIT
- **Minimum PHP**: 8.2 (tested on 8.2 – 8.5)
- **Runtime dependencies**: none
- **Packagist**: [`initphp/translator`](https://packagist.org/packages/initphp/translator)
- **Source**: [github.com/InitPHP/Translator](https://github.com/InitPHP/Translator)
- **Issues**: [github.com/InitPHP/Translator/issues](https://github.com/InitPHP/Translator/issues)
- **Discussions**: [github.com/orgs/InitPHP/discussions](https://github.com/orgs/InitPHP/discussions)
- **Security**: [`SECURITY.md`](https://github.com/InitPHP/.github/blob/main/SECURITY.md)

If something in this wiki is unclear, ambiguous, or wrong, please
[open an issue](https://github.com/InitPHP/Translator/issues/new) —
documentation fixes are reviewed eagerly.

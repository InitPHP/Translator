# Installation

Install `initphp/translator` with Composer, confirm the requirements, and run a
short smoke test before moving on to the [Quick Start](Quick-Start.md).

## Requirements

| | |
| --- | --- |
| PHP | `^8.2` (tested on 8.2, 8.3, 8.4, 8.5, 8.6) |
| Extensions | None beyond the PHP core |
| Runtime dependencies | None |

Development tools (PHPUnit, PHPStan, PHP-CS-Fixer) live under `require-dev` and
are **not** installed when you depend on the package from another project.

## Install via Composer

```bash
composer require initphp/translator
```

The classes are autoloaded under the `InitPHP\Translator` namespace via PSR-4:

```json
{
    "autoload": {
        "psr-4": {
            "InitPHP\\Translator\\": "src/"
        }
    }
}
```

The package ships two public types plus one exception:

| Type | Purpose |
| --- | --- |
| [`Translator`](API-Reference.md#translator) | The default concrete implementation (`final`). |
| [`TranslatorInterface`](API-Reference.md#translatorinterface) | The contract — type-hint this in your services. |
| [`TranslatorException`](Exceptions.md) | Thrown on misconfiguration. |

## Smoke test

Drop this into a script and run it from the CLI. It creates a tiny language file
in a temporary directory, then loads and reads it — exercising the full path
from disk to interpolated output:

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use InitPHP\Translator\Translator;

// 1. Create a throwaway language pack.
$dir = sys_get_temp_dir() . '/translator-smoke';
@mkdir($dir);
file_put_contents($dir . '/en.php', "<?php return ['hello' => 'Hello {name}'];");

// 2. Configure the translator and read a key.
$lang = new Translator();
$lang->setDir($dir)->setDefault('en');

echo $lang->translate('hello', null, ['name' => 'Ada']); // Hello Ada
```

If you see `Hello Ada`, autoloading, file loading, and interpolation all work.

## Supported PHP versions in CI

Every change runs the test matrix against PHP 8.2, 8.3, 8.4, 8.5 and 8.6 (plus a
lowest-dependency run on 8.2). If you find a regression on a supported version,
please [file an issue](https://github.com/InitPHP/Translator/issues/new) and
include the version reported by `php -v`.

## Where to go next

- Take the five-minute tour in [Quick Start](Quick-Start.md).
- Decide on a layout: [File Mode](File-Mode.md) or [Directory Mode](Directory-Mode.md).
- Learn how a key resolves in [Keys & Fallback](Keys-and-Fallback.md).
- Upgrading from 0.2? See [Migration (0.x → 1.0)](Migration-from-0.x.md).

# Recipes

Practical, copy-pasteable patterns for using `initphp/translator` in a real
application. Every snippet runs against the released package.

## A global translation helper

Most apps want a short function instead of passing the translator around. Build
the translator once, then expose a helper:

```php
use InitPHP\Translator\Translator;

function translator(): Translator
{
    static $lang = null;

    if ($lang === null) {
        $lang = new Translator();
        $lang->setDir(__DIR__ . '/languages/')
            ->setDefault('en');
    }

    return $lang;
}

/** Translate-and-return. */
function t(string $key, ?string $fallback = null, array $context = []): string
{
    return translator()->translate($key, $fallback, $context);
}

/** Translate-and-echo. */
function te(string $key, ?string $fallback = null, array $context = []): void
{
    translator()->render($key, $fallback, $context);
}
```

```php
echo t('welcome', null, ['user' => 'Ada']);
te('hello'); // echoes
```

## Pick the language per request

Resolve the active language from the request, always validating against a known
list. `change()` builds a file path from its argument: characters outside
`[A-Za-z0-9_-]` throw, and an unsupported code throws a file-not-found error, so
an unvalidated value breaks the request instead of falling back (see
[Exceptions](Exceptions.md#validate-user-supplied-language-codes)):

```php
$supported = ['en', 'tr', 'fr'];

$requested = $_GET['lang']
    ?? $_COOKIE['lang']
    ?? 'en';

translator()->change(in_array($requested, $supported, true) ? $requested : 'en');
```

### From the `Accept-Language` header

```php
function preferredLanguage(array $supported, string $default): string
{
    $header = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';

    foreach (explode(',', $header) as $part) {
        $code = strtolower(substr(trim($part), 0, 2)); // "en-US;q=0.9" → "en"
        if (in_array($code, $supported, true)) {
            return $code;
        }
    }

    return $default;
}

translator()->change(preferredLanguage(['en', 'tr', 'fr'], 'en'));
```

## Inject the interface (testable services)

Depend on [`TranslatorInterface`](API-Reference.md#translatorinterface), not the
concrete class, so the dependency can be swapped in tests:

```php
use InitPHP\Translator\TranslatorInterface;

final class Mailer
{
    public function __construct(private TranslatorInterface $lang)
    {
    }

    public function welcomeSubject(string $user): string
    {
        return $this->lang->translate('emails.welcome.subject', 'Welcome, {user}!', [
            'user' => $user,
        ]);
    }
}
```

Wire it up once (here with a plain factory; any PSR-11 container works the same
way):

```php
$lang = new Translator();
$lang->setDir(__DIR__ . '/languages/')->setDefault('en');

$mailer = new Mailer($lang);
```

## Organize a large translation set

Use [Directory Mode](Directory-Mode.md) and split each language into focused files.
The file name becomes the key prefix:

```
languages/
    en/
        admin.php     → admin.*
        user.php      → user.*
        emails.php    → emails.*
        errors.php    → errors.*
    tr/
        admin.php
        user.php
        emails.php
        errors.php
```

```php
$lang->useDirectory()->setDir(__DIR__ . '/languages/')->setDefault('en');

$lang->translate('emails.welcome.subject');
$lang->translate('errors.http.404');
```

## A single fallback string for every key

If you prefer "never show a raw key", pass an inline fallback at the call site —
it is interpolated too:

```php
$label = $lang->translate('ui.save_button', 'Save'); // "Save" if the key is missing
```

For a project-wide default, centralize it in your helper:

```php
function t(string $key, string $fallback, array $context = []): string
{
    return translator()->translate($key, $fallback, $context);
}

echo t('ui.save_button', 'Save');
echo t('ui.cancel_button', 'Cancel');
```

## Switch languages mid-request

The translator caches each language it loads, so switching back and forth is
cheap:

```php
$lang->change('en');
$english = $lang->translate('welcome', null, ['user' => 'Ada']);

$lang->change('tr');
$turkish = $lang->translate('welcome', null, ['user' => 'Ada']);
```

## Inspect the current state while debugging

`var_dump()` shows a compact snapshot via
[`__debugInfo()`](API-Reference.md#__debuginfo):

```php
var_dump($lang);
// system => 'file', default => 'en', current => 'tr', container => [ ...active language... ]
```

## Where to go next

- [Testing](Testing.md) — testing code that depends on the translator.
- [Keys & Fallback](Keys-and-Fallback.md) — the resolution rules behind these patterns.
- [FAQ](FAQ.md) — scope questions (plurals, caching, formats).

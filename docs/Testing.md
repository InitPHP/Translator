# Testing

How to test code that uses `initphp/translator`, and how the package itself is
tested.

## Two approaches

1. **Use a real translator** with throwaway language files. Best for testing the
   translation behavior itself (lookups, fallback, interpolation).
2. **Inject a fake** that implements [`TranslatorInterface`](API-Reference.md#translatorinterface).
   Best for testing *your* services without touching the filesystem.

## Approach 1 — a real translator with temp fixtures

The translator reads from disk, so give it a temporary directory:

```php
use InitPHP\Translator\Translator;
use PHPUnit\Framework\TestCase;

final class GreetingTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/tr-' . uniqid();
        mkdir($this->dir);
        file_put_contents($this->dir . '/en.php', "<?php return ['hello' => 'Hello {user}'];");
        file_put_contents($this->dir . '/tr.php', "<?php return ['hello' => 'Merhaba {user}'];");
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dir . '/*.php'));
        rmdir($this->dir);
    }

    public function testInterpolatesAndFallsBack(): void
    {
        $lang = new Translator();
        $lang->setDir($this->dir)->setDefault('en')->change('tr');

        self::assertSame('Merhaba Ada', $lang->translate('hello', null, ['user' => 'Ada']));
        self::assertSame('missing', $lang->translate('missing')); // graceful miss
    }
}
```

Keeping the fixtures inside the test (rather than as committed files) makes the
expectations obvious and self-contained.

### Committed fixtures

For larger suites, commit fixture packs under `tests/` and point `setDir()` at
them:

```php
$lang->setDir(__DIR__ . '/fixtures/languages/')->setDefault('en');
```

## Asserting `render()` output

`render()` echoes, so capture it with PHPUnit's output expectation:

```php
public function testRenderEchoes(): void
{
    $lang = new Translator();
    $lang->setDir($this->dir)->setDefault('en');

    $this->expectOutputString('Hello Ada');
    $lang->render('hello', null, ['user' => 'Ada']);
}
```

Or buffer it manually:

```php
ob_start();
$lang->render('hello', null, ['user' => 'Ada']);
$output = ob_get_clean();
self::assertSame('Hello Ada', $output);
```

## Approach 2 — inject a fake

When you only want to verify that a service *asks* for the right key, implement
the interface with a predictable stub — no files required:

```php
use InitPHP\Translator\TranslatorInterface;

final class FakeTranslator implements TranslatorInterface
{
    /** @var array<string, string> */
    public array $calls = [];

    public function setDir(string $dir): TranslatorInterface { return $this; }
    public function setDefault(string $default): TranslatorInterface { return $this; }
    public function useFile(): TranslatorInterface { return $this; }
    public function useDirectory(): TranslatorInterface { return $this; }
    public function change(string $current): TranslatorInterface { return $this; }

    public function translate(string $key, ?string $fallback = null, array $context = []): string
    {
        $this->calls[] = $key;

        return $fallback ?? $key; // deterministic, no disk access
    }

    public function render(string $key, ?string $fallback = null, array $context = []): void
    {
        echo $this->translate($key, $fallback, $context);
    }

    public function _r(string $key, ?string $fallback = null, array $context = []): string
    {
        return $this->translate($key, $fallback, $context);
    }

    public function _e(string $key, ?string $fallback = null, array $context = []): void
    {
        $this->render($key, $fallback, $context);
    }
}
```

```php
public function testMailerAsksForTheSubjectKey(): void
{
    $fake = new FakeTranslator();
    $mailer = new Mailer($fake);

    $mailer->welcomeSubject('Ada');

    self::assertContains('emails.welcome.subject', $fake->calls);
}
```

Because `Translator` is `final`, write a fake against the **interface** rather
than mocking the concrete class.

## How the package itself is tested

The library ships a PHPUnit suite covering both layouts, the fallback chain
(flat and nested keys), placeholder interpolation, every exception path, the
`render()`/`_e()` output, and the deprecated aliases. Run it from a clone:

```bash
composer install
composer test        # PHPUnit
composer stan        # PHPStan (level max)
composer cs-check    # PHP-CS-Fixer (dry run)
composer ci          # all three
```

## Where to go next

- [Recipes](Recipes.md) — dependency injection and helper patterns.
- [API Reference](API-Reference.md) — the interface your fakes must implement.
- [Exceptions](Exceptions.md) — error paths worth asserting.

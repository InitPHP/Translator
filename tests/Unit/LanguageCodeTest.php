<?php

declare(strict_types=1);

namespace Tests\InitPHP\Translator\Unit;

use InitPHP\Translator\Translator;
use InitPHP\Translator\TranslatorException;
use PHPUnit\Framework\TestCase;

use function file_put_contents;
use function glob;
use function is_dir;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

/**
 * A language code is joined into a path that is `require`d, so anything other
 * than `[A-Za-z0-9_-]+` must be rejected before a file is touched, and a
 * rejection must leave the translator untouched.
 */
final class LanguageCodeTest extends TestCase
{
    private string $base;

    private string $languages;

    protected function setUp(): void
    {
        $this->base = sys_get_temp_dir() . '/initphp-translator-' . uniqid('', true);
        $this->languages = $this->base . '/languages';
        mkdir($this->languages . '/en', 0o777, true);
        mkdir($this->base . '/outside', 0o777, true);

        $probe = '<?php $GLOBALS[\'translatorProbe\'] = true; return [\'hello\' => \'pwned\'];';
        file_put_contents($this->base . '/outside.php', $probe);
        file_put_contents($this->base . '/outside/main.php', $probe);
        file_put_contents($this->languages . '/en.php', '<?php return [\'hello\' => \'Hello\'];');
        file_put_contents($this->languages . '/en_US.php', '<?php return [\'hello\' => \'Hello US\'];');
        file_put_contents($this->languages . '/pt-BR.php', '<?php return [\'hello\' => \'Ola\'];');
        file_put_contents($this->languages . '/en/main.php', '<?php return [\'hello\' => \'Hello\'];');

        unset($GLOBALS['translatorProbe']);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['translatorProbe']);
        $this->remove($this->base);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidCodes(): array
    {
        return [
            'parent traversal'          => ['../outside'],
            'nested traversal'          => ['en/../../outside'],
            'backslash traversal'       => ['..\\outside'],
            'absolute path'             => ['/etc/passwd'],
            'extension'                 => ['en.php'],
            'dot only'                  => ['..'],
            'space'                     => ['en US'],
            'trailing newline'          => ["en\n"],
            'leading newline'           => ["\nen"],
            'null byte'                 => ["en\0"],
            'null byte then traversal'  => ["en\0/../outside"],
            'empty'                     => [''],
        ];
    }

    /**
     * @dataProvider invalidCodes
     */
    public function testChangeRejectsInvalidCodeInFileMode(string $code): void
    {
        $lang = (new Translator())->setDir($this->languages)->setDefault('en');

        try {
            $lang->change($code);
            self::fail('Expected a TranslatorException.');
        } catch (TranslatorException) {
            // Expected.
        }

        self::assertArrayNotHasKey('translatorProbe', $GLOBALS);
        self::assertSame('Hello', $lang->translate('hello'));
        self::assertSame('en', $lang->__debugInfo()['current']);
    }

    /**
     * @dataProvider invalidCodes
     */
    public function testSetDefaultRejectsInvalidCodeInFileMode(string $code): void
    {
        $lang = (new Translator())->setDir($this->languages);

        try {
            $lang->setDefault($code);
            self::fail('Expected a TranslatorException.');
        } catch (TranslatorException) {
            // Expected.
        }

        self::assertArrayNotHasKey('translatorProbe', $GLOBALS);
        self::assertNull($lang->__debugInfo()['default']);
        self::assertNull($lang->__debugInfo()['current']);
    }

    /**
     * @dataProvider invalidCodes
     */
    public function testChangeRejectsInvalidCodeInDirectoryMode(string $code): void
    {
        $lang = (new Translator())->useDirectory()->setDir($this->languages)->setDefault('en');

        try {
            $lang->change($code);
            self::fail('Expected a TranslatorException.');
        } catch (TranslatorException) {
            // Expected.
        }

        self::assertArrayNotHasKey('translatorProbe', $GLOBALS);
        self::assertSame('Hello', $lang->translate('main.hello'));
        self::assertSame('en', $lang->__debugInfo()['current']);
    }

    /**
     * @dataProvider invalidCodes
     */
    public function testSetDefaultRejectsInvalidCodeInDirectoryMode(string $code): void
    {
        $lang = (new Translator())->useDirectory()->setDir($this->languages);

        try {
            $lang->setDefault($code);
            self::fail('Expected a TranslatorException.');
        } catch (TranslatorException) {
            // Expected.
        }

        self::assertArrayNotHasKey('translatorProbe', $GLOBALS);
        self::assertNull($lang->__debugInfo()['default']);
    }

    public function testTraversalCodeIsRejectedWithTheCodeMessageNotAFileError(): void
    {
        $lang = (new Translator())->setDir($this->languages);

        $this->expectException(TranslatorException::class);
        $this->expectExceptionMessage('language code is invalid');

        $lang->change('../outside');
    }

    public function testUnderscoreAndHyphenCodesStillLoad(): void
    {
        $lang = (new Translator())->setDir($this->languages)->setDefault('en');

        self::assertSame('Hello US', $lang->change('en_US')->translate('hello'));
        self::assertSame('Ola', $lang->change('pt-BR')->translate('hello'));
    }

    private function remove(string $path): void
    {
        foreach (glob($path . '/*') ?: [] as $entry) {
            is_dir($entry) ? $this->remove($entry) : unlink($entry);
        }
        if (is_dir($path)) {
            rmdir($path);
        }
    }
}

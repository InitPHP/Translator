<?php

declare(strict_types=1);

namespace Tests\InitPHP\Translator\Unit;

use InitPHP\Translator\Translator;
use InitPHP\Translator\TranslatorException;
use PHPUnit\Framework\TestCase;

use function file_put_contents;
use function is_dir;
use function mkdir;
use function rmdir;
use function scandir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use function var_export;

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
        file_put_contents($this->languages . '/sr@latin.php', '<?php return [\'hello\' => \'Zdravo\'];');
        file_put_contents($this->languages . '/zh.Hant.php', '<?php return [\'hello\' => \'Nihao\'];');
        file_put_contents($this->languages . '/türkçe.php', '<?php return [\'hello\' => \'Merhaba\'];');
        file_put_contents($this->languages . '/en GB.php', '<?php return [\'hello\' => \'Hello GB\'];');
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
            'parent traversal'         => ['../outside'],
            'nested traversal'         => ['en/../../outside'],
            'backslash traversal'      => ['..\\outside'],
            'absolute path'            => ['/etc/passwd'],
            'slash inside'             => ['en/US'],
            'backslash inside'         => ['en\\US'],
            'windows drive path'       => ['C:\\Windows\\x'],
            'null byte'                => ["en\0"],
            'null byte then traversal' => ["en\0/../outside"],
            'empty'                    => [''],
            'parent directory'         => ['..'],
            'current directory'        => ['.'],
            'three dots'               => ['...'],
            'dots and spaces'          => ['. .'],
            'parent with a space'      => ['.. '],
            'only a space'             => [' '],
        ];
    }

    /**
     * Not path components that leave the directory, so they are ordinary names:
     * they reach the loader and fail there only because no such pack exists.
     *
     * @return array<string, array{string}>
     */
    public static function oddButHarmlessCodes(): array
    {
        return [
            'trailing newline' => ["en\n"],
            'colon'            => ['en:stream'],
            'drive letter'     => ['C:'],
            'wrapper prefix'   => ['phar:'],
            'wrapper like'     => ['php:input'],
            'unknown code'     => ['xx'],
        ];
    }

    /**
     * Codes that are plain file names and must keep working as in 2.0.0.
     *
     * @return array<string, array{string, string}>
     */
    public static function acceptedCodes(): array
    {
        return [
            'underscore'     => ['en_US', 'Hello US'],
            'hyphen'         => ['pt-BR', 'Ola'],
            'at sign'        => ['sr@latin', 'Zdravo'],
            'inner dot'      => ['zh.Hant', 'Nihao'],
            'non-ASCII'      => ['türkçe', 'Merhaba'],
            'inner space'    => ['en GB', 'Hello GB'],
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

    /**
     * @dataProvider acceptedCodes
     */
    public function testPlainFileNamesStillLoadInFileMode(string $code, string $hello): void
    {
        $lang = (new Translator())->setDir($this->languages)->setDefault('en');

        self::assertSame($hello, $lang->change($code)->translate('hello'));
    }

    /**
     * @dataProvider acceptedCodes
     */
    public function testPlainDirectoryNamesStillLoadInDirectoryMode(string $code, string $hello): void
    {
        mkdir($this->languages . '/' . $code);
        file_put_contents($this->languages . '/' . $code . '/main.php', '<?php return [\'hello\' => ' . var_export($hello, true) . '];');
        $lang = (new Translator())->useDirectory()->setDir($this->languages)->setDefault('en');

        self::assertSame($hello, $lang->change($code)->translate('main.hello'));
    }

    /**
     * @dataProvider oddButHarmlessCodes
     */
    public function testOtherCodesAreJustMissingPacks(string $code): void
    {
        $lang = (new Translator())->setDir($this->languages)->setDefault('en');

        try {
            $lang->change($code);
            self::fail('Expected a TranslatorException.');
        } catch (TranslatorException $e) {
            self::assertStringContainsString('was not found', $e->getMessage());
        }

        self::assertArrayNotHasKey('translatorProbe', $GLOBALS);
        self::assertSame('en', $lang->__debugInfo()['current']);
    }

    /**
     * `glob()` wildcards in a code must not widen the pattern: `.*` would expand
     * to `..` and load the parent directory's PHP files, `*` every language.
     *
     * @return array<string, array{string}>
     */
    public static function globPatterns(): array
    {
        return [
            'dot star'        => ['.*'],
            'dot question'    => ['.?'],
            'bracketed dots'  => ['[.][.]'],
            'bracket class'   => ['[.]*'],
            'star'            => ['*'],
            'question marks'  => ['??'],
            'bracket range'   => ['[a-z][a-z]'],
        ];
    }

    /**
     * @dataProvider globPatterns
     */
    public function testGlobWildcardsAreNotExpandedInDirectoryMode(string $code): void
    {
        $lang = (new Translator())->useDirectory()->setDir($this->languages)->setDefault('en');

        try {
            $lang->change($code);
            self::fail('Expected a TranslatorException for a wildcard code.');
        } catch (TranslatorException $e) {
            self::assertStringContainsString('was not found', $e->getMessage());
        }

        self::assertArrayNotHasKey('translatorProbe', $GLOBALS);
        self::assertSame('en', $lang->__debugInfo()['current']);
    }

    public function testAWildcardCodeStillLoadsAPackThatHasThatLiteralName(): void
    {
        mkdir($this->languages . '/x*y');
        file_put_contents($this->languages . '/x*y/main.php', '<?php return [\'hello\' => \'Star\'];');
        // A sibling the pattern would also match if it were expanded.
        mkdir($this->languages . '/xzy');
        file_put_contents($this->languages . '/xzy/main.php', '<?php return [\'hello\' => \'Sibling\'];');
        $lang = (new Translator())->useDirectory()->setDir($this->languages)->setDefault('en');

        self::assertSame('Star', $lang->change('x*y')->translate('main.hello'));
    }

    /**
     * Where a directory literally named `.*` exists, the unescaped pattern
     * `languages/.*\/*.php` would also match `..` and load the PHP files of the
     * language directory's parent.
     */
    public function testALiteralDirectoryNamedLikeAWildcardDoesNotReachTheParent(): void
    {
        mkdir($this->languages . '/.*');
        file_put_contents($this->languages . '/.*/main.php', '<?php return [\'hello\' => \'Literal\'];');
        $lang = (new Translator())->useDirectory()->setDir($this->languages)->setDefault('en');

        self::assertSame('Literal', $lang->change('.*')->translate('main.hello'));
        self::assertArrayNotHasKey('translatorProbe', $GLOBALS);
        self::assertSame('outside.hello', $lang->translate('outside.hello'));
    }

    private function remove(string $path): void
    {
        foreach (scandir($path) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $full = $path . '/' . $entry;
            is_dir($full) ? $this->remove($full) : unlink($full);
        }
        rmdir($path);
    }
}

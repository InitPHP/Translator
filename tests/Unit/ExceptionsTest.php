<?php

declare(strict_types=1);

namespace Tests\InitPHP\Translator\Unit;

use InitPHP\Translator\Translator;
use InitPHP\Translator\TranslatorException;
use PHPUnit\Framework\TestCase;

/**
 * Covers every error path that raises a {@see TranslatorException}, plus the
 * graceful (non-throwing) edge cases.
 */
final class ExceptionsTest extends TestCase
{
    public function testSetDirRejectsNonDirectory(): void
    {
        $this->expectException(TranslatorException::class);

        (new Translator())->setDir(__DIR__ . '/does-not-exist');
    }

    public function testMissingLanguageFileThrows(): void
    {
        $lang = (new Translator())->useFile()->setDir(__DIR__ . '/FileLanguages/');

        $this->expectException(TranslatorException::class);

        $lang->setDefault('de');
    }

    public function testMissingLanguageDirectoryThrows(): void
    {
        $lang = (new Translator())->useDirectory()->setDir(__DIR__ . '/DirLanguages/')->setDefault('en');

        $this->expectException(TranslatorException::class);

        $lang->change('fr');
    }

    public function testNonArrayLanguageFileThrows(): void
    {
        $lang = (new Translator())->useFile()->setDir(__DIR__ . '/Fixtures/Broken/');

        $this->expectException(TranslatorException::class);
        $this->expectExceptionMessage('must return an array');

        $lang->setDefault('string_return');
    }

    public function testLoadingBeforeSetDirThrows(): void
    {
        $this->expectException(TranslatorException::class);
        $this->expectExceptionMessage('setDir()');

        (new Translator())->setDefault('en');
    }

    public function testChangingModeAfterLoadThrows(): void
    {
        $lang = (new Translator())->setDir(__DIR__ . '/FileLanguages/')->setDefault('en');

        $this->expectException(TranslatorException::class);
        $this->expectExceptionMessage('cannot be changed');

        $lang->useDirectory();
    }

    public function testReapplyingSameModeAfterLoadIsAllowed(): void
    {
        $lang = (new Translator())->useFile()->setDir(__DIR__ . '/FileLanguages/')->setDefault('en');

        // Same mode again is a harmless no-op, not an error.
        self::assertSame('Hello', $lang->useFile()->translate('hello'));
    }

    public function testEmptyLanguageDirectoryYieldsNoTranslations(): void
    {
        $lang = (new Translator())->useDirectory()->setDir(__DIR__ . '/DirLanguages/')->setDefault('en');

        // The 'empty' directory contains no *.php files; lookups return the key
        // and no exception is raised.
        $lang->change('empty');

        self::assertSame('whatever.key', $lang->translate('whatever.key'));
    }
}

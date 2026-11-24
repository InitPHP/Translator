<?php

declare(strict_types=1);

namespace Tests\InitPHP\Translator\Unit;

use InitPHP\Translator\Translator;
use InitPHP\Translator\TranslatorException;
use PHPUnit\Framework\TestCase;

/**
 * A language that cannot be loaded must leave the translator exactly as it was.
 */
final class FailedLoadTest extends TestCase
{
    public function testChangeToAMissingLanguageKeepsTheCurrentAndDefaultLanguage(): void
    {
        $lang = (new Translator())->setDir(__DIR__ . '/FileLanguages/')->setDefault('en');

        try {
            $lang->change('xx');
            self::fail('Expected a TranslatorException.');
        } catch (TranslatorException) {
            // Expected.
        }

        self::assertSame('en', $lang->__debugInfo()['current']);
        self::assertSame('en', $lang->__debugInfo()['default']);
        self::assertSame('Hello', $lang->translate('hello'));
    }

    public function testChangeToAMissingLanguageDoesNotMakeItTheDefault(): void
    {
        $lang = (new Translator())->setDir(__DIR__ . '/FileLanguages/');

        try {
            $lang->change('xx');
            self::fail('Expected a TranslatorException.');
        } catch (TranslatorException) {
            // Expected.
        }

        self::assertNull($lang->__debugInfo()['current']);
        self::assertNull($lang->__debugInfo()['default']);
    }

    public function testSetDefaultWithAMissingLanguageChangesNothing(): void
    {
        $lang = (new Translator())->setDir(__DIR__ . '/FileLanguages/');

        try {
            $lang->setDefault('xx');
            self::fail('Expected a TranslatorException.');
        } catch (TranslatorException) {
            // Expected.
        }

        self::assertNull($lang->__debugInfo()['default']);
        self::assertNull($lang->__debugInfo()['current']);
    }

    public function testSetDefaultWithAMissingLanguageKeepsAnExistingDefault(): void
    {
        $lang = (new Translator())->setDir(__DIR__ . '/FileLanguages/')->setDefault('en');

        try {
            $lang->setDefault('xx');
            self::fail('Expected a TranslatorException.');
        } catch (TranslatorException) {
            // Expected.
        }

        self::assertSame('en', $lang->__debugInfo()['default']);
    }

    public function testAPartiallyLoadedDirectoryLanguageIsNotCached(): void
    {
        $lang = (new Translator())->useDirectory()->setDir(__DIR__ . '/Fixtures/BrokenDir/')->setDefault('en');

        foreach ([1, 2] as $attempt) {
            try {
                $lang->change('bad');
                self::fail('Expected a TranslatorException on attempt ' . $attempt . '.');
            } catch (TranslatorException $e) {
                self::assertStringContainsString('must return an array', $e->getMessage());
            }
        }

        self::assertSame('en', $lang->__debugInfo()['current']);
        self::assertSame('Hello', $lang->translate('main.hello'));
        self::assertSame('bad.half', $lang->change('en')->translate('bad.half'));
    }
}

<?php

declare(strict_types=1);

namespace Tests\InitPHP\Translator\Unit;

use InitPHP\Translator\Translator;
use PHPUnit\Framework\TestCase;

/**
 * Covers the "one file per language" layout: basic lookups, placeholder
 * interpolation, switching the active language and the inline/default-language
 * fallback behaviour.
 */
final class FileTranslatorTest extends TestCase
{
    private Translator $lang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lang = new Translator();
        $this->lang->useFile()
            ->setDir(__DIR__ . '/FileLanguages/')
            ->setDefault('en');
    }

    public function testReturnsPlainTranslation(): void
    {
        self::assertSame('Hello', $this->lang->translate('hello'));
    }

    public function testInterpolatesPlaceholder(): void
    {
        self::assertSame('Welcome Muhammet', $this->lang->translate('welcome', null, [
            'user' => 'Muhammet',
        ]));
    }

    public function testChangeSwitchesActiveLanguage(): void
    {
        $this->lang->change('tr');

        self::assertSame('Hoşgeldin Muhammet', $this->lang->translate('welcome', null, [
            'user' => 'Muhammet',
        ]));
    }

    public function testMissingKeyWithoutFallbackReturnsKey(): void
    {
        $this->lang->change('tr');

        self::assertSame('hi', $this->lang->translate('hi', null, [
            'user' => 'Muhammet',
        ]));
    }

    public function testInlineFallbackIsUsedAndInterpolated(): void
    {
        $this->lang->change('tr');

        self::assertSame('Merhaba Muhammet', $this->lang->translate('hi', 'Merhaba {user}', [
            'user' => 'Muhammet',
        ]));
    }

    public function testInlineFallbackWinsOverDefaultLanguage(): void
    {
        $this->lang->change('tr');

        // 'errorMsg' exists in the default (en) language, but the inline fallback
        // must take precedence because it was explicitly provided.
        self::assertSame('Error Code : 2005', $this->lang->translate('errorMsg', 'Error Code : {code}', [
            'code' => 2005,
        ]));
    }

    public function testFallsBackToDefaultLanguageWhenNoInlineFallback(): void
    {
        $this->lang->change('tr');

        // 'errorMsg' is missing from tr and no inline fallback is given, so the
        // default (en) language is consulted.
        self::assertSame('Something went wrong. Code : 1002', $this->lang->translate('errorMsg', null, [
            'code' => 1002,
        ]));
    }

    public function testDebugInfoExposesState(): void
    {
        $info = $this->lang->__debugInfo();

        self::assertSame('file', $info['system']);
        self::assertSame('en', $info['default']);
        self::assertSame('en', $info['current']);
        self::assertArrayHasKey('container', $info);
    }
}

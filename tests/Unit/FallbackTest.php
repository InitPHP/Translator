<?php

declare(strict_types=1);

namespace Tests\InitPHP\Translator\Unit;

use InitPHP\Translator\Translator;
use PHPUnit\Framework\TestCase;

/**
 * Regression and behaviour tests for the default-language fallback.
 *
 * These guard the historical bug where dot-delimited keys were always resolved
 * against the *active* language, so a nested key present only in the default
 * language never fell back. Both file and directory layouts are covered.
 */
final class FallbackTest extends TestCase
{
    private function fileMode(string $current): Translator
    {
        $lang = new Translator();
        $lang->useFile()
            ->setDir(__DIR__ . '/FileLanguages/')
            ->setDefault('en')
            ->change($current);

        return $lang;
    }

    private function directoryMode(string $current): Translator
    {
        $lang = new Translator();
        $lang->useDirectory()
            ->setDir(__DIR__ . '/DirLanguages/')
            ->setDefault('en')
            ->change($current);

        return $lang;
    }

    public function testFlatKeyFallsBackToDefaultLanguageInFileMode(): void
    {
        // 'only_en' exists solely in the default (en) language.
        self::assertSame('English only', $this->fileMode('tr')->translate('only_en'));
    }

    public function testDottedKeyFallsBackToDefaultLanguageInFileMode(): void
    {
        // 'errors.e404' exists only in en; tr only defines 'errors.e500'.
        // Before the fix this returned the key because the nested lookup read
        // from the active (tr) language instead of the default (en).
        self::assertSame('Not Found', $this->fileMode('tr')->translate('errors.e404'));
    }

    public function testDeepDottedKeyFallsBackToDefaultLanguageInFileMode(): void
    {
        self::assertSame('Unauthorized', $this->fileMode('tr')->translate('errors.http.unauthorized'));
    }

    public function testFlatKeyFallsBackToDefaultLanguageInDirectoryMode(): void
    {
        self::assertSame('English only', $this->directoryMode('tr')->translate('main.only_en'));
    }

    public function testDeepDottedKeyFallsBackToDefaultLanguageInDirectoryMode(): void
    {
        // tr/admin.php has no 'menu' namespace; en/admin.php does.
        self::assertSame('Settings', $this->directoryMode('tr')->translate('admin.menu.settings.title'));
    }

    public function testActiveLanguageWinsOverDefault(): void
    {
        self::assertSame('Sunucu Hatası', $this->fileMode('tr')->translate('errors.e500'));
    }

    public function testNoFallbackWhenActiveEqualsDefault(): void
    {
        // Active and default are both 'en'; a missing key returns itself.
        self::assertSame('does.not.exist', $this->fileMode('en')->translate('does.not.exist'));
    }

    public function testReturnsKeyWhenMissingEverywhere(): void
    {
        self::assertSame('totally.unknown', $this->fileMode('tr')->translate('totally.unknown'));
    }

    public function testTranslateBeforeConfigurationReturnsKey(): void
    {
        // No directory, default or active language configured: degrade gracefully.
        $lang = new Translator();

        self::assertSame('anything', $lang->translate('anything'));
    }
}

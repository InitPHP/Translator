<?php

declare(strict_types=1);

namespace Tests\InitPHP\Translator\Unit;

use InitPHP\Translator\Translator;
use PHPUnit\Framework\TestCase;

/**
 * Covers the "one directory per language" layout, where each `*.php` file
 * becomes a dot-delimited namespace (e.g. `admin.dashboard`).
 */
final class DirTranslatorTest extends TestCase
{
    private Translator $lang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lang = new Translator();
        $this->lang->useDirectory()
            ->setDir(__DIR__ . '/DirLanguages/')
            ->setDefault('en');
    }

    public function testReadsNamespacedKeyFromActiveLanguage(): void
    {
        self::assertSame('Hello World', $this->lang->translate('main.hello_world'));
    }

    public function testChangeSwitchesNamespacedLookups(): void
    {
        $this->lang->change('tr');

        self::assertSame('Merhaba Dünya', $this->lang->translate('main.hello_world'));
        self::assertSame('Yönetim Paneli', $this->lang->translate('admin.dashboard'));

        $this->lang->change('en');

        self::assertSame('Dashboard', $this->lang->translate('admin.dashboard'));
    }

    public function testResolvesDeeplyNestedKey(): void
    {
        self::assertSame('Settings', $this->lang->translate('admin.menu.settings.title'));
    }

    public function testMissingNamespaceFileReturnsKey(): void
    {
        self::assertSame('profile.name', $this->lang->translate('profile.name'));
    }

    public function testDebugInfoReportsDirectorySystem(): void
    {
        $info = $this->lang->__debugInfo();

        self::assertSame('directory', $info['system']);
    }
}

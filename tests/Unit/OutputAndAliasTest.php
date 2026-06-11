<?php

declare(strict_types=1);

namespace Tests\InitPHP\Translator\Unit;

use InitPHP\Translator\Translator;
use PHPUnit\Framework\TestCase;

/**
 * Covers the echoing helper {@see Translator::render()} and the backward
 * compatible {@see Translator::_r()} / {@see Translator::_e()} aliases.
 */
final class OutputAndAliasTest extends TestCase
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

    public function testRenderEchoesTranslation(): void
    {
        $this->expectOutputString('Welcome Ada');

        $this->lang->render('welcome', null, ['user' => 'Ada']);
    }

    public function testDeprecatedEchoAliasMatchesRender(): void
    {
        $this->expectOutputString('Hello');

        $this->lang->_e('hello');
    }

    public function testDeprecatedReturnAliasMatchesTranslate(): void
    {
        self::assertSame(
            $this->lang->translate('welcome', null, ['user' => 'Ada']),
            $this->lang->_r('welcome', null, ['user' => 'Ada'])
        );
    }

    public function testDeprecatedReturnAliasHonoursFallback(): void
    {
        $this->lang->change('tr');

        self::assertSame('Merhaba Ada', $this->lang->_r('hi', 'Merhaba {user}', ['user' => 'Ada']));
    }
}

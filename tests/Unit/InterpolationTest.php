<?php

declare(strict_types=1);

namespace Tests\InitPHP\Translator\Unit;

use InitPHP\Translator\Translator;
use PHPUnit\Framework\TestCase;
use Stringable;

/**
 * Covers placeholder interpolation and the values a context map may hold.
 */
final class InterpolationTest extends TestCase
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

    public function testEmptyContextLeavesMessageUntouched(): void
    {
        self::assertSame('Welcome {user}', $this->lang->translate('welcome'));
    }

    public function testStringPlaceholder(): void
    {
        self::assertSame('Welcome Ada', $this->lang->translate('welcome', null, ['user' => 'Ada']));
    }

    public function testIntegerPlaceholderIsStringified(): void
    {
        self::assertSame('Something went wrong. Code : 500', $this->lang->translate('errorMsg', null, [
            'code' => 500,
        ]));
    }

    public function testFloatPlaceholderIsStringified(): void
    {
        self::assertSame('Rate: 3.5', $this->lang->translate('missing', 'Rate: {rate}', [
            'rate' => 3.5,
        ]));
    }

    public function testStringablePlaceholderIsStringified(): void
    {
        $money = new class () implements Stringable {
            public function __toString(): string
            {
                return '€9.99';
            }
        };

        self::assertSame('Total: €9.99', $this->lang->translate('missing', 'Total: {amount}', [
            'amount' => $money,
        ]));
    }

    public function testUnmatchedPlaceholderIsLeftIntact(): void
    {
        self::assertSame('Welcome {user}', $this->lang->translate('welcome', null, ['other' => 'x']));
    }

    public function testArrayContextValueIsIgnored(): void
    {
        // An array value cannot be interpolated, so its marker is left untouched
        // rather than throwing.
        self::assertSame('Welcome {user}', $this->lang->translate('welcome', null, [
            'user' => ['not', 'usable'],
        ]));
    }

    public function testObjectWithoutToStringIsIgnored(): void
    {
        self::assertSame('Welcome {user}', $this->lang->translate('welcome', null, [
            'user' => new \stdClass(),
        ]));
    }

    public function testMultiplePlaceholders(): void
    {
        self::assertSame('a=1, b=2', $this->lang->translate('missing', '{a}={a_val}, {b}={b_val}', [
            'a'     => 'a',
            'a_val' => 1,
            'b'     => 'b',
            'b_val' => 2,
        ]));
    }

    public function testRequestingNamespaceNodeReturnsKeyInsteadOfThrowing(): void
    {
        // 'errors' resolves to an array (a namespace), not a leaf string. It must
        // be treated as a miss and returned as the key, never passed to the
        // string interpolation routine.
        self::assertSame('errors', $this->lang->translate('errors'));
    }

    public function testRequestingPartialNestedPathReturnsKey(): void
    {
        // 'errors.http' is a nested array, not a leaf string.
        self::assertSame('errors.http', $this->lang->translate('errors.http'));
    }
}

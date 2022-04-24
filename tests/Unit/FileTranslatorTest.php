<?php
declare(strict_types=1);

namespace Tests\InitPHP\Translator\Unit;

use \InitPHP\Translator\Translator;

class FileTranslatorTest extends \PHPUnit\Framework\TestCase
{
    protected Translator $lang;

    protected function setUp(): void
    {
        $this->lang = new Translator();
        $this->lang->useFile()
                    ->setDir(__DIR__ . '/FileLanguages/')
                    ->setDefault('en');
        parent::setUp();
    }

    public function testPlaceholder()
    {
        $expected = 'Welcome Muhammet';

        $this->assertEquals($expected, $this->lang->_r('welcome', null, [
            'user'  => 'Muhammet'
        ]));
    }

    public function testLanguageChange()
    {
        $this->lang->change('tr');
        $expected = 'Hoşgeldin Muhammet';

        $this->assertEquals($expected, $this->lang->_r('welcome', null, [
            'user'  => 'Muhammet'
        ]));

        // Reset Current Language
        $this->lang->change('en');
    }

    public function testDefaultValue()
    {
        $this->lang->change('tr');

        // Returns the key value if it is not present in the current and default language.
        $this->assertEquals('hi', $this->lang->_r('hi', null, [
            'user'  => 'Muhammet'
        ]));

        // If it is not available in the current language, but a string is specified in the second parameter, the specified string is used.
        $this->assertEquals('Merhaba Muhammet', $this->lang->_r('hi', 'Merhaba {user}', [
            'user'  => 'Muhammet'
        ]));

        // If it does not exist in the current language and a string is specified in the second parameter; the specified string is used.
        $this->assertEquals('Error Code : 2005', $this->lang->_r('errorMsg', 'Error Code : {code}', [
            'code'  => 2005
        ]));

        // If it does not exist in the current language and the second parameter is empty; default language is used.
        $this->assertEquals('Something went wrong. Code : 1002', $this->lang->_r('errorMsg', null, [
            'code'  => 1002
        ]));

        // Reset Current Language
        $this->lang->change('en');
    }

}

# InitPHP Translator

This library; It is a micro library that will allow you to add multi-language support to your projects or libraries.

[![Latest Stable Version](http://poser.pugx.org/initphp/translator/v)](https://packagist.org/packages/initphp/translator) [![Total Downloads](http://poser.pugx.org/initphp/translator/downloads)](https://packagist.org/packages/initphp/translator) [![Latest Unstable Version](http://poser.pugx.org/initphp/translator/v/unstable)](https://packagist.org/packages/initphp/translator) [![License](http://poser.pugx.org/initphp/translator/license)](https://packagist.org/packages/initphp/translator) [![PHP Version Require](http://poser.pugx.org/initphp/translator/require/php)](https://packagist.org/packages/initphp/translator)

## Requirements

- PHP 7.4 or higher

## Installation

```
composer require initphp/translator
```

## Usage

```php
require_once "vendor/autoload.php";
use \InitPHP\Translator\Translator;

$lang = new Translator();
$lang->setDir(__DIR__ . '/languages/')
    ->setDefault('en');

$lang->change('tr'); // Set Current Language

echo $lang->_r('hello');
```

What does a language file look like?

```php
<?php
return [
    'hello'     => 'Hello {user}',
    'today'     => 'It\'s {day}',
];
```

### File? Directory?

You can use a single file for each language or multiple files under a directory. 

#### Use File

If you are going to use a single file for a language; Your directory structure will look something like this;

```
/languages/
    en.php
    tr.php
    fr.php
```

Your code looks like the following;

```php
require_once "vendor/autoload.php";
use \InitPHP\Translator\Translator;

$lang = new Translator;
$lang->useFile(); // Note that it is used first of all.
$lang->setDir(__DIR__ . '/languages/')
    ->setDefault('en');

echo $lang->_r('hello');
```

#### Use Directory

If you want to use directories that contain multiple files for each language, your directory structure will be something like this;

```
/languages/
    en/
        user.php
        admin.php
        profile.php
    tr/
        user.php
        admin.php
        profile.php
```

Your code looks like the following;

```php
require_once "vendor/autoload.php";
use \InitPHP\Translator\Translator;

$lang = new Translator;
$lang->useDirectory(); // Note that it is used first of all.
$lang->setDir(__DIR__ . '/languages/')
    ->setDefault('en');

// The filename and keyname are separated by dots.
// Example : "filename.key"
echo $lang->_r('user.hello');
```

### Methods

#### `setDir()`

Defines the full path to the parent directory where the language files are kept.

**Structure :**

```php
public function setDir(string $dir): self
```

#### `setDefault()`

Defines the main translation language to be used by default if the desired translation in the current language is not found.

**Structure :**

```php
public function setDefault(string $default): self
```

_Note :_ If a text is given by default during use; this library uses the default string you provided at the time of use.

#### `useFile()` and `useDirectory()`

These two methods; tells you whether to use a single php file or a directory for localization. [See "File? Directory?" above for more.](#file-directory)

_Note :_ If not specified, the file system is used by default.

**Structures :**

```php
public function useFile(): self;

public function useDirectory(): self;
```

#### `change()`

Changes the current language and loads the desired language if it is not already installed.

**Structure :**

```php
public function change(string $current): self
```

#### `_r()`

Returns the desired translation value.

**Structure :**

```php
public function _r(string $key, ?string $default = null, array $context = []): string
```

- `$key` : The key to the desired translation.
- `$default` : The string to substitute if the requested translation is not found.
- `$context` : An associative array that reports the value of placeholders, if any, in the translation.

#### `_e()`

Outputs the desired value directly.

- `$lang->_e('hello')` = `echo $lang->_r('hello')`

**Structure :**

```php
public function _e(string $key, ?string $default = null, array $context = []): void
```

## Getting Help

If you have questions, concerns, bug reports, etc, please file an issue in this repository's Issue Tracker.

## Contributing

> All contributions to this project will be published under the MIT License. By submitting a pull request or filing a bug, issue, or feature request, you are agreeing to comply with this waiver of copyright interest.

- Fork it ( https://github.com/initphp/translator/fork )
- Create your feature branch (`git checkout -b my-new-feature`)
- Commit your changes (`git commit -am "Add some feature"`)
- Push to the branch (`git push origin my-new-feature`)
- Create a new Pull Request

## Credits

- [Muhammet ŞAFAK](https://www.muhammetsafak.com.tr) <<info@muhammetsafak.com.tr>>

## License

Copyright &copy; 2022 [MIT License](./LICENSE)

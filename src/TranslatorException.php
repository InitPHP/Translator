<?php

/**
 * This file is part of the InitPHP Translator package.
 *
 * @author    Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright Copyright © 2022 InitPHP
 * @license   https://github.com/InitPHP/Translator/blob/main/LICENSE  MIT
 * @link      https://github.com/InitPHP/Translator
 */

declare(strict_types=1);

namespace InitPHP\Translator;

use Exception;

/**
 * Thrown for every error condition this package can raise, so callers can
 * catch a single, package-specific exception type.
 *
 * It is raised when:
 * - {@see TranslatorInterface::setDir()} is given a path that is not a directory;
 * - a language is loaded before the directory has been set with `setDir()`;
 * - the file/directory layout mode is changed after a language has been loaded;
 * - a requested language file or directory cannot be found or read;
 * - a language file does not `return` an array.
 */
class TranslatorException extends Exception
{
}

<?php
/**
 * TranslatorInterface.php
 *
 * This file is part of InitPHP Translator.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 InitPHP Translator
 * @license    http://initphp.github.io/license.txt  MIT
 * @version    1.0
 * @link       https://www.muhammetsafak.com.tr
 */

declare(strict_types=1);

namespace InitPHP\Translator;

interface TranslatorInterface
{

    /**
     * @param string $dir
     * @return TranslatorInterface
     */
    public function setDir(string $dir): TranslatorInterface;

    /**
     * @param string $default
     * @return TranslatorInterface
     * @throws TranslatorException
     */
    public function setDefault(string $default): TranslatorInterface;

    /**
     * @return TranslatorInterface
     */
    public function useFile(): TranslatorInterface;

    /**
     * @return TranslatorInterface
     */
    public function useDirectory(): TranslatorInterface;

    /**
     * @param string $current
     * @return TranslatorInterface
     * @throws TranslatorException
     */
    public function change(string $current): TranslatorInterface;

    /**
     * @param string $key
     * @param string|null $default
     * @param string[] $context
     * @return string
     */
    public function _r(string $key, ?string $default = null, array $context = []): string;

    /**
     * @param string $key
     * @param string|null $default
     * @param string[] $context
     * @return void
     */
    public function _e(string $key, ?string $default = null, array $context = []): void;

}

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

/**
 * Contract for the multi-language translator.
 *
 * A translator is configured with a base directory ({@see self::setDir()}), a
 * layout mode ({@see self::useFile()} or {@see self::useDirectory()}) and a
 * default language ({@see self::setDefault()}). Translations are then read with
 * {@see self::translate()} / {@see self::render()}, optionally switching the
 * active language with {@see self::change()}.
 *
 * Placeholders inside a translation are written as `{name}` and replaced from
 * the `$context` map passed to {@see self::translate()}.
 */
interface TranslatorInterface
{
    /**
     * Sets the base directory that holds the language files or directories.
     *
     * Any trailing `/` or `\` is removed. This must be called before a language
     * is loaded (i.e. before {@see self::setDefault()} or {@see self::change()}).
     *
     * @param string $dir Absolute path to the directory that contains the language packs.
     * @return TranslatorInterface The same instance, for chaining.
     * @throws TranslatorException If `$dir` is not an existing directory.
     */
    public function setDir(string $dir): TranslatorInterface;

    /**
     * Sets and eagerly loads the default (fallback) language.
     *
     * The default language is consulted by {@see self::translate()} when a key
     * is missing from the active language and no inline fallback was given. If
     * no active language has been chosen yet, it also becomes the active one.
     *
     * @param string $default Language identifier, e.g. `en` (a file name without
     *                        extension in file mode, or a sub-directory name in
     *                        directory mode).
     * @return TranslatorInterface The same instance, for chaining.
     * @throws TranslatorException If the directory is not set, or the language
     *                             pack cannot be found, read or does not return an array.
     */
    public function setDefault(string $default): TranslatorInterface;

    /**
     * Selects the "one file per language" layout (the default).
     *
     * Each language is a single PHP file named `<lang>.php` inside the base
     * directory. Must be called before any language is loaded.
     *
     * @return TranslatorInterface The same instance, for chaining.
     * @throws TranslatorException If the mode is changed after a language has
     *                             already been loaded.
     */
    public function useFile(): TranslatorInterface;

    /**
     * Selects the "one directory per language" layout.
     *
     * Each language is a sub-directory named `<lang>/` whose `*.php` files are
     * loaded into namespaces keyed by their (lower-cased) file name. Must be
     * called before any language is loaded.
     *
     * @return TranslatorInterface The same instance, for chaining.
     * @throws TranslatorException If the mode is changed after a language has
     *                             already been loaded.
     */
    public function useDirectory(): TranslatorInterface;

    /**
     * Changes the active language, loading it on first use.
     *
     * If no default language has been set yet, the given language also becomes
     * the default.
     *
     * @param string $current Language identifier to activate.
     * @return TranslatorInterface The same instance, for chaining.
     * @throws TranslatorException If the directory is not set, or the language
     *                             pack cannot be found, read or does not return an array.
     */
    public function change(string $current): TranslatorInterface;

    /**
     * Returns the translation for a key, with interpolation and fallback.
     *
     * Resolution order:
     * 1. The active language. In directory mode (or for nested values) the key
     *    is dot-delimited, e.g. `admin.dashboard` or `errors.http.404`.
     * 2. The inline `$fallback` string, if one was provided (it is interpolated too).
     * 3. The default language, when it differs from the active one.
     * 4. The key itself, returned verbatim, when nothing matched.
     *
     * @param string $key The translation key (dot-delimited for nested values).
     * @param string|null $fallback Text returned when the key is missing from the
     *                              active language. `''` and `'0'` count as provided.
     * @param array<string, mixed> $context Placeholder values
     *                              substituted into `{name}` markers.
     * @return string The interpolated translation, the interpolated fallback, or `$key`.
     */
    public function translate(string $key, ?string $fallback = null, array $context = []): string;

    /**
     * Echoes the value produced by {@see self::translate()}.
     *
     * @param string $key The translation key (dot-delimited for nested values).
     * @param string|null $fallback Text used when the key is missing from the active language.
     * @param array<string, mixed> $context Placeholder values.
     * @return void
     */
    public function render(string $key, ?string $fallback = null, array $context = []): void;

    /**
     * Alias of {@see self::translate()}.
     *
     * @param string $key The translation key (dot-delimited for nested values).
     * @param string|null $fallback Text returned when the key is missing from the active language.
     * @param array<string, mixed> $context Placeholder values.
     * @return string
     * @deprecated since 1.0, use {@see self::translate()} instead. Kept for backward compatibility.
     */
    public function _r(string $key, ?string $fallback = null, array $context = []): string;

    /**
     * Alias of {@see self::render()}.
     *
     * @param string $key The translation key (dot-delimited for nested values).
     * @param string|null $fallback Text used when the key is missing from the active language.
     * @param array<string, mixed> $context Placeholder values.
     * @return void
     * @deprecated since 1.0, use {@see self::render()} instead. Kept for backward compatibility.
     */
    public function _e(string $key, ?string $fallback = null, array $context = []): void;
}

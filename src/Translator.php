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

use function array_shift;
use function basename;
use function explode;
use function glob;
use function is_dir;
use function is_file;
use function method_exists;
use function rtrim;
use function str_contains;
use function strtolower;
use function strtr;

use const DIRECTORY_SEPARATOR;

/**
 * File-based multi-language translator.
 *
 * Supports two on-disk layouts (see {@see self::useFile()} /
 * {@see self::useDirectory()}), dot-delimited nested keys, `{name}` placeholder
 * interpolation and fallback to a default language. Loaded languages are cached
 * in memory for the lifetime of the instance.
 *
 * @see TranslatorInterface for the documented contract.
 */
final class Translator implements TranslatorInterface
{
    /**
     * In-memory cache of loaded languages, keyed by language identifier.
     *
     * @var array<string, array<array-key, mixed>>
     */
    private array $container = [];

    /** Base directory holding the language packs, without a trailing separator. */
    private ?string $dir = null;

    /** The default (fallback) language identifier, or null until set. */
    private ?string $default = null;

    /** The currently active language identifier, or null until set. */
    private ?string $current = null;

    /** Whether each language is a directory of files (true) or a single file (false). */
    private bool $languagesAreDirectory = false;

    /**
     * Returns a debug-friendly snapshot of the translator state.
     *
     * @return array{system: string, default: string|null, current: string|null, container?: array<array-key, mixed>|null}
     */
    public function __debugInfo(): array
    {
        $info = [
            'system'  => $this->languagesAreDirectory ? 'directory' : 'file',
            'default' => $this->default,
            'current' => $this->current,
        ];

        if ($this->current !== null) {
            $info['container'] = $this->container[$this->current] ?? null;
        }

        return $info;
    }

    /**
     * {@inheritDoc}
     */
    public function setDir(string $dir): self
    {
        if (!is_dir($dir)) {
            throw new TranslatorException('The translation directory "' . $dir . '" does not exist.');
        }

        $this->dir = rtrim($dir, '/\\');

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefault(string $default): self
    {
        $this->default = $default;
        $this->ensureLoaded($default);
        $this->current ??= $default;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function useFile(): self
    {
        return $this->setMode(false);
    }

    /**
     * {@inheritDoc}
     */
    public function useDirectory(): self
    {
        return $this->setMode(true);
    }

    /**
     * {@inheritDoc}
     */
    public function change(string $current): self
    {
        $this->current = $current;
        $this->ensureLoaded($current);
        $this->default ??= $current;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function translate(string $key, ?string $fallback = null, array $context = []): string
    {
        if ($this->current !== null) {
            $value = $this->lookup($this->current, $key, $context);
            if ($value !== null) {
                return $value;
            }
        }

        if ($fallback !== null) {
            return $this->interpolate($fallback, $context);
        }

        if ($this->default !== null && $this->default !== $this->current) {
            $value = $this->lookup($this->default, $key, $context);
            if ($value !== null) {
                return $value;
            }
        }

        return $key;
    }

    /**
     * {@inheritDoc}
     */
    public function render(string $key, ?string $fallback = null, array $context = []): void
    {
        echo $this->translate($key, $fallback, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function _r(string $key, ?string $fallback = null, array $context = []): string
    {
        return $this->translate($key, $fallback, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function _e(string $key, ?string $fallback = null, array $context = []): void
    {
        $this->render($key, $fallback, $context);
    }

    /**
     * Resolves a key within a single language, returning the interpolated value.
     *
     * Returns null (a "miss") when the language is not loaded, the key is absent,
     * or the key resolves to a non-string value (e.g. a nested namespace array).
     *
     * @param string $language The language to read from.
     * @param string $key The translation key (dot-delimited for nested values).
     * @param array<string, mixed> $context Placeholder values.
     * @return string|null The interpolated translation, or null on a miss.
     */
    private function lookup(string $language, string $key, array $context): ?string
    {
        if (str_contains($key, '.')) {
            $segments = explode('.', $key);
            $value = $this->container[$language][$segments[0]] ?? null;
            array_shift($segments);

            foreach ($segments as $segment) {
                if (!\is_array($value) || !isset($value[$segment])) {
                    return null;
                }
                $value = $value[$segment];
            }
        } else {
            $value = $this->container[$language][$key] ?? null;
        }

        if (!\is_string($value)) {
            return null;
        }

        return $this->interpolate($value, $context);
    }

    /**
     * Replaces `{name}` placeholders in a message with values from the context.
     *
     * Array values and objects without `__toString()` are skipped; every other
     * value is cast to a string. Markers without a matching context entry are
     * left untouched.
     *
     * @param string $message The message containing `{name}` markers.
     * @param array<string, mixed> $context Placeholder values.
     * @return string The message with placeholders substituted.
     */
    private function interpolate(string $message, array $context): string
    {
        if ($context === []) {
            return $message;
        }

        $replace = [];
        foreach ($context as $key => $value) {
            if (\is_array($value)) {
                continue;
            }
            if (\is_object($value) && !method_exists($value, '__toString')) {
                continue;
            }
            /** @var scalar|\Stringable|null $value */
            $replace['{' . $key . '}'] = (string) $value;
        }

        return $replace === [] ? $message : strtr($message, $replace);
    }

    /**
     * Loads a language into the in-memory cache if it is not already present.
     *
     * @param string $lang The language identifier to load.
     * @return void
     * @throws TranslatorException If the language pack cannot be loaded.
     */
    private function ensureLoaded(string $lang): void
    {
        if (!isset($this->container[$lang])) {
            $this->load($lang);
        }
    }

    /**
     * Sets the file/directory layout mode, guarding against late changes.
     *
     * @param bool $asDirectory True for the directory layout, false for the file layout.
     * @return self
     * @throws TranslatorException If a language has already been loaded under a different mode.
     */
    private function setMode(bool $asDirectory): self
    {
        if ($this->container !== [] && $this->languagesAreDirectory !== $asDirectory) {
            throw new TranslatorException(
                'The file/directory mode cannot be changed after a language has been loaded. '
                . 'Call useFile()/useDirectory() before setDefault()/change().'
            );
        }

        $this->languagesAreDirectory = $asDirectory;

        return $this;
    }

    /**
     * Reads a language pack from disk into the cache.
     *
     * In file mode a single `<lang>.php` file is loaded. In directory mode every
     * `*.php` file inside `<lang>/` is loaded into a namespace keyed by its
     * lower-cased file name.
     *
     * @param string $lang The language identifier to load.
     * @return void
     * @throws TranslatorException If the directory is unset, or the file/directory
     *                             is missing, unreadable or invalid.
     */
    private function load(string $lang): void
    {
        if ($this->dir === null) {
            throw new TranslatorException(
                'The translation directory has not been set. Call setDir() before loading a language.'
            );
        }

        $path = $this->dir . DIRECTORY_SEPARATOR . $lang
            . ($this->languagesAreDirectory ? DIRECTORY_SEPARATOR : '.php');

        if (!$this->languagesAreDirectory) {
            $this->container[$lang] = $this->requireFile($path);

            return;
        }

        if (!is_dir($path)) {
            throw new TranslatorException('The translation directory was not found: "' . $path . '".');
        }

        $files = glob($path . '*.php');
        if ($files === false) {
            throw new TranslatorException('The translation directory could not be read: "' . $path . '".');
        }

        $this->container[$lang] = [];
        foreach ($files as $file) {
            $namespace = strtolower(basename($file, '.php'));
            $this->container[$lang][$namespace] = $this->requireFile($file);
        }
    }

    /**
     * Requires a PHP file and asserts that it returns an array.
     *
     * @param string $path Absolute path to the language file.
     * @return array<array-key, mixed> The array returned by the file.
     * @throws TranslatorException If the file is missing or does not return an array.
     */
    private function requireFile(string $path): array
    {
        if (!is_file($path)) {
            throw new TranslatorException('The translation file was not found: "' . $path . '".');
        }

        /** @var mixed $data */
        $data = require $path;
        if (!\is_array($data)) {
            throw new TranslatorException('The translation file "' . $path . '" must return an array.');
        }

        return $data;
    }
}

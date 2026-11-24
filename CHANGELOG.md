# Changelog

All notable changes to **initphp/translator** will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Security

- `setDefault()` and `change()` now reject a language identifier that is empty or
  contains anything other than letters, digits, `_` and `-` (checked with `\A…\z`,
  so a trailing newline is rejected too) by throwing `TranslatorException` before
  any file is read. Previously the identifier was joined into the path of a file
  that was `require`d, so a request-supplied value such as `../config/install`
  could run PHP files outside the language directory. The translator's state is
  unchanged when a code is rejected. Codes such as `en`, `en_US` and `pt-BR` keep
  working; a language pack whose name used `.` or `/` must be renamed.

## [2.0.0] - 2026-11-22

### Changed (breaking)

- **Requires PHP `^8.2`.** Support for PHP 8.1 is dropped; CI now runs PHP 8.2, 8.3, 8.4 and 8.5.
- **Coding standard moved to PER-CS** through the shared `.php-cs-fixer.dist.php`; the whole code base was reformatted.

## [1.0.0] - 2026-06-11

First stable release. The public API of the previous `0.2` line — `_r()` /
`_e()` and the fluent setters — is preserved, so existing code keeps working.

### Requirements

- **Requires PHP 8.1+** (was 7.4+).

### Fixed

- **Default-language fallback now works for nested (dot-delimited) keys.** Nested
  lookups were always resolved against the *active* language, so a key present
  only in the default language was never found and the key was returned
  verbatim. Affected both the file and directory layouts.
- **Requesting a key that resolves to a nested array no longer throws a
  `TypeError`.** Such a namespace node is treated as a miss and falls through the
  resolution chain.
- **Inline fallbacks of `'0'` and `''` are honoured.** The provided-fallback
  check used `empty()`, which discarded these values; it now checks for `null`.

### Added

- **`translate()`** and **`render()`** as the primary, descriptive methods
  (returning and echoing, respectively).
- Clear `TranslatorException` messages for misconfiguration: directory not set,
  language file/directory missing, a directory that cannot be read, a file that
  does not return an array, and changing the file/directory mode after a language
  has been loaded.
- English documentation under `docs/`, a rewritten `README.md`, a GitHub Actions
  CI workflow, PHPStan (level max) and PHP-CS-Fixer configuration, and a
  comprehensive test suite.

### Changed

- `_r()` and `_e()` are now thin, **deprecated** aliases of `translate()` and
  `render()`. They remain fully functional for backward compatibility.
- `Translator` is now `final` and implements the documented `TranslatorInterface`.
  Depend on the interface rather than subclassing.
- Calling `useFile()` / `useDirectory()` after a language has been loaded now
  throws a `TranslatorException` instead of silently leaving the translator in an
  inconsistent state.

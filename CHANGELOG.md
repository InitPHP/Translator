# Changelog

All notable changes to **initphp/translator** will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Fixed

- On POSIX, `useDirectory()->setDir()` with a directory whose path contains a
  backslash (for example `…/b\y`) now reads that directory. The backslash was
  treated as a glob escape character, so no language file was found.

## [2.0.1] - 2026-11-25

### Security

- **Affected versions: every release up to and including 2.0.0** (0.1, 0.2, 1.0.0
  and 2.0.0 were checked: none of them validates the language identifier before it
  is joined into a path that is `require`d or passed to `glob()`).
- `setDefault()` and `change()` now reject a language identifier that is empty,
  contains `/`, `\` or a NUL byte, or consists only of dots and spaces (`.`, `..`,
  and the forms Windows normalises to them) by throwing `TranslatorException`
  before any file is read. Previously the identifier was joined into the path of a
  file that was `require`d, so a request-supplied value such as `../config/install`
  could run PHP files outside the language directory. Codes that pointed into a
  sub-directory of the language directory worked in 2.0.0 (`sub/en`, and `sub\en`
  on Windows) and are now rejected, and so is a file name containing `\` on POSIX;
  a language pack must sit directly in the language directory (or be a directory of
  its own in directory mode).
  Every other name keeps working exactly as in 2.0.0 (`en_US`, `pt-BR`,
  `sr@latin`, `zh.Hant`, `türkçe`, names with a space). On Windows a `:` is also
  rejected, as it is not valid in a file name there. The translator's state is unchanged when a code is rejected.
  Rejecting these codes (a sub-directory path, an empty code, or one made only of
  dots and spaces such as `.`) is the incompatible change of this release: for
  example `useDirectory()->setDefault('.')` loaded the files of the language
  directory itself in 2.0.0 and now throws. It ships as a patch release so that
  installations on `^2.0` get the fix.
- In directory mode the language path is matched literally by `glob()`: `*`, `?`
  and `[` in a language code or in the base directory are no longer wildcards, so
  a directory named like `.*` cannot also pull in the files of its parent.

### Fixed

- `setDefault()` and `change()` no longer change the active or default language
  when the language cannot be loaded (missing file or directory, a file that does
  not return an array): the pack is loaded first and the state is assigned after.
  Before, `change('xx')` left `xx` as the active language.
- In directory mode a language is cached only after all of its files have
  loaded. Before, a broken file left a half-loaded language in the cache, and
  the next `change()` to it succeeded silently with the missing namespaces.

### Documentation

- The README, API reference, Exceptions, Quick Start and Recipes describe which
  language codes are rejected (empty, `/`, `\`, NUL, only dots and spaces, and `:`
  on Windows), that a rejected or unloadable code leaves the translator as it was,
  that `*`, `?` and `[` are matched literally in directory mode, and that a
  well-formed but unsupported code still throws, so a language taken from a request
  must be checked against an allow-list first.

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

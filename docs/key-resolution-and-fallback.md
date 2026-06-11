# Key resolution & fallback

`translate()` (and its echoing twin `render()`) resolve a key through a fixed
chain and return the **first** match.

## The resolution order

```php
public function translate(string $key, ?string $fallback = null, array $context = []): string
```

1. **Active language.** The language set by the most recent `setDefault()` or
   `change()`.
2. **Inline fallback.** If you passed a `$fallback` string, it is returned
   (interpolated) when step 1 misses. Passing a fallback short-circuits the
   default-language lookup.
3. **Default language.** The language given to `setDefault()`, consulted only
   when it differs from the active language and no inline fallback was provided.
4. **The key itself.** Returned verbatim when nothing matched.

```php
$lang->setDefault('en')->change('tr');

// 1: present in tr
$lang->translate('errors.e500');             // "Sunucu Hatası"

// 3: missing in tr, no inline fallback -> default (en)
$lang->translate('errors.e404');             // "Not Found"

// 2: inline fallback wins over the default language
$lang->translate('errors.e404', 'Oops');     // "Oops"

// 4: missing everywhere
$lang->translate('nope');                     // "nope"
```

## Nested keys

A key containing `.` is split into segments. The first segment selects the
top-level entry (in [directory mode](usage/directory-mode.md), the file), and
each further segment walks one level deeper into nested arrays.

```php
// en.php => ['errors' => ['http' => ['unauthorized' => 'Unauthorized']]]
$lang->translate('errors.http.unauthorized'); // "Unauthorized"
```

The fallback chain applies to nested keys exactly as it does to flat keys: a
nested key missing from the active language is looked up in the default
language.

## A key that points to an array

If a key resolves to an array (a namespace) instead of a string leaf, it is
treated as a **miss** — the chain continues, and you ultimately get the key
back. It never throws.

```php
// 'errors' is an array, not a translation string
$lang->translate('errors');       // "errors"
$lang->translate('errors.http');  // "errors.http"  (also an array)
```

## Inline fallback details

- The fallback is interpolated with the same `$context` as a real translation:

  ```php
  $lang->translate('greeting', 'Hi {user}', ['user' => 'Ada']); // "Hi Ada"
  ```

- An empty string (`''`) and the string `'0'` count as *provided* fallbacks and
  are returned as-is; only `null` means "no inline fallback".

  ```php
  $lang->translate('missing', '');   // ""  (not the key)
  $lang->translate('missing', null); // "missing"
  ```

## When no language is configured

If you call `translate()` before any language has been loaded, it degrades
gracefully and returns the key (or the inline fallback, if given) rather than
throwing.

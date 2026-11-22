# Placeholders

Translations can contain `{name}` markers that are replaced from the `$context`
map passed to `translate()` / `render()`.

```php
// en.php => ['welcome' => 'Welcome {user}']
$lang->translate('welcome', null, ['user' => 'Ada']); // "Welcome Ada"
```

The context **key** (`user`) matches the marker **name** (`{user}`), without the
braces.

## Which values are interpolated

Each context value is handled according to its type:

| Value type | Result |
| --- | --- |
| `string` | inserted as-is |
| `int`, `float` | cast to string (`2005` → `"2005"`, `3.5` → `"3.5"`) |
| `bool` | cast to string (`true` → `"1"`, `false` → `""`) |
| `null` | inserted as an empty string |
| object with `__toString()` (e.g. `Stringable`) | its string form is inserted |
| `array` | **ignored** — the marker is left untouched |
| object **without** `__toString()` | **ignored** — the marker is left untouched |

```php
$lang->translate('errorMsg', null, ['code' => 2005]);
// "Something went wrong. Code : 2005"
```

### Stringable objects

Any object that implements `__toString()` (including `Stringable`) is
stringified:

```php
$price = new class () implements Stringable {
    public function __toString(): string
    {
        return '€9.99';
    }
};

$lang->translate('total', 'Total: {amount}', ['amount' => $price]);
// "Total: €9.99"
```

## Unmatched markers are left intact

A marker without a matching context entry is left exactly as written — nothing
is removed. This makes missing context easy to spot during development:

```php
$lang->translate('welcome');                        // "Welcome {user}"
$lang->translate('welcome', null, ['other' => 1]);  // "Welcome {user}"
```

## Multiple placeholders

All markers are replaced in a single pass:

```php
// 'order' => 'Order {id} for {user}'
$lang->translate('order', null, [
    'id'   => 42,
    'user' => 'Ada',
]); // "Order 42 for Ada"
```

## Placeholders in inline fallbacks

The inline fallback is interpolated with the same context, so you can template
it too:

```php
$lang->translate('greeting', 'Hi {user}', ['user' => 'Ada']); // "Hi Ada"
```

## Empty context is a no-op

When `$context` is empty, the message is returned untouched (markers and all),
so there is no overhead for translations that take no placeholders:

```php
$lang->translate('welcome'); // "Welcome {user}" — no substitution attempted
```

## Choosing marker names

- Marker names are matched literally between the braces, so `{user}` and
  `{ user }` are different markers — don't add spaces inside the braces.
- Names are case-sensitive: `{User}` ≠ `{user}`.
- Anything not wrapped in `{...}` is literal text and is never touched.

## Where to go next

- [Keys & Fallback](Keys-and-Fallback.md) — how the message itself is resolved.
- [API Reference](API-Reference.md#translate) — the `translate()` signature.
- [Recipes](Recipes.md) — a translation helper function and framework integration.

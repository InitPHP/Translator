# Placeholders

Translations can contain `{name}` markers that are replaced from the `$context`
map passed to `translate()` / `render()`.

```php
// en.php => ['welcome' => 'Welcome {user}']
$lang->translate('welcome', null, ['user' => 'Ada']); // "Welcome Ada"
```

The context key (`user`) matches the marker name (`{user}`), without braces.

## Which values are interpolated

Each context value is handled as follows:

| Value type | Result |
| ---------- | ------ |
| `string` | inserted as-is |
| `int`, `float`, `bool` | cast to string (`true` → `"1"`, `false` → `""`) |
| `null` | inserted as an empty string |
| object with `__toString()` (e.g. `Stringable`) | its string form is inserted |
| `array` | **ignored** — the marker is left untouched |
| object without `__toString()` | **ignored** — the marker is left untouched |

```php
$lang->translate('errorMsg', null, ['code' => 2005]);
// "Something went wrong. Code : 2005"

$price = new class () implements Stringable {
    public function __toString(): string { return '€9.99'; }
};
$lang->translate('total', 'Total: {amount}', ['amount' => $price]);
// "Total: €9.99"
```

## Unmatched markers are left intact

A marker without a matching context entry is left exactly as written — nothing
is removed:

```php
$lang->translate('welcome');                       // "Welcome {user}"
$lang->translate('welcome', null, ['other' => 1]); // "Welcome {user}"
```

This makes missing context easy to spot during development.

## Placeholders in inline fallbacks

The inline fallback is interpolated with the same context, so you can template
it too:

```php
$lang->translate('greeting', 'Hi {user}', ['user' => 'Ada']); // "Hi Ada"
```

## Multiple placeholders

All markers are replaced in a single pass:

```php
// 'Order {id} for {user}'
$lang->translate('order', null, [
    'id'   => 42,
    'user' => 'Ada',
]); // "Order 42 for Ada"
```

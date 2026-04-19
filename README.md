# laravel-settings

Typed, polymorphic key-value settings for Laravel models.

Settings are stored in a dedicated `settings` table with a `type` column so values round-trip as the right PHP type (`boolean`, `integer`, `float`, `string`, `array`, `json`, `datetime`) rather than opaque JSON blobs. The relation is polymorphic, so any model — not just `User` — can have settings.

## Installation

```bash
composer require bhhaskin/laravel-settings
```

Publish and run the migration:

```bash
php artisan vendor:publish --tag=settings-migrations
php artisan migrate
```

Optionally publish the config:

```bash
php artisan vendor:publish --tag=settings-config
```

## Setup

Add the `HasSettings` trait to any model that should own settings:

```php
use Bhhaskin\LaravelSettings\Concerns\HasSettings;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasSettings;
}
```

## Usage

```php
$user->setSetting('theme', 'dark');               // type detected as string
$user->setSetting('notifications.email', true);   // type detected as boolean
$user->setSetting('digest.hour', 8);              // integer
$user->setSetting('tags', ['news', 'tips']);      // array

$user->getSetting('theme');                       // 'dark'
$user->getSetting('notifications.email');         // true
$user->getSetting('missing', 'fallback');         // 'fallback'

$user->hasSetting('theme');                       // true
$user->forgetSetting('theme');
$user->allSettings();                             // ['notifications.email' => true, ...]
```

### Forcing a type

Pass a third argument when you want to override auto-detection:

```php
use Bhhaskin\LaravelSettings\Enums\SettingType;

$user->setSetting('flag', 'yes', SettingType::Boolean);  // stored as true
$user->setSetting('opens_at', '2026-05-01T09:00:00Z', SettingType::Datetime);
```

### Declaring defaults and types

Predeclare keys in `config/settings.php` to skip passing types and get defaults for unset keys:

```php
'definitions' => [
    'theme'                => ['type' => 'string',  'default' => 'system'],
    'notifications.email'  => ['type' => 'boolean', 'default' => true],
    'digest.hour'          => ['type' => 'integer', 'default' => 8],
],
```

Keys outside this list still work — definitions are opt-in sugar, not a schema.

### Runtime-configurable defaults

Config defaults ship with your code. When you also need defaults an admin can
change at runtime, use the `setting_defaults` table. Defaults can be global or
scoped to a specific owner morph class:

```php
use Bhhaskin\LaravelSettings\SettingsManager;
use Bhhaskin\LaravelSettings\Facades\Settings;

// Global default — applies to every model that owns settings
Settings::setDefault('theme', 'sunrise');

// Default scoped to one owner class
Settings::setDefault('theme', 'midnight', null, User::class);

Settings::getDefault('theme');               // 'sunrise'
Settings::getDefault('theme', User::class);  // 'midnight'
Settings::forgetDefault('theme', User::class);
```

`getSetting()` resolves in this order:

1. The owner's own stored value
2. DB default scoped to the owner's morph class
3. Global DB default
4. The `$default` argument
5. Config `definitions[$key]['default']`
6. `null`

Use `allSettingsWithDefaults()` when you need the merged view:

```php
$user->allSettingsWithDefaults();
// ['theme' => 'midnight', 'digest.hour' => 8, ...]
```

`allSettings()` is unchanged — it still returns only values stored for the owner.

### Attaching settings to other models

Because the relation is polymorphic, you can add `HasSettings` to workspaces, teams, or any other model and call the same API. Each model's settings are scoped by `owner_type` / `owner_id`.

## Testing

```bash
composer test
```

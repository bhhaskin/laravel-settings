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

### Attaching settings to other models

Because the relation is polymorphic, you can add `HasSettings` to workspaces, teams, or any other model and call the same API. Each model's settings are scoped by `owner_type` / `owner_id`.

## Testing

```bash
composer test
```

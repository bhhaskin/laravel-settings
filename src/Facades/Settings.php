<?php

declare(strict_types=1);

namespace Bhhaskin\LaravelSettings\Facades;

use Bhhaskin\LaravelSettings\SettingsManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string settingModel()
 * @method static string defaultModel()
 * @method static ?\Bhhaskin\LaravelSettings\Enums\SettingType typeFor(string $key)
 * @method static mixed defaultFor(string $key)
 * @method static ?array definition(string $key)
 * @method static void setDefault(string $key, mixed $value, \Bhhaskin\LaravelSettings\Enums\SettingType|string|null $type = null, ?string $ownerType = null)
 * @method static mixed getDefault(string $key, ?string $ownerType = null)
 * @method static void forgetDefault(string $key, ?string $ownerType = null)
 * @method static mixed lookupDefault(string $key, ?string $ownerType = null)
 * @method static mixed resolveDefault(string $key, ?string $ownerType = null)
 * @method static array defaultsFor(?string $ownerType)
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SettingsManager::class;
    }
}

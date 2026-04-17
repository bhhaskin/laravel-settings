<?php

declare(strict_types=1);

namespace Bhhaskin\LaravelSettings\Facades;

use Bhhaskin\LaravelSettings\SettingsManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string settingModel()
 * @method static ?\Bhhaskin\LaravelSettings\Enums\SettingType typeFor(string $key)
 * @method static mixed defaultFor(string $key)
 * @method static ?array definition(string $key)
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SettingsManager::class;
    }
}

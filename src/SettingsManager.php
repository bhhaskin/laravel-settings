<?php

declare(strict_types=1);

namespace Bhhaskin\LaravelSettings;

use Bhhaskin\LaravelSettings\Enums\SettingType;
use Bhhaskin\LaravelSettings\Models\Setting;

class SettingsManager
{
    /**
     * Resolve the configured Setting model class.
     */
    public static function settingModel(): string
    {
        $model = config('settings.model', Setting::class);

        return is_string($model) ? $model : Setting::class;
    }

    /**
     * Look up the declared type for a key, if defined in config.
     */
    public static function typeFor(string $key): ?SettingType
    {
        $definition = self::definition($key);

        if ($definition === null || ! isset($definition['type'])) {
            return null;
        }

        return SettingType::coerce($definition['type']);
    }

    /**
     * Look up the declared default for a key, if defined in config.
     */
    public static function defaultFor(string $key): mixed
    {
        $definition = self::definition($key);

        return $definition['default'] ?? null;
    }

    /**
     * Pull a declared definition (type + default) for a key.
     */
    public static function definition(string $key): ?array
    {
        $definitions = config('settings.definitions', []);

        return is_array($definitions) && isset($definitions[$key]) && is_array($definitions[$key])
            ? $definitions[$key]
            : null;
    }
}

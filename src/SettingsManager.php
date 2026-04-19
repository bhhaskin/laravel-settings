<?php

declare(strict_types=1);

namespace Bhhaskin\LaravelSettings;

use Bhhaskin\LaravelSettings\Enums\SettingType;
use Bhhaskin\LaravelSettings\Models\Setting;
use Bhhaskin\LaravelSettings\Models\SettingDefault;
use Bhhaskin\LaravelSettings\Support\SettingCaster;

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
     * Resolve the configured SettingDefault model class.
     */
    public static function defaultModel(): string
    {
        $model = config('settings.defaults_model', SettingDefault::class);

        return is_string($model) ? $model : SettingDefault::class;
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

    /**
     * Store a runtime default for a key, optionally scoped to an owner type.
     * Pass $ownerType = null for a global default that applies to every owner.
     */
    public static function setDefault(string $key, mixed $value, SettingType|string|null $type = null, ?string $ownerType = null): void
    {
        $resolved = $type !== null
            ? SettingType::coerce($type)
            : self::typeFor($key) ?? SettingType::detect($value);

        $model = self::defaultModel();

        $model::query()->updateOrCreate(
            ['owner_type' => $ownerType, 'key' => $key],
            [
                'type' => $resolved->value,
                'value' => SettingCaster::serialize($value, $resolved),
            ]
        );
    }

    /**
     * Look up a runtime default for a key. Returns null if none is stored.
     * Does not fall back to config-declared defaults; use resolveDefault() for that.
     */
    public static function getDefault(string $key, ?string $ownerType = null): mixed
    {
        $model = self::defaultModel();

        $record = $model::query()
            ->where('owner_type', $ownerType)
            ->where('key', $key)
            ->first();

        return $record?->castedValue();
    }

    /**
     * Remove a runtime default. With $ownerType = null, only the global default is removed.
     */
    public static function forgetDefault(string $key, ?string $ownerType = null): void
    {
        $model = self::defaultModel();

        $model::query()
            ->where('owner_type', $ownerType)
            ->where('key', $key)
            ->delete();
    }

    /**
     * Look up a DB-stored default for a key: owner-type first, then global.
     * Returns null if neither exists. Does not consult config defaults.
     */
    public static function lookupDefault(string $key, ?string $ownerType = null): mixed
    {
        $model = self::defaultModel();

        if ($ownerType !== null) {
            $record = $model::query()
                ->where('owner_type', $ownerType)
                ->where('key', $key)
                ->first();

            if ($record !== null) {
                return $record->castedValue();
            }
        }

        $record = $model::query()
            ->whereNull('owner_type')
            ->where('key', $key)
            ->first();

        return $record?->castedValue();
    }

    /**
     * Resolve the effective default for a key: DB default then config default.
     */
    public static function resolveDefault(string $key, ?string $ownerType = null): mixed
    {
        return self::lookupDefault($key, $ownerType) ?? self::defaultFor($key);
    }

    /**
     * Return every resolved default (owner-type overrides global) for an owner class as a flat map.
     */
    public static function defaultsFor(?string $ownerType): array
    {
        $model = self::defaultModel();

        $rows = $model::query()
            ->where(function ($q) use ($ownerType) {
                $q->whereNull('owner_type');

                if ($ownerType !== null) {
                    $q->orWhere('owner_type', $ownerType);
                }
            })
            ->get();

        $out = [];

        foreach ($rows as $row) {
            if (! isset($out[$row->key]) || $row->owner_type !== null) {
                $out[$row->key] = $row->castedValue();
            }
        }

        return $out;
    }
}

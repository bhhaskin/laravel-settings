<?php

declare(strict_types=1);

namespace Bhhaskin\LaravelSettings\Concerns;

use Bhhaskin\LaravelSettings\Enums\SettingType;
use Bhhaskin\LaravelSettings\Support\SettingCaster;
use Bhhaskin\LaravelSettings\SettingsManager;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasSettings
{
    public function settings(): MorphMany
    {
        $model = SettingsManager::settingModel();

        return $this->morphMany($model, 'owner');
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        $record = $this->settings()->where('key', $key)->first();

        if ($record === null) {
            return $default ?? SettingsManager::defaultFor($key);
        }

        return $record->castedValue();
    }

    public function setSetting(string $key, mixed $value, SettingType|string|null $type = null): self
    {
        $resolved = $type !== null
            ? SettingType::coerce($type)
            : SettingsManager::typeFor($key) ?? SettingType::detect($value);

        $this->settings()->updateOrCreate(
            ['key' => $key],
            [
                'type' => $resolved->value,
                'value' => SettingCaster::serialize($value, $resolved),
            ]
        );

        return $this;
    }

    public function hasSetting(string $key): bool
    {
        return $this->settings()->where('key', $key)->exists();
    }

    public function forgetSetting(string $key): self
    {
        $this->settings()->where('key', $key)->delete();

        return $this;
    }

    public function allSettings(): array
    {
        return $this->settings->mapWithKeys(fn ($s) => [$s->key => $s->castedValue()])->all();
    }
}

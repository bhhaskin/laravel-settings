<?php

declare(strict_types=1);

namespace Bhhaskin\LaravelSettings\Models;

use Bhhaskin\LaravelSettings\Enums\SettingType;
use Bhhaskin\LaravelSettings\Support\SettingCaster;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property ?string $owner_type
 * @property string $key
 * @property ?string $value
 * @property string $type
 */
class SettingDefault extends Model
{
    protected $fillable = [
        'owner_type',
        'key',
        'value',
        'type',
    ];

    public function getTable()
    {
        return config('settings.defaults_table', parent::getTable() ?: 'setting_defaults');
    }

    public function typeEnum(): SettingType
    {
        return SettingType::coerce($this->type);
    }

    public function castedValue(): mixed
    {
        return SettingCaster::deserialize($this->value, $this->typeEnum());
    }

    public function setTypedValue(mixed $value, SettingType $type): void
    {
        $this->type = $type->value;
        $this->value = SettingCaster::serialize($value, $type);
    }
}

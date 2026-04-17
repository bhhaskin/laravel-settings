<?php

declare(strict_types=1);

namespace Bhhaskin\LaravelSettings\Models;

use Bhhaskin\LaravelSettings\Enums\SettingType;
use Bhhaskin\LaravelSettings\Support\SettingCaster;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $owner_type
 * @property int|string $owner_id
 * @property string $key
 * @property ?string $value
 * @property string $type
 */
class Setting extends Model
{
    protected $fillable = [
        'owner_type',
        'owner_id',
        'key',
        'value',
        'type',
    ];

    public function getTable()
    {
        return config('settings.table', parent::getTable() ?: 'settings');
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
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

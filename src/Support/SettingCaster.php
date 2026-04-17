<?php

declare(strict_types=1);

namespace Bhhaskin\LaravelSettings\Support;

use Bhhaskin\LaravelSettings\Enums\SettingType;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use InvalidArgumentException;

class SettingCaster
{
    /**
     * Serialize a PHP value into a string for storage.
     */
    public static function serialize(mixed $value, SettingType $type): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            SettingType::Boolean => $value ? '1' : '0',
            SettingType::Integer => (string) (int) $value,
            SettingType::Float => (string) (float) $value,
            SettingType::String => (string) $value,
            SettingType::Array, SettingType::Json => self::encodeJson($value),
            SettingType::Datetime => self::encodeDatetime($value),
        };
    }

    /**
     * Restore a stored string back into a PHP value.
     */
    public static function deserialize(?string $value, SettingType $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            SettingType::Boolean => in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true),
            SettingType::Integer => (int) $value,
            SettingType::Float => (float) $value,
            SettingType::String => $value,
            SettingType::Array, SettingType::Json => json_decode($value, true),
            SettingType::Datetime => CarbonImmutable::parse($value),
        };
    }

    private static function encodeJson(mixed $value): string
    {
        $encoded = json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $encoded;
    }

    private static function encodeDatetime(mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::instance($value)->toIso8601String();
        }

        if (is_string($value)) {
            return CarbonImmutable::parse($value)->toIso8601String();
        }

        throw new InvalidArgumentException('Datetime setting values must be a string or DateTimeInterface.');
    }
}

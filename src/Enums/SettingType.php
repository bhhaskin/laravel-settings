<?php

declare(strict_types=1);

namespace Bhhaskin\LaravelSettings\Enums;

enum SettingType: string
{
    case Boolean = 'boolean';
    case Integer = 'integer';
    case Float = 'float';
    case String = 'string';
    case Array = 'array';
    case Json = 'json';
    case Datetime = 'datetime';

    public static function detect(mixed $value): self
    {
        return match (true) {
            is_bool($value) => self::Boolean,
            is_int($value) => self::Integer,
            is_float($value) => self::Float,
            is_array($value) => self::Array,
            $value instanceof \DateTimeInterface => self::Datetime,
            is_object($value) => self::Json,
            default => self::String,
        };
    }

    public static function coerce(self|string|null $type): self
    {
        if ($type instanceof self) {
            return $type;
        }

        if ($type === null || $type === '') {
            return self::String;
        }

        return self::from($type);
    }
}

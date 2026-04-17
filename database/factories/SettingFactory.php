<?php

declare(strict_types=1);

namespace Bhhaskin\LaravelSettings\Database\Factories;

use Bhhaskin\LaravelSettings\Enums\SettingType;
use Bhhaskin\LaravelSettings\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->slug(2),
            'value' => '1',
            'type' => SettingType::Boolean->value,
        ];
    }
}

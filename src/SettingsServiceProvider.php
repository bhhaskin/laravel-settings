<?php

declare(strict_types=1);

namespace Bhhaskin\LaravelSettings;

use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/settings.php', 'settings');

        $this->app->singleton(SettingsManager::class, fn () => new SettingsManager());
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/settings.php' => $this->configPublishPath(),
        ], 'settings-config');

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'settings-migrations');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function configPublishPath(): string
    {
        $configuredPath = config('settings.config_path');

        return is_string($configuredPath) && $configuredPath !== ''
            ? $configuredPath
            : config_path('settings.php');
    }
}

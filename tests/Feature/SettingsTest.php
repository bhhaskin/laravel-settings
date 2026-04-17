<?php

declare(strict_types=1);

use Bhhaskin\LaravelSettings\Enums\SettingType;
use Bhhaskin\LaravelSettings\Models\Setting;
use Bhhaskin\LaravelSettings\Tests\Fixtures\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Hash;

it('stores and retrieves boolean settings', function () {
    $user = createUser();

    $user->setSetting('notifications.email', true);

    expect($user->getSetting('notifications.email'))->toBeTrue();
    expect($user->settings()->first()->type)->toBe('boolean');
});

it('detects type from native PHP values', function () {
    $user = createUser();

    $user->setSetting('theme', 'dark');
    $user->setSetting('max_items', 42);
    $user->setSetting('ratio', 1.5);
    $user->setSetting('tags', ['news', 'tips']);
    $user->setSetting('muted', false);

    expect($user->getSetting('theme'))->toBe('dark');
    expect($user->getSetting('max_items'))->toBe(42);
    expect($user->getSetting('ratio'))->toBe(1.5);
    expect($user->getSetting('tags'))->toBe(['news', 'tips']);
    expect($user->getSetting('muted'))->toBeFalse();
});

it('respects an explicit type override', function () {
    $user = createUser();

    $user->setSetting('flag', 'yes', SettingType::Boolean);

    expect($user->getSetting('flag'))->toBeTrue();
    expect($user->settings()->first()->type)->toBe('boolean');
});

it('returns a passed default when the setting is missing', function () {
    $user = createUser();

    expect($user->getSetting('missing', 'fallback'))->toBe('fallback');
    expect($user->getSetting('missing'))->toBeNull();
});

it('falls back to config-declared defaults and types', function () {
    config()->set('settings.definitions', [
        'theme' => ['type' => 'string', 'default' => 'system'],
        'notifications.push' => ['type' => 'boolean', 'default' => true],
    ]);

    $user = createUser();

    expect($user->getSetting('theme'))->toBe('system');
    expect($user->getSetting('notifications.push'))->toBeTrue();

    $user->setSetting('notifications.push', 0);

    expect($user->getSetting('notifications.push'))->toBeFalse();
    expect($user->settings()->where('key', 'notifications.push')->first()->type)->toBe('boolean');
});

it('updates existing settings instead of duplicating', function () {
    $user = createUser();

    $user->setSetting('theme', 'light');
    $user->setSetting('theme', 'dark');

    expect($user->settings()->count())->toBe(1);
    expect($user->getSetting('theme'))->toBe('dark');
});

it('forgets settings and reports presence accurately', function () {
    $user = createUser();

    $user->setSetting('theme', 'dark');

    expect($user->hasSetting('theme'))->toBeTrue();

    $user->forgetSetting('theme');

    expect($user->hasSetting('theme'))->toBeFalse();
    expect(Setting::count())->toBe(0);
});

it('returns all settings as a flat map', function () {
    $user = createUser();

    $user->setSetting('theme', 'dark');
    $user->setSetting('digest', false);

    expect($user->allSettings())->toEqualCanonicalizing([
        'theme' => 'dark',
        'digest' => false,
    ]);
});

it('round-trips datetime values', function () {
    $user = createUser();
    $when = CarbonImmutable::parse('2026-04-17T10:00:00Z');

    $user->setSetting('dnd_until', $when, SettingType::Datetime);

    $recalled = $user->getSetting('dnd_until');

    expect($recalled)->toBeInstanceOf(CarbonImmutable::class);
    expect($recalled->equalTo($when))->toBeTrue();
});

it('scopes settings per model instance via polymorphic relation', function () {
    $alice = createUser(['email' => 'alice@example.com']);
    $bob = createUser(['email' => 'bob@example.com']);

    $alice->setSetting('theme', 'dark');
    $bob->setSetting('theme', 'light');

    expect($alice->getSetting('theme'))->toBe('dark');
    expect($bob->getSetting('theme'))->toBe('light');
    expect(Setting::count())->toBe(2);
});

function createUser(array $attributes = []): User
{
    static $increment = 1;

    $defaults = [
        'name' => sprintf('User %d', $increment),
        'email' => $attributes['email'] ?? sprintf('user-%d@example.com', $increment),
        'password' => Hash::make('password'),
    ];

    $increment++;

    return User::create(array_merge($defaults, $attributes));
}

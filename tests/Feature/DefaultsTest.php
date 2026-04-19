<?php

declare(strict_types=1);

use Bhhaskin\LaravelSettings\Models\SettingDefault;
use Bhhaskin\LaravelSettings\SettingsManager;
use Bhhaskin\LaravelSettings\Tests\Fixtures\User;
use Illuminate\Support\Facades\Hash;

it('returns an owner-type DB default when no setting is stored', function () {
    SettingsManager::setDefault('theme', 'midnight', null, User::class);

    $user = makeUser();

    expect($user->getSetting('theme'))->toBe('midnight');
});

it('returns a global DB default when no owner-type default exists', function () {
    SettingsManager::setDefault('theme', 'sunrise');

    $user = makeUser();

    expect($user->getSetting('theme'))->toBe('sunrise');
});

it('prefers owner-type default over global default', function () {
    SettingsManager::setDefault('theme', 'sunrise');
    SettingsManager::setDefault('theme', 'midnight', null, User::class);

    $user = makeUser();

    expect($user->getSetting('theme'))->toBe('midnight');
});

it('prefers a stored owner value over any default', function () {
    SettingsManager::setDefault('theme', 'midnight', null, User::class);

    $user = makeUser();
    $user->setSetting('theme', 'custom');

    expect($user->getSetting('theme'))->toBe('custom');
});

it('prefers DB default over the $default argument', function () {
    SettingsManager::setDefault('theme', 'midnight');

    $user = makeUser();

    expect($user->getSetting('theme', 'fallback'))->toBe('midnight');
});

it('prefers $default argument over config default', function () {
    config()->set('settings.definitions', [
        'theme' => ['type' => 'string', 'default' => 'system'],
    ]);

    $user = makeUser();

    expect($user->getSetting('theme', 'explicit'))->toBe('explicit');
});

it('prefers DB default over config default', function () {
    config()->set('settings.definitions', [
        'theme' => ['type' => 'string', 'default' => 'system'],
    ]);

    SettingsManager::setDefault('theme', 'db-wins');

    $user = makeUser();

    expect($user->getSetting('theme'))->toBe('db-wins');
});

it('round-trips typed defaults', function () {
    SettingsManager::setDefault('digest.hour', 9);
    SettingsManager::setDefault('notifications.push', true);
    SettingsManager::setDefault('tags', ['news', 'tips']);

    $user = makeUser();

    expect($user->getSetting('digest.hour'))->toBe(9);
    expect($user->getSetting('notifications.push'))->toBeTrue();
    expect($user->getSetting('tags'))->toBe(['news', 'tips']);
});

it('updates existing defaults instead of duplicating', function () {
    SettingsManager::setDefault('theme', 'light');
    SettingsManager::setDefault('theme', 'dark');

    expect(SettingDefault::count())->toBe(1);
    expect(SettingsManager::getDefault('theme'))->toBe('dark');
});

it('scopes updates by owner_type so global and owner-type defaults coexist', function () {
    SettingsManager::setDefault('theme', 'global');
    SettingsManager::setDefault('theme', 'per-user', null, User::class);

    expect(SettingDefault::count())->toBe(2);
    expect(SettingsManager::getDefault('theme'))->toBe('global');
    expect(SettingsManager::getDefault('theme', User::class))->toBe('per-user');
});

it('forgets defaults without touching other scopes', function () {
    SettingsManager::setDefault('theme', 'global');
    SettingsManager::setDefault('theme', 'per-user', null, User::class);

    SettingsManager::forgetDefault('theme', User::class);

    expect(SettingsManager::getDefault('theme'))->toBe('global');
    expect(SettingsManager::getDefault('theme', User::class))->toBeNull();
});

it('merges defaults and owner values in allSettingsWithDefaults', function () {
    SettingsManager::setDefault('theme', 'sunrise');
    SettingsManager::setDefault('digest.hour', 8);
    SettingsManager::setDefault('notifications.push', false, null, User::class);

    $user = makeUser();
    $user->setSetting('theme', 'custom');

    expect($user->allSettingsWithDefaults())->toEqualCanonicalizing([
        'theme' => 'custom',
        'digest.hour' => 8,
        'notifications.push' => false,
    ]);
});

it('leaves allSettings unaffected by defaults', function () {
    SettingsManager::setDefault('theme', 'sunrise');

    $user = makeUser();
    $user->setSetting('digest', true);

    expect($user->allSettings())->toBe(['digest' => true]);
});

function makeUser(array $attributes = []): User
{
    static $increment = 1;

    $defaults = [
        'name' => sprintf('User %d', $increment),
        'email' => $attributes['email'] ?? sprintf('defaults-user-%d@example.com', $increment),
        'password' => Hash::make('password'),
    ];

    $increment++;

    return User::create(array_merge($defaults, $attributes));
}

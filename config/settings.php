<?php

declare(strict_types=1);

use Bhhaskin\LaravelSettings\Models\Setting;

return [
    /*
    |--------------------------------------------------------------------------
    | Setting Model
    |--------------------------------------------------------------------------
    |
    | The Eloquent model used to persist individual settings. You may replace
    | it with a custom implementation as long as it extends the packaged
    | Setting model.
    |
    */
    'model' => Setting::class,

    /*
    |--------------------------------------------------------------------------
    | Table Name
    |--------------------------------------------------------------------------
    |
    | The database table used to store settings. The default is suitable for
    | most applications, but can be changed if it conflicts with existing
    | tables in your schema.
    |
    */
    'table' => 'settings',

    /*
    |--------------------------------------------------------------------------
    | Setting Definitions
    |--------------------------------------------------------------------------
    |
    | Optionally predeclare known settings so the package can infer a type
    | and default without the caller passing them every time. Unknown keys
    | are always permitted - definitions are opt-in sugar, not a schema.
    |
    | Each entry may provide:
    |   - 'type':    one of SettingType values (boolean, integer, float,
    |                string, array, json, datetime)
    |   - 'default': the value returned by getSetting() when the key is
    |                not stored for the current model.
    |
    |   'theme' => ['type' => 'string', 'default' => 'system'],
    |   'notifications.email' => ['type' => 'boolean', 'default' => true],
    |
    */
    'definitions' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Config Publish Path
    |--------------------------------------------------------------------------
    |
    | Where the published config file lands in the host application. Leave
    | null to use the default config_path() location.
    |
    */
    'config_path' => null,
];

<?php

declare(strict_types=1);

namespace Bhhaskin\LaravelSettings\Tests\Fixtures;

use Bhhaskin\LaravelSettings\Concerns\HasSettings;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasSettings;
    use Notifiable;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}

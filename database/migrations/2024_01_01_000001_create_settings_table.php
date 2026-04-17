<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Config::get('settings.table', 'settings'), function (Blueprint $table) {
            $table->id();
            $table->morphs('owner');
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('type', 32)->default('string');
            $table->timestamps();

            $table->unique(['owner_type', 'owner_id', 'key'], 'settings_owner_key_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Config::get('settings.table', 'settings'));
    }
};

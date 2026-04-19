<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Config::get('settings.defaults_table', 'setting_defaults'), function (Blueprint $table) {
            $table->id();
            $table->string('owner_type')->nullable();
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('type', 32)->default('string');
            $table->timestamps();

            $table->unique(['owner_type', 'key'], 'setting_defaults_owner_key_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Config::get('settings.defaults_table', 'setting_defaults'));
    }
};

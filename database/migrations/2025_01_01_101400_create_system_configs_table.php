<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Runtime system configuration store. Allows operators to change platform
 * settings (surge multipliers, service areas, feature flags) without deployments.
 * Values are jsonb to support any data type (string, number, array, object).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique(); // e.g. 'pricing.surge_enabled', 'dispatch.radius_km'
            $table->jsonb('value');          // flexible: "true", 5.5, {"zones": [...]}
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_configs');
    }
};

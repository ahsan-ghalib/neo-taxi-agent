<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vehicles owned/operated by drivers. A driver may have multiple vehicles
 * but only one active vehicle is used per booking.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('driver_id')->constrained('drivers')->cascadeOnDelete();
            $table->string('vehicle_type'); // App\Enums\VehicleType
            $table->string('make');
            $table->string('model');
            $table->unsignedSmallInteger('year');
            $table->string('color');
            $table->string('plate_number')->unique();
            $table->unsignedTinyInteger('capacity');
            $table->string('status')->default('active'); // App\Enums\VehicleStatus
            $table->timestamps();

            $table->index('driver_id');
            $table->index('vehicle_type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};

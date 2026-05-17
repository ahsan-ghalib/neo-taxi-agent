<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Time-series GPS breadcrumb trail for drivers.
 * High write volume — designed for append-only inserts from driver app heartbeats.
 *
 * Architecture note: For production scale, consider partitioning this table by
 * recorded_at (monthly) using PostgreSQL declarative partitioning, or offloading
 * to TimescaleDB. The composite index on (driver_id, recorded_at DESC) supports
 * the most common query: "latest N locations for driver X".
 *
 * PostGIS improvement: Replace lat/lng with a geography(Point, 4326) column
 * to enable ST_DWithin proximity queries for dispatch.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_locations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('driver_id')->constrained('drivers')->cascadeOnDelete();

            // Nullable: location pings outside of a booking are still recorded
            $table->foreignUuid('booking_id')->nullable()->constrained('ride_bookings')->nullOnDelete();

            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);

            // Heading in degrees (0-360), speed in km/h
            $table->decimal('heading', 5, 2)->nullable();
            $table->decimal('speed', 6, 2)->nullable();

            // Driver app status at time of ping (e.g. 'online', 'busy')
            $table->string('status')->nullable();

            $table->timestamp('recorded_at');
            $table->timestamps();

            // Primary query pattern: latest locations for a driver
            $table->index(['driver_id', 'recorded_at']);
            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_locations');
    }
};

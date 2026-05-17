<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Driver profile table. Stores licensing, real-time availability, and aggregate stats.
 * GPS coordinates use decimal(10,7) for ~1cm precision — sufficient for taxi dispatch.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('license_number')->unique();
            $table->date('license_expiry');
            $table->string('onboarding_status')->default('pending')->comment(''); // App\Enums\OnboardingStatus
            $table->string('availability_status')->default('offline'); // App\Enums\AvailabilityStatus
            $table->decimal('current_lat', 10, 7)->nullable();
            $table->decimal('current_lng', 10, 7)->nullable();

            $table->decimal('rating', 3, 2)->default(5.00);
            $table->unsignedBigInteger('total_rides')->default(0);
            $table->boolean('is_online')->default(false);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->index('onboarding_status');
            $table->index('availability_status');
            $table->index('is_online');

            // Composite index for nearby-driver queries (lat/lng range scans)
            $table->index(['current_lat', 'current_lng']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};

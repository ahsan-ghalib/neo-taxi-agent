<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Core booking table — the central entity of the platform.
 * Soft-deleted to preserve audit trail for disputes and analytics.
 *
 * Composite indexes are designed for the two most common query patterns:
 *   1. Customer history: customer_id + status
 *   2. Driver dispatch queue: driver_id + status
 *   3. Scheduler: status + scheduled_at (for upcoming rides)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ride_bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('booking_code')->unique();

            $table->foreignUuid('quote_id')->constrained('ride_quotes')->restrictOnDelete();
            $table->foreignUuid('customer_id')->constrained('users')->restrictOnDelete();

            // Nullable until a driver accepts the booking
            $table->foreignUuid('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->foreignUuid('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();

            $table->string('booking_source'); // App\Enums\BookingSource
            $table->string('status')->default('pending'); // App\Enums\BookingStatus

            // Denormalized from quote for immutable booking record
            $table->string('pickup_address');
            $table->decimal('pickup_lat', 10, 7);
            $table->decimal('pickup_lng', 10, 7);
            $table->string('destination_address');
            $table->decimal('destination_lat', 10, 7);
            $table->decimal('destination_lng', 10, 7);

            $table->timestamp('scheduled_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();

            $table->unsignedTinyInteger('passengers');
            $table->string('vehicle_type'); // App\Enums\VehicleType

            $table->decimal('estimated_price', 10, 2);
            $table->decimal('final_price', 10, 2)->nullable(); // set on completion
            $table->string('currency', 3)->default('EUR');

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Composite indexes for the most frequent query patterns
            $table->index(['customer_id', 'status']);
            $table->index(['driver_id', 'status']);
            $table->index(['status', 'scheduled_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ride_bookings');
    }
};

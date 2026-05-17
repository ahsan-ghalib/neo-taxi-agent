<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Immutable audit log of every booking status transition.
 * Critical for dispute resolution, SLA tracking, and analytics.
 * No soft deletes — this is an append-only audit trail.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_status_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id')->constrained('ride_bookings')->cascadeOnDelete();

            $table->string('old_status')->nullable(); // null on initial creation
            $table->string('new_status');

            // Who triggered the change: system (null), customer, driver, operator, admin
            $table->foreignUuid('changed_by')->nullable()->constrained('users')->nullOnDelete();

            // jsonb: additional context (e.g. cancellation reason, driver location at time of change)
            $table->jsonb('metadata')->nullable();

            $table->timestamps();

            $table->index('booking_id');
            $table->index(['booking_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_status_logs');
    }
};

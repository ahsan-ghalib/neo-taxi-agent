<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Customer support tickets. Optionally linked to a booking for context.
 * No soft deletes — tickets are legal/compliance records and must be retained.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Nullable: tickets can be raised without a specific booking
            $table->foreignUuid('booking_id')->nullable()->constrained('ride_bookings')->nullOnDelete();
            $table->foreignUuid('customer_id')->constrained('users')->restrictOnDelete();

            $table->string('category'); // e.g. 'payment', 'driver_behaviour', 'lost_item'
            $table->string('priority')->default('medium'); // App\Enums\TicketPriority
            $table->string('status')->default('open');     // App\Enums\TicketStatus
            $table->text('description');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('customer_id');
            $table->index('status');
            $table->index('priority');
            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payment records linked to bookings. One booking can have multiple payment
 * attempts (e.g. failed card → retry with different card).
 * The payment_intent_id is the provider's external reference for reconciliation.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id')->constrained('ride_bookings')->restrictOnDelete();
            $table->foreignUuid('customer_id')->constrained('users')->restrictOnDelete();

            $table->string('provider'); // App\Enums\PaymentProvider
            $table->string('payment_mode'); // App\Enums\PaymentMode

            // Provider's payment intent / transaction reference
            $table->string('payment_intent_id')->nullable()->index();

            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('status')->default('pending'); // App\Enums\PaymentStatus

            $table->timestamp('paid_at')->nullable();

            // jsonb: raw provider response, card details, 3DS data, etc.
            $table->jsonb('metadata')->nullable();

            $table->timestamps();

            $table->index('booking_id');
            $table->index('customer_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

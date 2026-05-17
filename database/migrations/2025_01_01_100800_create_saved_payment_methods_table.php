<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tokenized payment methods stored per customer.
 * We never store raw card numbers — only provider tokens and display metadata.
 * The provider_payment_method_id is the vault token from Stripe/Adyen/Checkout.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_payment_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained('users')->cascadeOnDelete();

            $table->string('provider'); // App\Enums\PaymentProvider
            $table->string('provider_payment_method_id'); // vault token

            // Display metadata — safe to store, no sensitive data
            $table->string('card_brand', 32)->nullable();  // visa, mastercard, amex
            $table->string('last4', 4)->nullable();
            $table->unsignedTinyInteger('expiry_month')->nullable();
            $table->unsignedSmallInteger('expiry_year')->nullable();

            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('customer_id');
            $table->unique(['customer_id', 'provider_payment_method_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_payment_methods');
    }
};

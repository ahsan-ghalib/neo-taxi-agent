<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Customer-specific profile data. Separated from users to keep the users table
 * lean and allow independent scaling of customer features (loyalty, preferences, etc.).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->uuid('default_payment_method_id')->nullable()->index();
            $table->unsignedBigInteger('loyalty_points')->default(0);
            $table->uuid('corporate_account_id')->nullable()->index();
            $table->jsonb('emergency_contact')->nullable();

            // jsonb: { preferred_vehicle_type, language, notifications, ... }
            $table->jsonb('preferences')->nullable();

            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_profiles');
    }
};

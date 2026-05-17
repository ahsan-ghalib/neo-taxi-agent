<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the FK constraint for default_payment_method_id on customer_profiles.
 * This is a separate migration because saved_payment_methods must exist first
 * (circular dependency: customer_profiles → saved_payment_methods → users → customer_profiles).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_profiles', function (Blueprint $table) {
            $table->foreign('default_payment_method_id')
                ->references('id')
                ->on('saved_payment_methods')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customer_profiles', function (Blueprint $table) {
            $table->dropForeign(['default_payment_method_id']);
        });
    }
};

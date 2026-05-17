<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Custom notifications table (not Laravel's built-in notifications table).
 * Tracks multi-channel delivery (push, SMS, email, WhatsApp) with read receipts.
 * sent_at is null until the message is confirmed dispatched by the provider.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type');    // e.g. 'booking_confirmed', 'driver_arriving'
            $table->string('channel'); // App\Enums\NotificationChannel
            $table->string('title');
            $table->text('body');

            // jsonb: deep-link data, booking_id, driver info, etc.
            $table->jsonb('payload')->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index(['user_id', 'read_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

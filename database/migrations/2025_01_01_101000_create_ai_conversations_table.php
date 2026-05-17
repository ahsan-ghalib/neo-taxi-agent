<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AI conversation sessions. Each session maps to a ChatGPT (or other provider)
 * conversation thread. session_id is the external provider's thread/session ID.
 * Nullable customer_id supports anonymous pre-auth conversations.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('session_id')->unique(); // provider's thread/session ID
            $table->string('provider');             // e.g. 'openai', 'anthropic'
            $table->string('model');                // e.g. 'gpt-4o', 'claude-3-5-sonnet'
            $table->string('status')->default('active'); // App\Enums\ConversationStatus
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index('customer_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_conversations');
    }
};

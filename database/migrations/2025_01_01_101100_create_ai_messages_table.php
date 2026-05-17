<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Individual messages within an AI conversation.
 * Stores the full OpenAI-compatible message format including tool calls.
 * token_usage and latency_ms enable cost tracking and performance monitoring.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained('ai_conversations')->cascadeOnDelete();

            $table->string('role'); // App\Enums\MessageRole: user | assistant | tool
            $table->text('content');

            // Populated when role = 'tool'
            $table->string('tool_name')->nullable();
            $table->jsonb('tool_payload')->nullable(); // tool call arguments + result

            // Populated on assistant messages
            $table->unsignedInteger('token_usage')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();

            $table->timestamps();

            $table->index('conversation_id');
            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_messages');
    }
};

<?php

namespace App\Models;

use App\Enums\MessageRole;
use Database\Factories\AiMessageFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMessage extends Model
{
    /** @use HasFactory<AiMessageFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'tool_name',
        'tool_payload',
        'token_usage',
        'latency_ms',
    ];

    protected function casts(): array
    {
        return [
            'role' => MessageRole::class,
            'tool_payload' => 'array',
            'token_usage' => 'integer',
            'latency_ms' => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** @param \Illuminate\Database\Eloquent\Builder<AiMessage> $query */
    public function scopeByRole($query, MessageRole $role): void
    {
        $query->where('role', $role);
    }

    /** @param \Illuminate\Database\Eloquent\Builder<AiMessage> $query */
    public function scopeToolCalls($query): void
    {
        $query->where('role', MessageRole::Tool)->whereNotNull('tool_name');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isToolCall(): bool
    {
        return $this->role === MessageRole::Tool && $this->tool_name !== null;
    }
}

<?php

namespace App\Models;

use App\Enums\ConversationStatus;
use Database\Factories\AiConversationFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiConversation extends Model
{
    /** @use HasFactory<AiConversationFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'customer_id',
        'session_id',
        'provider',
        'model',
        'status',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ConversationStatus::class,
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /** @return HasMany<AiMessage> */
    public function messages(): HasMany
    {
        return $this->hasMany(AiMessage::class, 'conversation_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** @param \Illuminate\Database\Eloquent\Builder<AiConversation> $query */
    public function scopeActive($query): void
    {
        $query->where('status', ConversationStatus::Active);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status === ConversationStatus::Active;
    }

    public function totalTokensUsed(): int
    {
        return $this->messages()
            ->whereNotNull('token_usage')
            ->sum('token_usage');
    }
}

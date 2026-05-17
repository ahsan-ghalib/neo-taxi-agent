<?php

namespace App\Models;

use App\Enums\PaymentProvider;
use Database\Factories\SavedPaymentMethodFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedPaymentMethod extends Model
{
    /** @use HasFactory<SavedPaymentMethodFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'customer_id',
        'provider',
        'provider_payment_method_id',
        'card_brand',
        'last4',
        'expiry_month',
        'expiry_year',
        'is_default',
    ];

    protected $hidden = [
        'provider_payment_method_id', // vault token — never expose in API responses
    ];

    protected function casts(): array
    {
        return [
            'provider' => PaymentProvider::class,
            'expiry_month' => 'integer',
            'expiry_year' => 'integer',
            'is_default' => 'boolean',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** @param \Illuminate\Database\Eloquent\Builder<SavedPaymentMethod> $query */
    public function scopeDefault($query): void
    {
        $query->where('is_default', true);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isExpired(): bool
    {
        $now = now();

        return $this->expiry_year < $now->year
            || ($this->expiry_year === $now->year && $this->expiry_month < $now->month);
    }

    public function getDisplayLabelAttribute(): string
    {
        return strtoupper((string) $this->card_brand).' •••• '.$this->last4;
    }
}

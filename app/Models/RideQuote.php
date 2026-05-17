<?php

namespace App\Models;

use App\Enums\VehicleType;
use Database\Factories\RideQuoteFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RideQuote extends Model
{
    /** @use HasFactory<RideQuoteFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'quote_code',
        'customer_id',
        'pickup_address',
        'pickup_lat',
        'pickup_lng',
        'destination_address',
        'destination_lat',
        'destination_lng',
        'pickup_time',
        'estimated_distance_km',
        'estimated_duration_minutes',
        'estimated_price',
        'currency',
        'surge_multiplier',
        'passengers',
        'vehicle_type',
        'payment_methods',
        'serviceable',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'vehicle_type' => VehicleType::class,
            'pickup_time' => 'datetime',
            'expires_at' => 'datetime',
            'estimated_distance_km' => 'decimal:2',
            'estimated_duration_minutes' => 'integer',
            'estimated_price' => 'decimal:2',
            'surge_multiplier' => 'decimal:2',
            'passengers' => 'integer',
            'payment_methods' => 'array',
            'serviceable' => 'boolean',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function booking(): HasOne
    {
        return $this->hasOne(RideBooking::class, 'quote_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** @param \Illuminate\Database\Eloquent\Builder<RideQuote> $query */
    public function scopeValid($query): void
    {
        $query->where('expires_at', '>', now())
            ->where('serviceable', true);
    }

    /** @param \Illuminate\Database\Eloquent\Builder<RideQuote> $query */
    public function scopeExpired($query): void
    {
        $query->where('expires_at', '<=', now());
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function hasBeenBooked(): bool
    {
        return $this->booking()->exists();
    }
}

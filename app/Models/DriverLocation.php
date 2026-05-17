<?php

namespace App\Models;

use Database\Factories\DriverLocationFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverLocation extends Model
{
    /** @use HasFactory<DriverLocationFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'driver_id',
        'booking_id',
        'lat',
        'lng',
        'heading',
        'speed',
        'status',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'heading' => 'decimal:2',
            'speed' => 'decimal:2',
            'recorded_at' => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(RideBooking::class, 'booking_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** @param \Illuminate\Database\Eloquent\Builder<DriverLocation> $query */
    public function scopeLatestFirst($query): void
    {
        $query->orderByDesc('recorded_at');
    }

    /** @param \Illuminate\Database\Eloquent\Builder<DriverLocation> $query */
    public function scopeForBooking($query, string $bookingId): void
    {
        $query->where('booking_id', $bookingId);
    }
}

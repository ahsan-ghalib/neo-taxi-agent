<?php

namespace App\Models;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\VehicleType;
use Database\Factories\RideBookingFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RideBooking extends Model
{
    /** @use HasFactory<RideBookingFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'booking_code',
        'quote_id',
        'customer_id',
        'driver_id',
        'vehicle_id',
        'booking_source',
        'status',
        'pickup_address',
        'pickup_lat',
        'pickup_lng',
        'destination_address',
        'destination_lat',
        'destination_lng',
        'scheduled_at',
        'started_at',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
        'passengers',
        'vehicle_type',
        'estimated_price',
        'final_price',
        'currency',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'booking_source' => BookingSource::class,
            'status' => BookingStatus::class,
            'vehicle_type' => VehicleType::class,
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'estimated_price' => 'decimal:2',
            'final_price' => 'decimal:2',
            'passengers' => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function quote(): BelongsTo
    {
        return $this->belongsTo(RideQuote::class, 'quote_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** @return HasMany<BookingStatusLog> */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(BookingStatusLog::class, 'booking_id');
    }

    /** @return HasMany<Payment> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'booking_id');
    }

    /** @return HasMany<DriverLocation> */
    public function driverLocations(): HasMany
    {
        return $this->hasMany(DriverLocation::class, 'booking_id');
    }

    /** @return HasMany<SupportTicket> */
    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'booking_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** @param \Illuminate\Database\Eloquent\Builder<RideBooking> $query */
    public function scopeActive($query): void
    {
        $query->whereIn('status', [
            BookingStatus::Confirmed,
            BookingStatus::DriverSearching,
            BookingStatus::DriverAssigned,
            BookingStatus::DriverArriving,
            BookingStatus::DriverArrived,
            BookingStatus::TripStarted,
        ]);
    }

    /** @param \Illuminate\Database\Eloquent\Builder<RideBooking> $query */
    public function scopePending($query): void
    {
        $query->where('status', BookingStatus::Pending);
    }

    /** @param \Illuminate\Database\Eloquent\Builder<RideBooking> $query */
    public function scopeCompleted($query): void
    {
        $query->where('status', BookingStatus::Completed);
    }

    /** @param \Illuminate\Database\Eloquent\Builder<RideBooking> $query */
    public function scopeForCustomer($query, string $customerId): void
    {
        $query->where('customer_id', $customerId);
    }

    /** @param \Illuminate\Database\Eloquent\Builder<RideBooking> $query */
    public function scopeForDriver($query, string $driverId): void
    {
        $query->where('driver_id', $driverId);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isCompleted(): bool
    {
        return $this->status === BookingStatus::Completed;
    }

    public function isCancelled(): bool
    {
        return $this->status === BookingStatus::Cancelled;
    }

    public function hasDriver(): bool
    {
        return $this->driver_id !== null;
    }
}

<?php

namespace App\Models;

use App\Enums\AvailabilityStatus;
use App\Enums\OnboardingStatus;
use Database\Factories\DriverFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    /** @use HasFactory<DriverFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'license_number',
        'license_expiry',
        'onboarding_status',
        'availability_status',
        'current_lat',
        'current_lng',
        'rating',
        'total_rides',
        'is_online',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'onboarding_status' => OnboardingStatus::class,
            'availability_status' => AvailabilityStatus::class,
            'license_expiry' => 'date',
            'current_lat' => 'decimal:7',
            'current_lng' => 'decimal:7',
            'rating' => 'decimal:2',
            'total_rides' => 'integer',
            'is_online' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<Vehicle> */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    /** @return HasMany<RideBooking> */
    public function bookings(): HasMany
    {
        return $this->hasMany(RideBooking::class);
    }

    /** @return HasMany<DriverLocation> */
    public function locations(): HasMany
    {
        return $this->hasMany(DriverLocation::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** @param \Illuminate\Database\Eloquent\Builder<Driver> $query */
    public function scopeOnline($query): void
    {
        $query->where('is_online', true)
            ->where('availability_status', AvailabilityStatus::Online);
    }

    /** @param \Illuminate\Database\Eloquent\Builder<Driver> $query */
    public function scopeApproved($query): void
    {
        $query->where('onboarding_status', OnboardingStatus::Approved);
    }

    /** @param \Illuminate\Database\Eloquent\Builder<Driver> $query */
    public function scopeAvailable($query): void
    {
        $query->where('is_online', true)
            ->where('availability_status', AvailabilityStatus::Online)
            ->where('onboarding_status', OnboardingStatus::Approved);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isAvailable(): bool
    {
        return $this->is_online
            && $this->availability_status === AvailabilityStatus::Online
            && $this->onboarding_status === OnboardingStatus::Approved;
    }

    public function isLicenseExpired(): bool
    {
        return $this->license_expiry->isPast();
    }
}

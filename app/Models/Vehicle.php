<?php

namespace App\Models;

use App\Enums\VehicleStatus;
use App\Enums\VehicleType;
use Database\Factories\VehicleFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    /** @use HasFactory<VehicleFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'driver_id',
        'vehicle_type',
        'make',
        'model',
        'year',
        'color',
        'plate_number',
        'capacity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'vehicle_type' => VehicleType::class,
            'status' => VehicleStatus::class,
            'year' => 'integer',
            'capacity' => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** @param \Illuminate\Database\Eloquent\Builder<Vehicle> $query */
    public function scopeActive($query): void
    {
        $query->where('status', VehicleStatus::Active);
    }

    /** @param \Illuminate\Database\Eloquent\Builder<Vehicle> $query */
    public function scopeOfType($query, VehicleType $type): void
    {
        $query->where('vehicle_type', $type);
    }
}

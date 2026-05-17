<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case DriverSearching = 'driver_searching';
    case DriverAssigned = 'driver_assigned';
    case DriverArriving = 'driver_arriving';
    case DriverArrived = 'driver_arrived';
    case TripStarted = 'trip_started';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    /** Statuses that represent an active/in-progress ride */
    public function isActive(): bool
    {
        return in_array($this, [
            self::Confirmed,
            self::DriverSearching,
            self::DriverAssigned,
            self::DriverArriving,
            self::DriverArrived,
            self::TripStarted,
        ]);
    }

    /** Statuses that represent a terminal (final) state */
    public function isTerminal(): bool
    {
        return in_array($this, [
            self::Completed,
            self::Cancelled,
            self::Expired,
        ]);
    }
}

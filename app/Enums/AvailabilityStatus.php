<?php

namespace App\Enums;

enum AvailabilityStatus: string
{
    case Offline = 'offline';
    case Online = 'online';
    case Busy = 'busy';
}

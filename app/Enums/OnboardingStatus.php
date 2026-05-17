<?php

namespace App\Enums;

enum OnboardingStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}

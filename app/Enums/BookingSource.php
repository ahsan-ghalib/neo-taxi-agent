<?php

namespace App\Enums;

enum BookingSource: string
{
    case ChatGpt = 'chatgpt';
    case MobileApp = 'mobile_app';
    case Web = 'web';
    case Operator = 'operator';
}

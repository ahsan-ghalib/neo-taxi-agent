<?php

namespace App\Enums;

enum PaymentProvider: string
{
    case Stripe = 'stripe';
    case Adyen = 'adyen';
    case Checkout = 'checkout';
}

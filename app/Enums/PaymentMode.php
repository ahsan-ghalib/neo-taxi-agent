<?php

namespace App\Enums;

enum PaymentMode: string
{
    case SavedCard = 'saved_card';
    case NewCard = 'new_card';
    case Cash = 'cash';
    case CorporateInvoice = 'corporate_invoice';
}

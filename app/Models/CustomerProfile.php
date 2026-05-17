<?php

namespace App\Models;

use Database\Factories\CustomerProfileFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerProfile extends Model
{
    /** @use HasFactory<CustomerProfileFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'default_payment_method_id',
        'loyalty_points',
        'corporate_account_id',
        'emergency_contact',
        'preferences',
    ];

    protected function casts(): array
    {
        return [
            'loyalty_points' => 'integer',
            'emergency_contact' => 'array',
            'preferences' => 'array',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function defaultPaymentMethod(): BelongsTo
    {
        return $this->belongsTo(SavedPaymentMethod::class, 'default_payment_method_id');
    }
}

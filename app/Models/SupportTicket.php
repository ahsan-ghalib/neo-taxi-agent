<?php

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Database\Factories\SupportTicketFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicket extends Model
{
    /** @use HasFactory<SupportTicketFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'booking_id',
        'customer_id',
        'category',
        'priority',
        'status',
        'description',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'priority' => TicketPriority::class,
            'status' => TicketStatus::class,
            'resolved_at' => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function booking(): BelongsTo
    {
        return $this->belongsTo(RideBooking::class, 'booking_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** @param \Illuminate\Database\Eloquent\Builder<SupportTicket> $query */
    public function scopeOpen($query): void
    {
        $query->where('status', TicketStatus::Open);
    }

    /** @param \Illuminate\Database\Eloquent\Builder<SupportTicket> $query */
    public function scopeUrgent($query): void
    {
        $query->where('priority', TicketPriority::Urgent);
    }

    /** @param \Illuminate\Database\Eloquent\Builder<SupportTicket> $query */
    public function scopeUnresolved($query): void
    {
        $query->whereNotIn('status', [TicketStatus::Resolved, TicketStatus::Closed]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isResolved(): bool
    {
        return in_array($this->status, [TicketStatus::Resolved, TicketStatus::Closed]);
    }
}

<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'subscription_id',
        'mp_payment_id',
        'mp_preference_id',
        'amount',
        'payment_method',
        'status',
        'paid_at',
        'receipt_url',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the subscription associated with this invoice.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Check if invoice is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if invoice is pending payment.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}

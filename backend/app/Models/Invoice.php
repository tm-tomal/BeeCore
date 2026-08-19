<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'tenant_id',
        'customer_id',
        'subscription_id',
        'invoice_number',
        'status',
        'subtotal',
        'tax_amount',
        'total',
        'due_date',
        'billing_period_start',
        'meta',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'due_date' => 'date',
        'billing_period_start' => 'date',
        'meta' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(CustomerSubscription::class, 'subscription_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getPaidAmountAttribute(): string
    {
        $paid = $this->relationLoaded('payments')
            ? $this->payments->where('status', 'successful')->sum('amount')
            : $this->payments()->where('status', 'successful')->sum('amount');

        return number_format((float) $paid, 2, '.', '');
    }

    public function getOutstandingAmountAttribute(): string
    {
        return number_format(max(0, (float) $this->total - (float) $this->paid_amount), 2, '.', '');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaasInvoice extends Model
{
    protected $fillable = [
        'tenant_id', 'tenant_subscription_id', 'invoice_number', 'status',
        'period_start', 'period_end', 'amount', 'due_date', 'paid_at', 'reminder_sent_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantSubscription::class, 'tenant_subscription_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SaasPayment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaasInvoiceItem::class);
    }
}

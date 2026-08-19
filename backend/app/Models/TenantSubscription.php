<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantSubscription extends Model
{
    protected $fillable = [
        'tenant_id', 'saas_plan_id', 'status', 'billing_cycle', 'price',
        'starts_at', 'trial_ends_at', 'current_period_ends_at', 'grace_ends_at',
        'cancelled_at', 'auto_renew',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'trial_ends_at' => 'date',
        'current_period_ends_at' => 'date',
        'grace_ends_at' => 'date',
        'cancelled_at' => 'datetime',
        'auto_renew' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SaasPlan::class, 'saas_plan_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(TenantSubscriptionEvent::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SaasInvoice::class);
    }
}
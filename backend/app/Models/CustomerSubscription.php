<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerSubscription extends Model
{
    protected $fillable = [
        'tenant_id',
        'customer_id',
        'package_id',
        'package_name',
        'price',
        'tax_rate',
        'billing_cycle',
        'status',
        'next_billing_date',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'next_billing_date' => 'date',
        'started_at' => 'date',
        'ended_at' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'subscription_id');
    }
}